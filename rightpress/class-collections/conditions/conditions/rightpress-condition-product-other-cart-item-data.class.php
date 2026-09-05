<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

// Load dependencies
require_once 'rightpress-condition-product-other.class.php';

/**
 * Condition: Product Other - Cart Item Data
 *
 * @class RightPress_Condition_Product_Other_Cart_Item_Data
 * @package RightPress
 * @author RightPress
 */
abstract class RightPress_Condition_Product_Other_Cart_Item_Data extends RightPress_Condition_Product_Other
{

    protected $key          = 'cart_item_data';
    protected $method       = 'meta';
    protected $fields       = array(
        'before'    => array('meta_key'),
        'after'     => array('text'),
    );
    protected $main_field   = 'text';
    protected $position = 8;

    /**
     * Constructor
     *
     * @access public
     * @return void
     */
    public function __construct()
    {

        parent::__construct();

        $this->hook();
    }

    /**
     * Get label
     *
     * @access public
     * @return string
     */
    public function get_label()
    {

        return esc_html__('Cart item data', 'rightpress');
    }

    /**
     * Get value to compare against condition
     *
     * @access public
     * @param array $params
     * @return mixed
     */
    public function get_value($params)
    {

        // Get meta key
        $meta_key = $params['condition']['meta_key'];

        // Check if cart item and data key is set
        if (!empty($params['cart_item']) && isset($params['cart_item'][$meta_key])) {

            // Return meta
            return $params['cart_item'][$meta_key];
        }

        // Cart item or meta key is not set
        return null;
    }





}
