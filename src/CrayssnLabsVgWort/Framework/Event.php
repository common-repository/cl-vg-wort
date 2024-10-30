<?php

declare(strict_types=1);

namespace CrayssnLabsVgWort\Framework;

/**
 * Class Event
 *
 * @package   CrayssnLabsVgWort\Framework
 *
 * @author    Sebastian Ludwig <dev@cl.team>
 * @copyright Copyright (c) 2023, CrayssnLabs Ludwig Wiegler GbR
 */
abstract class Event
{
    /**
     * @var int
     */
    protected int $priority = 10;

    /**
     * @var int
     */
    protected int $acceptedArgs = 1;

    /**
     * @var \CrayssnLabsVgWort\Framework\Plugin
     */
    protected Plugin $pluginInstance;

    /**
     * @param \CrayssnLabsVgWort\Framework\Plugin $_pluginInstance
     */
    public function __construct(Plugin $_pluginInstance)
    {
        $this->pluginInstance = $_pluginInstance;
    }

    /**
     * @return string
     */
    protected static function getIdentifier(): string
    {
        return self::camelCaseToUnderscore(substr(static::class, strrpos(static::class, '\\')+1));
    }

    /**
     * @param string $_string
     *
     * @return string
     */
    private static function camelCaseToUnderscore(string $_string): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $_string));
    }
}
