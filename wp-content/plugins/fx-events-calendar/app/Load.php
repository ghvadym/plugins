<?php


namespace Includes;


class Load
{
    public static function init()
    {
        InitScripts::init();
        Metabox::init();
        MainSettings::init();
        Shortcode::init();
        Ajax::init();
    }
}