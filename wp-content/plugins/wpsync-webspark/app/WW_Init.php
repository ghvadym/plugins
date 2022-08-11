<?php


class WW_Init
{
    public function __construct()
    {
        add_action('admin_menu', [self::class, 'admin_menu_page']);
        add_action('admin_enqueue_scripts', [self::class, 'admin_scripts']);
    }

    static function admin_scripts(): void
    {
        wp_enqueue_style('ww-plugin-styles', WW_PLUGIN_URL . '/assets/css/app.css', [], time());
    }

    static function admin_menu_page(): void
    {
        add_menu_page(
            __('WPSync Webspark', 'webspark'),
            __('WPSync Webspark', 'webspark'),
            'manage_options',
            WW_PLUGIN_SLUG,
            [self::class, 'admin_menu_page_init'],
            'dashicons-networking',
            20
        );
    }

    static function admin_menu_page_init(): void
    {
        if (isset($_GET['ww_update_products'])) {
            $prodApiResponse = WW_API_Products::api_send_response();

            if (!isset($prodApiResponse->message) || $prodApiResponse->message != 'OK' || empty($prodApiResponse->data)) {
                _e('Bad response...');
            }

            $data = WW_Functions::update_products_from_api($prodApiResponse->data);
        }

        require WW_Functions::get_path('admin-template');
    }
}