<?php

/**
 * Plugin Name: WooCommerce Dynamic Pricing & Discounts
 * Plugin URI: https://github.com/jodukkan-max/dukkan-dynamic-pricing/
 * Update URI: https://github.com/jodukkan-max/dukkan-dynamic-pricing/
 * Description: All-purpose product pricing, cart discount and checkout fee tool for WooCommerce
 * Author: dukkan
 * Author URI: https://github.com/jodukkan-max/
 *
 * Text Domain: rp_wcdpd
 * Domain Path: /languages
 *
 * Version: 1.0.2
 *
 * Requires at least: 4.0
 * Tested up to: 6.9
 *
 * WC requires at least: 3.0
 * WC tested up to: 10.5
 *
 * @package WooCommerce Dynamic Pricing & Discounts
 * @category Core
 * @author dukkan
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define Constants
define('RP_WCDPD_PLUGIN_KEY', 'wc-dynamic-pricing-and-discounts');
define('RP_WCDPD_PLUGIN_PUBLIC_PREFIX', 'rp_wcdpd_');
define('RP_WCDPD_PLUGIN_PRIVATE_PREFIX', 'rp_wcdpd_');
define('RP_WCDPD_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('RP_WCDPD_PLUGIN_URL', plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__)));
define('RP_WCDPD_ADMIN_CAPABILITY', 'manage_woocommerce');
define('RP_WCDPD_SUPPORT_PHP', '7.2');
define('RP_WCDPD_SUPPORT_WP', '4.0');
define('RP_WCDPD_SUPPORT_WC', '3.0');
define('RP_WCDPD_VERSION', '1.0.2');

// Load main plugin class
require_once 'rp_wcdpd.class.php';

// Initialize automatic updates from GitHub releases
require_once(RP_WCDPD_PLUGIN_PATH . 'classes/rp-wcdpd-github-updater.class.php');
RP_WCDPD_GitHub_Updater::init(__FILE__, RP_WCDPD_VERSION);

// Declare compatibility with WooCommerce HPOS
add_action('before_woocommerce_init', function() {
    if (class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});
