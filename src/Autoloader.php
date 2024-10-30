<?php

declare(strict_types=1);

namespace CrayssnLabsVgWort;

use Exception;

/**
 * Class Autoloader
 *
 * @package   CrayssnLabsVgWort
 *
 * @author    Sebastian Ludwig <dev@cl.team>
 * @copyright Copyright (c) 2023, CrayssnLabs Ludwig Wiegler GbR
 */
class Autoloader
{
    /**
     * @var array
     */
    private static array $loadedFiles = [];

    /**
     * @var string
     */
    private string $namespace;

    /**
     * Autoloader constructor.
     */
    private function __construct()
    {
        $this->prepareNamespace();

        spl_autoload_register([$this, 'load']);
    }

    /**
     * Function init
     *
     */
    public static function init(): void
    {
        static $instance;

        if ($instance === null) {
            $instance = new static();
        }
    }

    /**
     * Function prepareNamespace
     *
     */
    private function prepareNamespace(): void
    {
        foreach (scandir(__DIR__) as $file) {
            if ($file === '..' || $file === '.') {
                continue;
            }

            if (is_dir(__DIR__ . DIRECTORY_SEPARATOR . $file)) {
                $this->namespace = $file;

                return ;
            }
        }
    }

    /**
     * Function load
     *
     * @param string $_className
     *
     * @throws \Exception
     */
    private function load(string $_className): void
    {
        if (!str_contains($_className, $this->namespace)) {
            return;
        }

        $file = __DIR__ . DIRECTORY_SEPARATOR . str_replace("\\", DIRECTORY_SEPARATOR, $_className).".php";

        if (!isset(self::$loadedFiles[$file]) && !is_file($file)) {
            throw new Exception("The class \"" . $_className . "\" (" . $file . ") couldn't load.");
        }

        self::$loadedFiles[$file] = $file;

        include $file;
    }
}

Autoloader::init();
