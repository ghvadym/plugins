<?php

namespace Includes;

class Shortcode
{
    public static function init()
    {
        add_shortcode('fx_event_calendar', [self::class, 'getCalendar']);
        add_filter('acf/format_value/type=text', 'do_shortcode');
    }

    public static function getCalendar()
    {
        include Functions::getPath('calendar');
    }
}