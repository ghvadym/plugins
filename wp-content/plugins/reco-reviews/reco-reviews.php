<?php
/*
Plugin name: Reco Reviews Slider
Description: Generate a slider with the Reco reviews
Author: Invistic AB
Author URI:  https://invistic.com/
Text Domain: reco-reviews
Version 1.0
Requires PHP: 7.4
 */

if (!defined('ABSPATH')) exit;

require_once('vendor/autoload.php');

define('RECO_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('RECO_PLUGIN_URL', plugin_dir_url(__FILE__));
define('RECO_PLUGIN_NAME', plugin_basename(__DIR__));

new Reco_Setup();