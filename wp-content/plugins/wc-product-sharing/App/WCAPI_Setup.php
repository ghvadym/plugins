<?php


class WCAPI_Setup
{
    static function on_activation()
    {
        WCAPI_Settings::create_table();
        WCAPI_Init::wcapi_default_settings();
    }

    static function on_deactivation()
    {
        WCAPI_Functions::wcapi_user_logout();

        delete_option('wc_api_settings');

        self::drop_table();

        wp_unschedule_hook(WCAPI_CRON_POSTS);
    }

    static function drop_table()
    {
        global $wpdb;
        $table = $wpdb->prefix . WCAPI_TABLE_POSTS;

        $wpdb->query("DROP TABLE {$table}");
    }
}