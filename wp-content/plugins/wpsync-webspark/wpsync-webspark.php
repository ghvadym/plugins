<?php
/*
Plugin name: WPSync Webspark API.
Description: WP Synchronization woocommerce Products with Webspark API.
Author: Vadym Kravchenko
Text Domain: webspark
Version 1.0
 */

if (!defined('ABSPATH')) exit;

if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    _e('Woocommerce plugin isn\'t active. Please, install Woocommerce and activate it.');
    exit;
}

require_once('vendor/autoload.php');

define('WW_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('WW_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WW_PLUGIN_NAME', plugin_basename(__DIR__));

const WW_PLUGIN_SLUG = 'wpsync_webspark';

register_deactivation_hook(__FILE__, [WW_Event::class, 'deactivation_plugin']);

new WW_Init();