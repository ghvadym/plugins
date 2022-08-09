<?php
/*
Plugin name: FX WC API Products
Description: Plugin enable to connect API and use woocommerce products from the father website.
Author: Flexi
Text Domain: wcapi
Version 1.0
 */

if (!defined('ABSPATH')) exit;

require_once('vendor/autoload.php');

define('WCAPI_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('WCAPI_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WCAPI_PLUGIN_NAME', plugin_basename(__DIR__));

const WCAPI_TABLE_POSTS = 'wcapi_products';
const WCAPI_PRODUCTS_PAGE = 'wcapi_products_page';
const WCAPI_SETTINGS_PAGE = 'wcapi_settings_page';
const WCAPI_DEMO_PRODUCTS_PAGE = 'wcapi_demo_page';
const WCAPI_CRON_POSTS = 'wcapi_cron_get_posts';

register_activation_hook(__FILE__, [WCAPI_Setup::class, 'on_activation']);
register_deactivation_hook(__FILE__, [WCAPI_Setup::class, 'on_deactivation']);

new WCAPI_Init();