<?php

declare(strict_types=1);

namespace CrayssnLabsVgWort\Framework;

use Closure;
use CrayssnLabsVgWort\Framework\SettingsPage\Table;

/**
 * Class SettingsPage
 *
 * @package   CrayssnLabsVgWort\Framework
 *
 * @author    Sebastian Ludwig <dev@cl.team>
 * @copyright Copyright (c) 2023, CrayssnLabs Ludwig Wiegler GbR
 */
class SettingsPage
{
    const
        FIELD_TYPE_TEXT = 'text',
        FIELD_TYPE_NUMBER = 'number',
        FIELD_TYPE_SELECT = 'select',
        FIELD_TYPE_TEXTAREA = 'textarea',
        FIELD_TYPE_MEDIA = 'media',
        FIELD_TYPE_HTML = 'html',
        FIELD_TYPE_BUTTON = 'button';


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
     * Function register
     *
     */
    public function register(): void
    {
        add_action('admin_enqueue_scripts', [$this, 'registerImageMediaManagerScript']);
        add_action('wp_ajax_cl_base_get_image', [$this, 'handleAjaxGetImageRequest']);
        add_action('admin_menu', [$this, 'registerOptionsPage']);
        add_action('admin_init', [$this, 'initializeSettings']);
    }

    /**
     * Function registerImageMediaManagerScript
     *
     * @param string $page
     *
     */
    public function registerImageMediaManagerScript(string $page): void
    {
        if (str_contains($page, 'settings_page_cl')) {
            wp_enqueue_media();

            $this->pluginInstance->registerResources(__DIR__ . '/../../../js/select-image.js');
        }
    }

    /**
     * Function handleAjaxGetImageRequest
     *
     */
    public function handleAjaxGetImageRequest(): void
    {
        if (isset($_GET['id'])) {
            $imageId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

            wp_send_json_success([
                'image' => wp_get_attachment_image_url($imageId, 'medium'),
            ]);

            return;
        }

        wp_send_json_error();
    }

    /**
     * Function registerOptionsPage
     *
     */
    public function registerOptionsPage(): void
    {
        add_options_page(
            $this->pluginInstance->name(),
            $this->pluginInstance->name(),
            'manage_options',
            $this->pluginInstance->identifier,
            [
                $this,
                'initializePage'
            ]
        );
    }

    /**
     * Function initializePage
     *
     */
    public function initializePage(): void
    {
        echo <<<HTML
        <div class="wrap">
        <style>
            .form-wrapper
            {
                max-width: 1000px;
            }
            .form-wrapper td > *
            {
                min-width: 100%;
            }
            .form-wrapper td > textarea
            {
                min-height: 250px;
            }
            .form-wrapper td > textarea.monospace
            {
                font-family: monospace;
            }
            .form-wrapper td > *[type="number"]
            {
                min-width: 250px;
            }
            .form-wrapper td > img
            {
                object-fit: contain;
                max-height: 300px;
                min-width: auto;
            }
        </style>
        <div class="form-wrapper {$this->pluginInstance->identifier}-options">
            <h1>{$this->pluginInstance->name()} Options</h1>
        HTML;

        $this->pluginInstance->prependToSettingsPage();

        /** @noinspection HtmlUnknownTarget */
        echo <<<HTML
            <form method="post" action="options.php">
        HTML;

            settings_fields($this->pluginInstance->identifier . '-group');
            do_settings_sections($this->pluginInstance->identifier);
            submit_button();

        echo <<<HTML
            </form>
        </div>
        HTML;

        $this->pluginInstance->appendToSettingsPage();

        echo <<<HTML
        </div>
        HTML;

    }

    /**
     * Function initializeSettings
     *
     */
    public function initializeSettings(): void
    {
        register_setting(
            $this->pluginInstance->identifier . '-group',
            $this->pluginInstance->identifier,
            [
                $this,
                'sanitize'
            ]
        );

        foreach ($this->pluginInstance->settings() as $index => $section) {
            $sectionId = $this->pluginInstance->identifier . '-section-' . $index;

            add_settings_section(
                $sectionId,
                $section['label'] ?? '',
                function () {
                },
                $this->pluginInstance->identifier
            );

            foreach ($section['fields'] as $option) {
                $fieldId = isset($option['index']) ?
                    $this->pluginInstance->identifier . '-field-' . $option['index'] :
                    null;

                add_settings_field(
                    $fieldId,
                    $option['label'],
                    $this->createCallBack($option),
                    $this->pluginInstance->identifier,
                    $sectionId
                );
            }
        }
    }

    /**
     * Function createCallBack
     *
     * @param array $_option
     *
     * @return \Closure
     *
     * @noinspection PhpUnnecessaryCurlyVarSyntaxInspection
     */
    protected function createCallBack(array $_option): Closure
    {
        $identifier = esc_attr($this->pluginInstance->identifier);
        $options = $this->pluginInstance->options;

        $index = esc_attr($_option['index'] ?? '');
        $placeholder = esc_attr($_option['placeholder'] ?? '');
        $cssClass = esc_attr($_option['class'] ?? '');
        $content = '';

        $name = " name=\"{$identifier}[$index]\"";

        if (!empty($cssClass)) {
            $cssClass = " class=\"$cssClass\"";
        }

        if (!empty($placeholder)) {
            $placeholder = " placeholder=\"$placeholder\"";
        }

        switch ($_option['type']) {
            case self::FIELD_TYPE_HTML:
                if ($_option['content'] instanceof Closure) {
                    $_option['content']();
                } else {
                    $content = $_option['content'];
                }

                break;

            case self::FIELD_TYPE_TEXT:
                $content = "<input type=\"text\" value=\"%s\" id=\"{$index}\"{$name}{$placeholder}{$cssClass}/>";
                break;

            case self::FIELD_TYPE_SELECT:
                $content = "<select id=\"{$index}\"{$name}{$placeholder}{$cssClass}>";

                foreach ($_option['options'] as $optionIndex => $optionValue)
                {
                    $selected = '';

                    if(isset($options[$index]))
                    {
                        $selected = $options[$index] === $optionIndex ? ' selected' : '';
                    }

                    $content .= "<option value=\"$optionIndex\"$selected>$optionValue</option>";
                }

                $content .= "</select>";
                break;

            case self::FIELD_TYPE_NUMBER:
                $content = "<input type=\"number\" id=\"{$index}\" value=\"%s\"{$name}{$placeholder}{$cssClass}/>";
                break;

            case self::FIELD_TYPE_TEXTAREA:
                $content = "<textarea id=\"{$index}\"{$name}{$placeholder}{$cssClass}>%s</textarea>";
                break;

            case self::FIELD_TYPE_MEDIA:
                $imageId = empty($options[$index]) ? null : $options[$index];

                $url = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

                if (intval($imageId) > 0) {
                    $url = wp_get_attachment_image_url($imageId, 'medium');
                }

                $content = "<img id=\"{$index}PreviewImage\" src=\"{$url}\" alt=\"Preview Image\" />";

                $content .= "<input type=\"hidden\" name=\"{$identifier}[$index]\" id=\"$index\" value=\"%s\" class=\"regular-text\" /><br/>";

                $content .= "<button type=\"button\" value=\"$index\" class=\"click-open-media-manager\">Bild wählen</button>";

                break;
        }

        if (!empty($index)) {
            $content = sprintf(
                $content,
                isset($options[$index]) ? esc_attr($options[$index]) : ''
            );
        }

        return function () use ($content) {

            echo $content;
        };
    }
}
