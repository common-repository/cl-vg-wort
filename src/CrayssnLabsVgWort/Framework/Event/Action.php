<?php

declare(strict_types=1);

namespace CrayssnLabsVgWort\Framework\Event;

use CrayssnLabsVgWort\Framework;
use CrayssnLabsVgWort\Framework\Plugin;

/**
 * Class Action
 *
 * @package   CrayssnLabsVgWort\Framework\Event
 *
 * @author    Sebastian Ludwig <dev@cl.team>
 * @copyright Copyright (c) 2023, CrayssnLabs Ludwig Wiegler GbR
 */
abstract class Action extends Framework\Event
{
    /**
     * @param \CrayssnLabsVgWort\Framework\Plugin $_pluginInstance
     */
    public function __construct(Plugin $_pluginInstance)
    {
        parent::__construct($_pluginInstance);

        add_action(static::getIdentifier(), [$this, 'process'], $this->priority, $this->acceptedArgs);
    }

    /**
     * Function process
     *
     */
    abstract public function process(...$parameters): void;
}
