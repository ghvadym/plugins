<?php
/*
Plugin name: FX Events Calendar
Description: Create calendar from current post types.
Author: Flexi
Text Domain: fxevents
Version 1.0
 */

use Includes\Load;

if (!defined('ABSPATH')) exit;

define('PLUGIN_PATH', plugin_dir_path(__FILE__));
define('PLUGIN_URL', plugin_dir_url(__FILE__));
define('PLUGIN_NAME', plugin_basename(__DIR__));
define('THEME_PATH', get_template_directory());
define('THEME_URL', get_template_directory_uri());

foreach (glob(PLUGIN_PATH . 'app/*.php') as $file) {
    include_once $file;
}

Load::init();