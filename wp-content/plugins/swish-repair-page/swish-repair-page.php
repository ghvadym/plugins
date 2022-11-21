<?php

/**
 * Plugin Name: SWISH Repair Page
 * Description: Plugin created a auto filled page for swish payment
 * Version: 1.0
 * Author: wl
 * Author Uri: https://github.com/woplab/samsungservice
 * Text Domain: swish-rp
 *
 * @package WordPress
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once "vendor/autoload.php";

define('SWISH_RP_DIR', plugin_dir_path(__FILE__));
define('SWISH_RP_URL', plugin_dir_url(__FILE__));
define('SWISH_RP_SLUG', plugin_basename(__FILE__));

const SWISH_DB_TABLE_LOG = 'swish_rp_log';
const SWISH_DB_MANUAL_PAYMENT_TABLE_LOG = 'swish_rp_manual_payment_log';
const SWISH_CRON_NAME = 'swish_repair_event';
const SWISH_TIME_PAYMENT = 180;
const SWISH_MANUAL_ORDER_PAGE_NAME = 'Swish';

register_activation_hook(__FILE__, function () {
    Swish_Repair_Database::table_log_install();
    Swish_Repair_Database::manual_table_log_install();
    Swish_Repair_Database::add_manual_payment_page();
});

register_deactivation_hook(__FILE__, function () {
    Swish_Repair_Database::remove_manual_payment_page();
    Swish_Repair_Cron::cron_deactivation();
});

register_uninstall_hook(__FILE__, [Swish_Repair_Database::class, 'clear_after_uninstall']);

new Swish_Repair_Setup();