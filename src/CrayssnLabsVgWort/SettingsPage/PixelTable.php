<?php

declare(strict_types=1);

namespace CrayssnLabsVgWort\SettingsPage;

use CrayssnLabsVgWort\Framework\SettingsPage\Table;
use CrayssnLabsVgWort\Framework\VgWort\Pixel;
use CrayssnLabsVgWort\Framework\VgWort\PixelCollection;
use CrayssnLabsVgWort\CrayssnLabsVgWort;

/**
 * Class PixelTable
 *
 * @package   CrayssnLabsVgWort\SettingsPage
 *
 * @author    Sebastian Ludwig <dev@cl.team>
 * @copyright Copyright (c) 2023, CrayssnLabs Ludwig Wiegler GbR
 */
class PixelTable extends Table
{
    /**
     * @var \CrayssnLabsVgWort\Framework\VgWort\PixelCollection
     */
    protected PixelCollection $collection;

    /**
     * @param array $args
     */
    public function __construct(array $args = [])
    {
        $this->collection = new PixelCollection();

        parent::__construct($args);

        $this->set_pagination_args([
            'total_pages' => 1,
            'per_page' => count($this->items),
            'total_items' => count($this->items),
        ]);
    }

    /**
     * Function prepare_items
     *
     */
    public function prepare_items()
    {
        parent::prepare_items();

        /**
         * @var Pixel $item
         */
        foreach ($this->items as $index => $item)
        {
            if($item->getSiteStatus() === false)
            {
                unset($this->items[$index]);
            }
        }

        $year = date('Y') - 1;

        usort($this->items, function($aPixel, $bPixel) use ($year) {

            $rating = [
                CrayssnLabsVgWort::LIMIT_TYPE_FULL_LIMIT    => 1,
                CrayssnLabsVgWort::LIMIT_TYPE_REDUCED_LIMIT => 2,
                CrayssnLabsVgWort::LIMIT_TYPE_WITHOUT_LIMIT => 3,
                CrayssnLabsVgWort::LIMIT_TYPE_NOT_SET       => 4,
            ];

            $aType = $aPixel->status[$year] ?? CrayssnLabsVgWort::LIMIT_TYPE_NOT_SET;
            $bType = $bPixel->status[$year] ?? CrayssnLabsVgWort::LIMIT_TYPE_NOT_SET;

            return $rating[$aType] <=> $rating[$bType];
        });
    }

    /**
     * Function bulk_actions
     *
     * @param $which
     */
    protected function bulk_actions($which = '')
    {
        $reportedItems = 0;
        $statuses = [];

        /**
         * @var Pixel $item
         */
        foreach ($this->items as $item)
        {
            if(!empty($item->reported))
            {
                $reportedItems++;
            }

            foreach ($item->status ?? [] as $status)
            {
                if(!isset($statuses[$status]))
                {
                    $statuses[$status] = 0;
                }

                $statuses[$status]++;
            }
        }

        echo '<span class="pixel-overview">';

        echo '<span class="pixel-overview-item">' . $reportedItems . ' reported items</span>';

        foreach ($statuses as $status => $count)
        {
            switch ($status)
            {
                case CrayssnLabsVgWort::LIMIT_TYPE_FULL_LIMIT:
                    echo '<span class="pixel-overview-item"><span class="status-icon" title="Minimum access reached">⬤</span>' . $count . '</span>';
                    break;
                case CrayssnLabsVgWort::LIMIT_TYPE_REDUCED_LIMIT:
                    echo '<span class="pixel-overview-item"><span class="status-icon" title="Minimum access achieved on a pro rata basis">◍</span>' . $count . '</span>';
                    break;
                default:
                    echo '<span class="pixel-overview-item"><span class="status-icon" title="Minimum access not reached">○</span>' . $count . '</span>';
            }
        }

        echo '</span>';
    }

    /**
     * Function get_columns
     *
     * @return string[]
     */
    public function get_columns()
    {
        return [
            'identifier' => 'Identifier',
            'title' => 'Title <br>Author / VG Wort card number',
            'publicPrivateIdentifier' => 'VG Wort public identifier<br>VG Wort private identifier',
            'domain' => 'VG Wort Domain',
            'status' => 'Status',
            'reported' => 'Reported',
            'reportText' => '🚀',
        ];
    }

    /**
     * Function get_default_primary_column_name
     *
     * @return string
     */
    protected function get_default_primary_column_name()
    {
        return 'publicIdentifier';
    }

    /**
     * Function identifier
     *
     * @param \CrayssnLabsVgWort\Framework\VgWort\Pixel $item
     *
     * @return mixed|string
     */
    protected function column_identifier(Pixel $item)
    {
        return $item->identifier;
    }

    /**
     * Function identifier
     *
     * @param \CrayssnLabsVgWort\Framework\VgWort\Pixel $item
     *
     * @return mixed|string
     */
    protected function column_title(Pixel $item)
    {
        $link =  '<label class="title">' . $item->getSiteTitle() . '</label>';
        $link .= '<a href="' . $item->getSiteUrl() . '" target="_blank" title="Frontend view">🌍</a> ';
        $link .= '<a href="https://hochzeitsreise.info/wp-admin/post.php?post=' . $item->siteId . '&action=edit" target="_blank"  title="Edit page">🛠</a>';

        $link .=  '<label>' . ($item->getSiteAuthorName() ?? 'No name set') . ' / ' . ($item->getSiteAuthorCardNumber() ?? 'No card number set') . '</label>';

        return $link;
    }

    /**
     * Function column_publicIdentifier
     *
     * @param \CrayssnLabsVgWort\Framework\VgWort\Pixel $item
     *
     * @return mixed|string|null
     */
    protected function column_publicPrivateIdentifier(Pixel $item)
    {
        return 'public:  ' . $item->publicIdentifier . '<br>private: ' . $item->privateIdentifier;
    }

    /**
     * Function column_domain
     *
     * @param \CrayssnLabsVgWort\Framework\VgWort\Pixel $item
     *
     * @return mixed|string|null
     */
    protected function column_domain(Pixel $item)
    {
        return $item->domain;
    }

    /**
     * Function column_reportText
     *
     * @param \CrayssnLabsVgWort\Framework\VgWort\Pixel $item
     *
     * @return string
     */
    protected function column_status(Pixel $item)
    {
        if(empty($item->status))
        {
            return '-';
        }

        $years = [];

        foreach ($item->status as $year => $type)
        {
            $year = '<span class="status-year">' . $year . '</span>';

            switch ($type)
            {
                case CrayssnLabsVgWort::LIMIT_TYPE_FULL_LIMIT:
                    $year = '<span class="status-icon" title="Minimum access reached">⬤</span>' . $year;
                    break;
                case CrayssnLabsVgWort::LIMIT_TYPE_REDUCED_LIMIT:
                    $year = '<span class="status-icon" title="Minimum access achieved on a pro rata basis">◍</span>' . $year;
                    break;
                default:
                    $year = '<span class="status-icon" title="Minimum access not reached">○</span>' . $year;
            }

            $years[] = $year;
        }

        return implode(', ', $years);
    }

    /**
     * Function column_reportText
     *
     * @param \CrayssnLabsVgWort\Framework\VgWort\Pixel $item
     *
     * @return string
     */
    protected function column_reported(Pixel $item)
    {
        if(empty($item->reported))
        {
            return 'not reported';
        }

        $reportedDate = new \DateTime($item->reported);

        return $reportedDate->format('d.m.Y') . '<br>' . $reportedDate->format('H:i:s');
    }

    /**
     * Function column_reportText
     *
     * @param \CrayssnLabsVgWort\Framework\VgWort\Pixel $item
     *
     * @return string
     */
    protected function column_reportText(Pixel $item)
    {
        return '
        <form action="" method="post"><button class="report-btn" title="Report the current text to VG Wort" type="submit" name="report-text" value="' . $item->identifier . '">🚀</button></form>
        ';
    }
}
