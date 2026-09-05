<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Condition Field: Multiselect - Days of Month
 *
 * @class RP_WCDPD_Condition_Field_Multiselect_Days_Of_Month
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
class RP_WCDPD_Condition_Field_Multiselect_Days_Of_Month extends RightPress_Condition_Field_Multiselect_Days_Of_Month
{

    protected $plugin_prefix = RP_WCDPD_PLUGIN_PRIVATE_PREFIX;

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

RP_WCDPD_Condition_Field_Multiselect_Days_Of_Month::get_instance();
