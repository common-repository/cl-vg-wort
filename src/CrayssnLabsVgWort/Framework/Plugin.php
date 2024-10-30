<?php

/** @noinspection PhpPropertyOnlyWrittenInspection */

declare(strict_types=1);

namespace CrayssnLabsVgWort\Framework;

/**
 * Class Plugin
 *
 * @package   CrayssnLabsVgWort\Framework
 *
 * @author    Sebastian Ludwig <dev@cl.team>
 * @copyright Copyright (c) 2023, CrayssnLabs Ludwig Wiegler GbR
 */
abstract class Plugin
{
    /**
     * @var array
     */
    protected static array $instances = [];

    /**
     * @var string
     */
    public string $identifier;

    /**
     * @var string
     */
    protected string $rootPath;

    /**
     * @var string
     */
    protected string $pluginFile;

    /**
     * @var array
     */
    public array $options = [];

    /**
     * @var array
     */
    private array $actions = [];

    /**
     * @var array
     */
    private array $filters = [];

    /**
     * @var array
     */
    private array $shortcodes = [];


    /**
     * Function name
     *
     * @return string
     */
    abstract public function name(): string;

    /**
     * Function settings
     *
     * @return array
     */
    abstract public function settings(): array;

    /**
     *
     */
    protected function __construct()
    {
        $this->rootPath = realpath(__DIR__ . '/../../..');
        $this->identifier = basename($this->rootPath);
        $this->pluginFile = $this->rootPath . DIRECTORY_SEPARATOR . $this->identifier . '.php';

        $this->setOptions();
        $this->registerEvents();

        if (is_admin()) {
            $this->registerSettingsPage();
        }
    }

    /**
     * Function init
     *
     * @return mixed|static
     */
    public static function init()
    {
        if (!isset(self::$instances[static::class])) {
            self::$instances[static::class] = new static();
        }

        return self::$instances[static::class];
    }

    /**
     * Function isPluginByFile
     *
     * @param string $_file
     *
     * @return bool
     */
    public function isPluginByFile(string $_file): bool
    {
        return dirname($_file) === $this->identifier;
    }

    /**
     * Function actions
     *
     */
    protected function actions(): array
    {
        return [];
    }

    /**
     * Function hooks
     *
     */
    protected function filters(): array
    {
        return [];
    }

    /**
     * Function hooks
     *
     */
    protected function shortcodes(): array
    {
        return [];
    }

    /**
     * Function registerSettingsPage
     *
     */
    protected function registerSettingsPage(): void
    {
        (new SettingsPage($this))->register();
    }

    /**
     * Function appendToSettingsPage
     *
     */
    public function prependToSettingsPage(): void
    {
    }

    /**
     * Function appendToSettingsPage
     *
     */
    public function appendToSettingsPage(): void
    {
    }

    /**
     * Function registerEvents
     *
     */
    private function registerEvents(): void
    {
        foreach (array_unique($this->actions()) as $action) {
            $this->actions[$action] = new $action($this);
        }

        foreach (array_unique($this->filters()) as $filter) {
            $this->filters[$filter] = new $filter($this);
        }

        foreach (array_unique($this->shortcodes()) as $shortcode) {
            $this->shortcodes[$shortcode] = new $shortcode($this);
        }

        $this->registerStatusEvents();
        $this->registerUpdateEvents();
    }

    /**
     * Function registerResources
     *
     * @param string $_file
     */
    public function registerResources(string $_file): void
    {
        if (!is_file($_file)) {
            return ;
        }

        $_file = realpath($_file);

        [$pluginRootDirectory] = explode('/plugins/', __DIR__);

        $pluginRootDirectory .= '/plugins';

        $url = plugins_url(str_replace($pluginRootDirectory, '', $_file));

        if (str_contains($_file, '.css')) {
            wp_enqueue_style($this->identifier, $url);
        } elseif (str_contains($_file, '.js')) {
            wp_enqueue_script($this->identifier, $url, ['jquery'], '1.0', true);
        }
    }

    /**
     * Function setOptions
     *
     */
    protected function setOptions(): void
    {
        $option = get_option($this->identifier);

        $this->options = array_merge($this->options, !is_array($option) ? [] : $option);
    }

    /**
     * Function saveOptions
     *
     */
    protected function saveOptions(): void
    {
        update_option($this->identifier, $this->options);
    }

    /**
     * Function getPluginData
     *
     * @return array|null
     */
    protected function getPluginData(): ?array
    {
        static $pluginData;

        if ($pluginData === null) {
            Util::functionsExists('get_plugin_data');

            $pluginData = get_plugin_data($this->pluginFile);
        }

        /**
         * @var array $pluginData = array (
        'Name' => 'Rich Snippet Stars',
        'PluginURI' => 'https://rich-snippet-stars.grimnir.wae-server.de/repository/cl-rich-snippet-stars.zip',
        'Version' => '0.0.0',
        'Description' => 'Von <a href="https://cl.team">Sebastian Ludwig , CrayssnLabs Ludwig Wiegler GbR</a>.',
        'Author' => '<a href="https://cl.team">Sebastian Ludwig, CrayssnLabs</a>',
        'AuthorURI' => 'https://cl.team',
        'TextDomain' => 'cl-rich-snippet-stars',
        'DomainPath' => '',
        'Network' => false,
        'RequiresWP' => '5.2',
        'RequiresPHP' => '7.2',
        'UpdateURI' => 'https://rich-snippet-stars.grimnir.wae-server.de/repository/cl-rich-snippet-stars.json',
        'Title' => '<a href="https://rich-s[...]ippet-stars.zip">Rich Snippet Stars</a>',
        'AuthorName' => 'Sebastian Ludwig , CrayssnLabs Ludwig Wiegler GbR',
        )
         */
        return $pluginData;
    }


    /**
     * Function registerUpdateEvents
     *
     */
    private function registerUpdateEvents(): void
    {
        add_filter('plugins_api', [$this, 'info'], 20, 3);
        add_action('upgrader_process_complete', [$this, 'purge'], 10, 2);
    }

    /**
     * Function registerStatusEvents
     *
     */
    private function registerStatusEvents(): void
    {
        register_activation_hook( $this->pluginFile, [$this, 'pluginActivation']);
        register_deactivation_hook( $this->pluginFile, [$this, 'pluginDeactivation']);
    }

    /**
     * Function pluginActivation
     *
     */
    public function pluginActivation(): void
    {
        /**
         * @var Event\ScheduleAction $actions
         */
        foreach (array_unique($this->actions()) as $actions)
        {
            if(method_exists($actions, 'register'))
            {
                $actions::register();
            }
        }
    }

    /**
     * Function pluginDeactivation
     *
     */
    public function pluginDeactivation(): void
    {
        /**
         * @var Event\ScheduleAction $actions
         */
        foreach (array_unique($this->actions()) as $actions)
        {
            if(method_exists($actions, 'remove'))
            {
                $actions::remove();
            }
        }
    }

    /**
     * Function request
     *
     * @return false|mixed
     */
    public function request()
    {
        $remote = get_transient($this->identifier);

        $pluginData = $this->getPluginData();

        if (false === $remote) {
            $remote = wp_remote_get(
                $pluginData['UpdateURI'],
                array(
                    'timeout' => 10,
                    'headers' => array(
                        'Accept' => 'application/json',
                    ),
                )
            );

            if (is_wp_error($remote)
                || 200 !== wp_remote_retrieve_response_code($remote)
                || empty(wp_remote_retrieve_body($remote))
            ) {
                return false;
            }

            set_transient($this->identifier, $remote, 60 * 60 * 24);
        }

        return json_decode(wp_remote_retrieve_body($remote));
    }

    /**
     * Function info
     *
     * @param $res
     * @param $action
     * @param $args
     *
     * @return false|object
     *
     * @noinspection PhpUnusedParameterInspection
     */
    public function info($res, $action, $args)
    {
        if ('plugin_information' !== $action) {
            return false;
        }

        if ($this->identifier !== $args->slug) {
            return false;
        }

        // get updates
        $remote = $this->request();

        if (! $remote) {
            return false;
        }

        $res = (object)[];

        $res->name = $remote->name;
        $res->slug = $remote->slug;
        $res->version = $remote->version;
        $res->tested = $remote->tested;
        $res->requires = $remote->requires;
        $res->author = $remote->author;
        $res->author_profile = $remote->author_profile;
        $res->download_link = $remote->download_url;
        $res->trunk = $remote->download_url;
        $res->requires_php = $remote->requires_php;
        $res->last_updated = $remote->last_updated;

        $res->sections = [
            'description' => $remote->sections->description,
            'installation' => $remote->sections->installation ?? '',
            'changelog' => $remote->sections->changelog ?? '',
        ];

        if (! empty($remote->banners)) {
            $res->banners = [
                'low' => $remote->banners->low,
                'high' => $remote->banners->high,
            ];
        }

        return $res;
    }

    /**
     * Function purge
     *
     * @param $_pluginUpdater
     * @param $options
     */
    public function purge($_pluginUpdater, $options)
    {
        if ('update' === $options['action'] && 'plugin' === $options[ 'type' ]) {
            // just clean the cache when new plugin version is installed
            delete_transient($this->identifier);
        }
    }
}
