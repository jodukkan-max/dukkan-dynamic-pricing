<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Condition: Product Other - Cart Item Data
 *
 * @class RP_WCDPD_Condition_Product_Other_Cart_Item_Data
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
class RP_WCDPD_Condition_Product_Other_Cart_Item_Data extends RightPress_Condition_Product_Other_Cart_Item_Data
{

    protected $plugin_prefix = RP_WCDPD_PLUGIN_PRIVATE_PREFIX;

    protected $contexts = array(
        'product_pricing_product',
        'cart_discounts_product',
        'checkout_fees_product',
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

RP_WCDPD_Condition_Product_Other_Cart_Item_Data::get_instance();
