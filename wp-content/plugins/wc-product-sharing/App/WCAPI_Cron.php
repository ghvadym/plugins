<?php


class WCAPI_Cron
{
    static function cron_init()
    {
        add_action('wp', [self::class, 'admin_wcapi_cron']);
        add_action(WCAPI_CRON_POSTS, [self::class, 'cron_call']);
    }

    static function admin_wcapi_cron()
    {
        if (wp_next_scheduled(WCAPI_CRON_POSTS)) {
            return;
        }

        $time = apply_filters('wcapi_cron_schedule_event_time', strtotime('06:00:00'));

        $recurrence = apply_filters('wcapi_cron_schedule_event_recurrence', 'daily');

        wp_schedule_event($time, $recurrence, WCAPI_CRON_POSTS);
    }

    static function cron_call()
    {
        $token = WCAPI_Functions::get_token();

        if (!$token) {
            return;
        }

        WCAPI_Init::admin_get_products_by_token($token);
    }
}