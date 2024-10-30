<?php

declare(strict_types=1);

namespace CrayssnLabsVgWort\Framework\Event;

use CrayssnLabsVgWort\Framework;
use CrayssnLabsVgWort\Framework\Plugin;

/**
 * Class Shortcode
 *
 * @package   CrayssnLabsVgWort\Framework\Event
 *
 * @author    Sebastian Ludwig <dev@cl.team>
 * @copyright Copyright (c) 2023, CrayssnLabs Ludwig Wiegler GbR
 */
abstract class Shortcode extends Framework\Event
{
    /**
     * @param \CrayssnLabsVgWort\Framework\Plugin $_pluginInstance
     */
    public function __construct(Plugin $_pluginInstance)
    {
        parent::__construct($_pluginInstance);

        add_shortcode(static::getIdentifier(), [$this, 'process']);
    }

    /**
     * Function process
     *
     * @param array $_attributes
     *
     * @return string
     */
    abstract public function process(array $_attributes): string;
}
