<?php


class Swish_Repair_Cron
{
    static function cron_init()
    {
        add_action('wp', [self::class, 'swish_orders_event']);
        add_action(SWISH_CRON_NAME, [self::class, 'cron_call']);
    }

    static function swish_orders_event()
    {
        if (wp_next_scheduled(SWISH_CRON_NAME)) {
            return;
        }

        wp_schedule_event(time(), 'five_min', SWISH_CRON_NAME);
    }

    static function cron_call()
    {
        //Swish_Repair_Fixably_API::check_orders();
    }

    static function cron_deactivation()
    {
        wp_unschedule_hook(SWISH_CRON_NAME);
    }
}