<?php


class WW_Event
{
    const WW_CRON_JOB_NAME = 'wpsync_webspark_event';

    static function cron_init()
    {
        add_action('wp', [self::class, 'admin_webspark_cron']);
        //add_action(self::WW_CRON_JOB_NAME, [self::class, 'cron_call']);
    }

    static function admin_webspark_cron()
    {
        if (wp_next_scheduled(self::WW_CRON_JOB_NAME)) {
            return;
        }

        $time = apply_filters('wpsync_webspark_cron_schedule_time', time());

        $recurrence = apply_filters('wpsync_webspark_cron_schedule_recurrence', 'hourly');

        wp_schedule_event($time, $recurrence, self::WW_CRON_JOB_NAME);
    }

    static function cron_call()
    {
        $prodApiResponse = WW_API_Products::api_send_response();

        if (isset($prodApiResponse->message) && $prodApiResponse->message == 'OK' || !empty($prodApiResponse->data)) {
            WW_Functions::update_products_from_api($prodApiResponse->data);
        }
    }

    static function deactivation_plugin()
    {
        wp_clear_scheduled_hook(self::WW_CRON_JOB_NAME);
    }
}