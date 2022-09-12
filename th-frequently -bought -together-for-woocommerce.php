<?php
/**
 * Plugin Name: TH Frequently Bought Together for WooCommerce
 * Plugin URI: https://themehunk.com
 * Description: Increase your sales with personalized product recommendations.             
 * Version: 1.1.4
 * Author:ThemeHunk
 * Author URI:https://themehunk.com
 * Requires at least: 5.0
 * Tested up to: 6.0.1
 * WC requires at least: 3.0
 * WC tested up to: 6.8
 * Domain Path:/languages
 * Text Domain:th-bought-together
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if (!defined('THWBT_PLUGIN_FILE')) {
    define('THWBT_PLUGIN_FILE', __FILE__);
}

if (!defined('THWBT_PLUGIN_URI')) {
    define( 'THWBT_PLUGIN_URI', plugin_dir_url( __FILE__ ) );
}

if (!defined('THWBT_PLUGIN_PATH')) {
    define( 'THWBT_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}

if (!defined('THWBT_PLUGIN_DIRNAME')) {
    define( 'THWBT_PLUGIN_DIRNAME', dirname( plugin_basename( __FILE__ ) ) );
}

if (!defined('THWBT_PLUGIN_BASENAME')) {
    define( 'THWBT_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
}

if (!defined('THWBT_IMAGES_URI')) {
define( 'THWBT_IMAGES_URI', trailingslashit( plugin_dir_url( __FILE__ ) . 'images' ) );
}

if (!defined('THWBT_VERSION')) {
    $plugin_data = get_file_data(__FILE__, array('version' => 'Version'), false);
    define('THWBT_VERSION', $plugin_data['version']);
} 

require_once("inc/thwbt-main.php");