<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Condition: Time - Days of Month
 *
 * @class RP_WCDPD_Condition_Time_Days_Of_Month
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
class RP_WCDPD_Condition_Time_Days_Of_Month extends RightPress_Condition_Time_Days_Of_Month
{

    protected $plugin_prefix = RP_WCDPD_PLUGIN_PRIVATE_PREFIX;

    protected $contexts = array(
        'product_pricing',
        'cart_discounts',
        'checkout_fees',
    );

    // Singleton instance
    protected static $instance = false;

    /**
     * Constructor
     *
     * @access public
     * @return void
     */
    public function __construct()
    {

        parent::__construct();
    }





}

RP_WCDPD_Condition_Time_Days_Of_Month::get_instance();
