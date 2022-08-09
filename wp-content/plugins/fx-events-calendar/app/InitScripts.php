<?php

namespace Includes;

class InitScripts
{
    public static function init()
    {
        add_action('wp_enqueue_scripts', [self::class, 'theme_scripts']);
    }

    public static function theme_scripts()
    {
        wp_enqueue_style('fx-events-styles', PLUGIN_URL . 'assets/css/app.css', [], time());
        wp_enqueue_script('jquery');
        wp_enqueue_script('fx-events-scripts', PLUGIN_URL . 'assets/js/app.js', ['jquery'], time(), true);
        wp_localize_script('fx-events-scripts', 'fx_theme_ajax', ['ajaxurl' => admin_url('admin-ajax.php')]);

        self::loadThemeStyles('event.css');
    }

    static function loadThemeStyles($file): void
    {
        $path = '/' . PLUGIN_NAME . "/styles/$file";

        if (!file_exists(THEME_PATH . $path)) {
            return;
        }

        wp_enqueue_style("fx-events-styles-$file", THEME_URL . $path);
    }
}