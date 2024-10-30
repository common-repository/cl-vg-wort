<?php

declare(strict_types=1);

namespace CrayssnLabsVgWort\Framework;

/**
 * Class Util
 *
 * @package   CrayssnLabsVgWort\Framework
 *
 * @author    Sebastian Ludwig <dev@cl.team>
 * @copyright Copyright (c) 2023, CrayssnLabs Ludwig Wiegler GbR
 */
abstract class Util
{
    /**
     * @var array|string[]
     */
    private static array $functionToFile = [
        'check_admin_referer' => 'wp-includes/pluggable.php',
        'get_plugin_data' => 'wp-admin/includes/plugin.php',
    ];

    /**
     * Function functionsExists
     *
     * @param array|string $_functionNames
     */
    public static function functionsExists($_functionNames): void
    {
        if(is_string($_functionNames))
        {
            $_functionNames = [$_functionNames];
        }

        foreach ($_functionNames as $functionName)
        {
            if(!function_exists($functionName)) {
                if(isset(self::$functionToFile[$functionName])) {
                    require_once ABSPATH .self::$functionToFile[$functionName];
                }
            }
        }
    }
}