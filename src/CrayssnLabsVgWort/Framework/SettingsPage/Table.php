<?php
declare(strict_types=1);

namespace CrayssnLabsVgWort\Framework\SettingsPage;

use WP_List_Table;

/**
 * Class Table
 *
 * @package   CrayssnLabsVgWort\Framework\SettingsPage
 *
 * @author    Sebastian Ludwig <dev@cl.team>
 * @copyright Copyright (c) 2024, CrayssnLabs Ludwig Wiegler GbR
 */
abstract class Table extends WP_List_Table
{
    /**
     * @param $args
     */
    public function __construct($args = [])
    {
        parent::__construct($args);

        $this->prepare_items();
    }

    /**
     * Function prepare_items
     *
     */
    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = [];
        $sortable = [];
        $this->_column_headers = array($columns, $hidden, $sortable);

        $this->items = $this->collection->collect();
    }
}
