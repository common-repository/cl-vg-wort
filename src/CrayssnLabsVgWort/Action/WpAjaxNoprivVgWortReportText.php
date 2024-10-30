<?php

declare(strict_types=1);

namespace CrayssnLabsVgWort\Action;

use CrayssnLabsVgWort\CrayssnLabsVgWort;
use CrayssnLabsVgWort\Framework\Event\Action;
use CrayssnLabsVgWort\Framework\Event\Extension\SendJsonResponse;
use Exception;

/**
 * Class WpAjaxNoprivVgWortReportText
 *
 * @package   CrayssnLabsVgWort\Action
 *
 * @author    Sebastian Ludwig <dev@cl.team>
 * @copyright Copyright (c) 2024, CrayssnLabs Ludwig Wiegler GbR
 *
 * @property \CrayssnLabsVgWort\CrayssnLabsVgWort $pluginInstance
 */
class WpAjaxNoprivVgWortReportText extends Action
{
    use SendJsonResponse;

    /**
     * Function action
     *
     */
    public function process(...$parameters): void
    {
        $pixelIdentifier = $_POST['pixel'];
        $result = [];

        try {
            if ($this->pluginInstance->reportTextToVgWortByPixelIdentifier($pixelIdentifier, $result)) {
                $this->sendJsonResponse([
                    'status' => 'success',
                    'message' => 'Tracked text with the identifier ' . $pixelIdentifier . ' successful reported.'
                ]);
            } else {
                $this->sendJsonResponse([
                    'status' => 'error',
                    'message' => 'Reporting problem with the VGWort api.' . var_export($result, true)
                ]);
            }
        } catch (Exception $e) {
            $this->sendJsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}
