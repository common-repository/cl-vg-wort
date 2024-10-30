<?php

declare(strict_types=1);

namespace CrayssnLabsVgWort;

use CrayssnLabsVgWort\Framework\Plugin;
use CrayssnLabsVgWort\Framework\VgWort\Pixel;
use CrayssnLabsVgWort\Framework\VgWort\Rest\Client;
use CrayssnLabsVgWort\Framework\VgWort\Rest\Request\OrderPixel;
use CrayssnLabsVgWort\Framework\VgWort\Rest\Request\PixelOverview;
use CrayssnLabsVgWort\Framework\VgWort\Rest\Request\ReportText;
use CrayssnLabsVgWort\Framework\VgWort\Rest\Request\Status;
use CrayssnLabsVgWort\SettingsPage\PixelTable;

/**
 * Class CrayssnLabsVgWort
 *
 * @package   CrayssnLabsVgWort
 *
 * @author    Sebastian Ludwig <dev@cl.team>
 * @copyright Copyright (c) 2023, CrayssnLabs Ludwig Wiegler GbR
 */
class CrayssnLabsVgWort extends Plugin
{
    const MIN_CONTENT_LENGTH = 1800;

    const CARD_NUMBER_INDEX = 'cl-vg-wort_cardNumber';

    const
        LIMIT_TYPE_FULL_LIMIT = 'FULL_LIMIT',
        LIMIT_TYPE_REDUCED_LIMIT = 'REDUCED_LIMIT',
        LIMIT_TYPE_NOT_SET = 'NOT_SET',
        LIMIT_TYPE_WITHOUT_LIMIT = 'WITHOUT_LIMIT';

    const
        SCHEDULED_TASK_ENABLE = 'enable',
        SCHEDULED_TASK_DISABLE = 'disable';

    /**
     * @var array
     */
    public array $options = [
        'apiKey' => null,
        'enableScheduledTask' => self::SCHEDULED_TASK_DISABLE,
        'counters' => []
    ];

    /**
     * Function name
     *
     * @return string
     */
    public function name(): string
    {
        return 'VG Wort - T(A)OM';
    }

    /**
     * Function actions
     *
     * @return array
     */
    protected function actions(): array
    {
        return [
            Action\WpFooter::class,
            Action\WpAjaxVgWortOrderCounter::class,
            Action\WpAjaxNoprivVgWortOrderCounter::class,
            Action\WpAjaxVgWortTransferPixel::class,
            Action\WpAjaxNoprivVgWortTransferPixel::class,
            Action\WpAjaxVgWortReportText::class,
            Action\WpAjaxNoprivVgWortReportText::class,
            Action\ShowUserProfile::class,
            Action\EditUserProfile::class,
            Action\UserNewForm::class,
            Action\PersonalOptionsUpdate::class,
            Action\EditUserProfileUpdate::class,
            Action\UserRegister::class,
            Action\VgWortReportTexts::class,
        ];
    }

    /**
     * Function actions
     *
     * @return array
     */
    protected function filters(): array
    {
        return [
            Filter\PreUpdateOption::class,
        ];
    }

    /**
     * Function scheduleEvents
     *
     * @return string[]
     */
    protected function scheduleEvents(): array
    {
        return [

        ];
    }

    /**
     * Function settings
     *
     * @return array
     */
    public function settings(): array
    {
        $minContentLength = self::MIN_CONTENT_LENGTH;

        return [
            [
                'fields' => [
                    [
                        'label' => 'VG Wort API Key',
                        'index' => 'apiKey',
                        'type' => Framework\SettingsPage::FIELD_TYPE_TEXT,
                        'placeholder' => '1b1b1b1b-2b2b-2b2b-2b2b-1b1b1b1b1b1b',
                    ], [
                        'label' => 'How it works',
                        'type' => Framework\SettingsPage::FIELD_TYPE_HTML,
                        'content' => "
                        <p>
                            After entering the API key, the counters are automatically integrated into the individual pages. The system first checks whether the content of the page exceeds $minContentLength characters and no other counters have been integrated.
                        </p>
                        <p>
                            The process is fully automated. This applies to the ordering of the counting tokens as well as the actual integration. In the future, the reporting of the texts, including their extraction, will also be carried out.
                        </p>",
                    ], [
                        'label' => 'Automatic report text message to VG Wort',
                        'index' => 'enableScheduledTask',
                        'type' => Framework\SettingsPage::FIELD_TYPE_SELECT,
                        'options' => [
                            self::SCHEDULED_TASK_ENABLE => 'enable',
                            self::SCHEDULED_TASK_DISABLE => 'disable',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Function prependToSettingsPage
     *
     * @throws \Exception
     */
    public function prependToSettingsPage(): void
    {
        $this->migrateJsonFilesToOptions();

        if (!empty($this->options['apiKey'])) {
            $restClient = new Client($this->options['apiKey']);

            $result = $restClient->sendRequest(new Status());

            if ($result['status'] === 200) {
                $this->adminNoticeConnectionSuccess();
            } else {
                $this->adminNoticesConnectionError();
            }
        }
    }

    /**
     * Function appendToSettingsPage
     *
     * @throws \Exception
     */
    public function appendToSettingsPage(): void
    {
        echo '<h3>Aktions-Log</h3>';
        echo '<div id="message-wrapper"></div>';

        if(isset($_POST['search-counter']))
        {
            $pages = get_pages();

            $urls = [];

            foreach ($pages as $page)
            {
                $urls[] = get_permalink($page) ?? $page->guid;
            }

            $scriptClassContent = file_get_contents(__DIR__ . '/_resources/Search.js');
            $options = json_encode([
                'urls' => $urls,
                'messageWrapperId' => 'message-wrapper',
            ]);

            echo "<script>$scriptClassContent (new Search($options)).start();</script>";
        }
        elseif(isset($_POST['report-texts']))
        {
            $scriptClassContent = file_get_contents(__DIR__ . '/_resources/ReportText.js');
            $options = json_encode([
                'ajaxRequestUrl' => admin_url("admin-ajax.php"),
                //'pixels' => array_keys($this->options['counters']),
                'pixels' => ['post-601'],
                'messageWrapperId' => 'message-wrapper',
            ]);

            echo "<script>$scriptClassContent (new ReportText($options)).start();</script>";
        }
        elseif(isset($_POST['check-texts']))
        {
            $this->updateTextStatus();
        }
        elseif(isset($_POST['report-text']))
        {
            $apiResult = [];

            $pixel = $this->getVgWortPixelByIdentifier($_POST['report-text']);

            if(!$this->reportTextToVgWortByPixelIdentifier($_POST['report-text'], $apiResult, true))
            {
                echo <<<HTML
                <div class="notice notice-error is-dismissible">
                    <p>The text from the page "{$pixel->getSiteTitle()}" could not be reported.</p>
                </div>
                HTML;

                if(!empty($apiResult['message']) && !empty($apiResult['message']['errormsg']))
                {
                    echo <<<HTML
                    <div class="notice notice-error is-dismissible">
                        <p>Reply from the VG Wort interface: <strong>{$apiResult['message']['errormsg']}</strong></p>
                    </div>
                    HTML;

                    if($apiResult['message']['errorcode'] === 37)
                    {
                        $text = $pixel->getSiteText();
                        $textLength = mb_strlen($text);

                        echo <<<HTML
                        <div class="notice is-dismissible">
                            <p>The text ($textLength characters) found:</p>
                            <p>$text</p>
                        </div>
                        HTML;
                    }
                }
            }
            else
            {
                echo <<<HTML
                <div class="notice notice-success is-dismissible">
                    <p>The text from the page "{$pixel->getSiteTitle()}" was successfully reported.</p>
                </div>
                HTML;
            }
        }

        echo '<div class="cl-vg-wort-options-menu">';
            echo '<form method="post" action="" class="submit"><input type="submit" name="search-counter" id="submit" class="button button-primary" value="Search for integrated counters"' . (isset($_POST['search-counter']) ? ' disabled' : '') . '></form>';
            echo '<form method="post" action="" class="submit report-text-form"><input type="submit" name="report-texts" id="submit" class="button button-primary" value="Submit texts to VG Wort"' . (isset($_POST['report-texts']) ? ' disabled' : '') . '></form>';
            echo '<form method="post" action="" class="submit check-texts-form"><input type="submit" name="check-texts" id="submit" class="button button-primary" value="Check all texts status"' . (isset($_POST['report-texts']) ? ' disabled' : '') . '></form>';
        echo '</div>';
        echo '<style>' . file_get_contents(__DIR__ . '/_resources/admin.css') . '</style>';

        $table = new PixelTable();
        $table->prepare_items();
        $table->display();
    }

    /**
     * Function getVgWortPixelByIdentifier
     *
     * @param string $_identifier
     * @param bool   $_orderIfNotExists
     *
     * @return \CrayssnLabsVgWort\Framework\VgWort\Pixel|null
     * @throws \Exception
     */
    public function getVgWortPixelByIdentifier(string $_identifier, bool $_orderIfNotExists = false): ?Pixel
    {
        if (!isset($this->options['counters'][$_identifier]) && $_orderIfNotExists) {
            $apiKey = $this->options['apiKey'];

            $restClient = new Client($apiKey);

            $result = $restClient->sendRequest(new OrderPixel());

            $pixel = array_pop($result['pixels']);

            $this->options['counters'][$_identifier] = [
                'identifier' => $_identifier,
                'apiKey' => $apiKey,
                'domain' => $result['domain'],
                'publicIdentifier' => $pixel['publicIdentificationId'],
                'privateIdentifier' => $pixel['privateIdentificationId'],
                'status' => [],
                'reported' => null,
            ];

            $this->saveOptions();
        }

        return Pixel::getInstanceByArray($this->options['counters'][$_identifier] ?? []);
    }

    /**
     * Function reportTextToVgWortByPixelIdentifier
     *
     * @param string $_identifier
     * @param array  $result
     * @param bool   $_force
     *
     * @return bool
     * @throws \Exception
     */
    public function reportTextToVgWortByPixelIdentifier(string $_identifier, array &$result = [], bool $_force = false): bool
    {
        if(empty($this->options['counters'][$_identifier]))
        {
            return false;
        }

        $pixel = new Pixel($this->options['counters'][$_identifier]);

        if($pixel->reported && !$_force)
        {
            return true;
        }

        if($pixel->getSiteStatus() === false)
        {
            return false;
        }

        $pixelStatus = $this->getPixelStatus($pixel->publicIdentifier);

        $limitsType = null;
        foreach ($pixelStatus['limitsInYear'] as $limit)
        {
            if($limit['year'] === (date('Y') - 1))
            {
                $limitsType = $limit['type'];

                break;
            }
        }

        if(
            $limitsType !== self::LIMIT_TYPE_FULL_LIMIT &&
            $limitsType !== self::LIMIT_TYPE_REDUCED_LIMIT
        ) {
            return false;
        }

        $apiKey = $this->options['apiKey'];
        $restClient = new Client($apiKey);

        $siteText = $pixel->getSiteText();
        $siteUrl = $pixel->getSiteUrl();

        if(empty($siteText))
        {
            $result['message'] = [
                'errorcode' => 37,
                'errormsg' => 'Der gemeldete Text hat nicht die erforderliche Länge (min. 10.000 Zeichen), um bei anteiligem Mindestzugriff gemeldet werden zu können.'
            ];

            return false;
        }

        $result = $restClient->sendRequest(new ReportText(
            $pixel->privateIdentifier,
            $pixel->getSiteMetaTitle() ?? $pixel->getSiteTitle(),
            $siteText,
            $siteUrl,
            $pixel->getSiteLastUpdate(),
            $pixel->getParticipants()
        ));

        // main account is recipient, this musst be fixed
        if((int)$result['message']['errorcode'] === 31)
        {
            if(update_user_meta($pixel->getSiteAuthorId(), self::CARD_NUMBER_INDEX, '') !== false)
            {
                return $this->reportTextToVgWortByPixelIdentifier($_identifier, $result, $_force);
            }
        }
        elseif(
            !empty($result['createdDate']) ||
            (int)$result['message']['errorcode'] === 3
        )
        {
            $this->options['counters'][$_identifier]['reported'] = date('Y-m-d H:i:s');

            $this->saveOptions();

            return true;
        }

        return false;
    }

    /**
     * Function reportTextsToVgWort
     *
     * @return array
     * @throws \Exception
     */
    public function reportTextsToVgWort(): array
    {
        $reportedTexts = [];
        $counter = 0;

        $isCli = str_contains(php_sapi_name(), 'cli');

        foreach ($this->options['counters'] as $pixelData)
        {
            if(!empty($pixelData['reported']))
            {
                if($isCli)
                {
                    echo "Already reported #" . $pixelData['identifier'] . "\n";
                    echo " -> successful\n\n";
                }

                $reportedTexts[$pixelData['identifier']] = true;

                continue;
            }

            if($isCli)
            {
                echo "Report #" . $pixelData['identifier'] . "\n";
            }

            $apiResult = [];

            $reportedTexts[$pixelData['identifier']] = $this->reportTextToVgWortByPixelIdentifier($pixelData['identifier'], $apiResult);

            if($reportedTexts[$pixelData['identifier']])
            {
                if($isCli)
                {
                    echo " -> successful\n";
                }

                $counter++;
            }
            else
            {
                if($isCli)
                {
                    echo " -> failed\n";
                    var_dump($apiResult);
                }
            }

            if($isCli)
            {
                echo "\n";
            }

            if($counter >= 5)
            {
                if($isCli)
                {
                    echo "Abort after 5 reports\n";
                }

                break;
            }
        }

        return $reportedTexts;
    }

    /**
     * Function setVgWortPixelDataByIdentifier
     *
     * @param string $_identifier
     * @param array  $_pixelData
     */
    public function setVgWortPixelDataByIdentifier(string $_identifier, array $_pixelData): void
    {
        $_pixelData['identifier'] = $_identifier;

        $this->options['counters'][$_identifier] = $_pixelData;

        $this->saveOptions();
    }

    /**
     * Function registerSettingsPage
     *
     * @throws \Exception
     */
    protected function registerSettingsPage(): void
    {
        parent::registerSettingsPage();
    }

    /**
     * Function admin_notices_connection_success
     *
     */
    public function adminNoticeConnectionSuccess(): void
    {
        echo <<<HTML
        <div class="notice notice-success is-dismissible">
            <p>Connection with the VG Wort interface established.</p>
        </div>
        HTML;
    }

    /**
     * Function admin_notices_connection_error
     *
     */
    public function adminNoticesConnectionError(): void
    {
        echo <<<HTML
        <div class="notice notice-error is-dismissible">
            <p>Connection to the VG Wort interface could not be established. Please check your API Key!</p>
        </div>
        HTML;
    }

    /**
     * Function migrateJsonFilesToOptions
     *
     */
    private function migrateJsonFilesToOptions(): void
    {
        $deprecatedJsonDataFolder = $this->rootPath . '/data';

        if(is_dir($deprecatedJsonDataFolder))
        {
            $files = glob($deprecatedJsonDataFolder . '/*');

            if(!empty($files))
            {
                foreach ($files as $file)
                {
                    $pixelData = json_decode(file_get_contents($file), true);

                    $identifier = str_replace('.json', '', basename($file));

                    $this->setVgWortPixelDataByIdentifier($identifier, $pixelData);

                    unlink($file);
                }
            }

            if(rmdir($deprecatedJsonDataFolder))
            {
                echo <<<HTML
                <div class="notice notice-success is-dismissible">
                    <p>The obsolete file structure was transferred to the options and then removed.</p>
                </div>
                HTML;
            }
        }
    }

    /**
     * Function getPixelStatus
     *
     * @param string $publicIdentifier
     *
     * @return array|null
     * @throws \Exception
     */
    private function getPixelStatus(string $publicIdentifier): ?array
    {
        $apiKey = $this->options['apiKey'];
        $restClient = new Client($apiKey);
        $result = $restClient->sendRequest(new PixelOverview([$publicIdentifier]));

        if(empty($result['pixels']))
        {
            return null;
        }

        return $result['pixels'][0];
    }

    /**
     * Function updateTextStatus
     *
     */
    private function updateTextStatus(): void
    {
        $apiKey = $this->options['apiKey'];
        $counterIndexByPublicIdentifiers = [];

        foreach ($this->options['counters'] as $index => $counter)
        {
            $counterIndexByPublicIdentifiers[$counter['publicIdentifier']] = $index;
        }

        $restClient = new Client($apiKey);
        $result = $restClient->sendRequest(new PixelOverview(array_keys($counterIndexByPublicIdentifiers)));

        if(empty($result) || empty($result['pixels']))
        {
            return ;
        }

        $pixelWithNoLimits = 0;

        foreach ($result['pixels'] as $pixel)
        {
            $index = $counterIndexByPublicIdentifiers[$pixel['publicUID']];

            $this->options['counters'][$index]['status'] = [];

            foreach ($pixel['limitsInYear'] as $limit)
            {
                $this->options['counters'][$index]['status'][$limit['year']] = $limit['type'];
            }

            if(empty($pixel['limitsInYear']))
            {
                $pixelWithNoLimits++;
            }

            if(!$pixel['countStarted'])
            {

            }
        }

        $this->saveOptions();

        echo <<<HTML
        <div class="notice notice-success is-dismissible">
            <p>The text statuses have been updated. ($pixelWithNoLimits with no )</p>
        </div>
        HTML;
    }
}
