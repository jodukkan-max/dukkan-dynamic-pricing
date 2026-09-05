<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Methods related to WooCommerce Order
 *
 * @class RP_WCDPD_WC_Order
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
class RP_WCDPD_WC_Order
{

    // Singleton control
    protected static $instance = false; public static function get_instance() { return self::$instance ? self::$instance : (self::$instance = new self()); }

    /**
     * Constructor
     *
     * @access public
     * @return void
     */
    public function __construct()
    {

        // Override coupon code with cart discount title in order view
        add_filter('woocommerce_order_item_get_code', array($this, 'get_coupon_code'), 99);
    }

    /**
     * Override coupon code with cart discount title in order view
     *
     * @access public
     * @param string $code
     * @return string
     */
    public function get_coupon_code($code)
    {

        // Check if coupon is our cart discount
        if (RP_WCDPD_Controller_Methods_Cart_Discount::coupon_is_cart_discount($code)) {

            // Do this only in admin order view
            if (is_admin() && did_action('woocommerce_admin_order_items_after_fees') && !did_action('woocommerce_admin_order_totals_after_discount')) {

                // Combined
                if ($code === 'rp_wcdpd_combined') {

                    // Set combined title
                    $combined_title = RP_WCDPD_Settings::get('cart_discounts_combined_title');
                    $code = $combined_title ?: esc_html__('Combined Cart Discount', 'rp_wcdpd');
                }
                // Individual
                else {

                    // Get rules
                    $rules = RP_WCDPD_Rules::get('cart_discounts', array('uids' => array($code)), true);

                    // Rule was found
                    if (!empty($rules) && is_array($rules)) {

                        // Get rule title
                        $rule = array_pop($rules);
                        $rule_title = esc_html($rule['title']);
                    }
                    // Rule was not found
                    else {
                        $rule_title = esc_html__('Cart Discount (deleted)', 'rp_wcdpd');
                    }

                    // Set title
                    $code = $rule_title;
                }
            }
        }

        return $code;
    }





}

RP_WCDPD_WC_Order::get_instance();
