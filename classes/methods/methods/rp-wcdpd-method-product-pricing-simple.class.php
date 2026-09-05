<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load dependencies
if (!class_exists('RP_WCDPD_Method_Product_Pricing')) {
    require_once('rp-wcdpd-method-product-pricing.class.php');
}

/**
 * Product Pricing Method: Simple
 *
 * @class RP_WCDPD_Method_Product_Pricing_Simple
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
class RP_WCDPD_Method_Product_Pricing_Simple extends RP_WCDPD_Method_Product_Pricing
{

    protected $key              = 'simple';
    protected $group_key        = 'simple';
    protected $group_position   = 10;
    protected $position         = 10;

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

        $this->hook_group();
        $this->hook();
    }

    /**
     * Get group label
     *
     * @access public
     * @return string
     */
    public function get_group_label()
    {
        return esc_html__('Simple', 'rp_wcdpd');
    }

    /**
     * Get label
     *
     * @access public
     * @return string
     */
    public function get_label()
    {
        return esc_html__('Simple adjustment', 'rp_wcdpd');
    }

    /**
     * Get cart item adjustments by rule
     *
     * @access public
     * @param array $rule
     * @param array $cart_items
     * @return array
     */
    public function get_adjustments($rule, $cart_items = null)
    {
        $adjustments = array();

        // Iterate over cart items
        foreach ($cart_items as $cart_item_key => $cart_item) {

            // Check if rule applies to current cart item
            if (RP_WCDPD_Controller_Conditions::object_conditions_are_matched($rule, array('cart_item' => $cart_item, 'cart_items' => $cart_items))) {

                // Add adjustment to main array
                $adjustments[$cart_item_key] = array(
                    'rule' => $rule,
                    // Original regular price - used by percentage fee/discount markup/markdown pricing methods
                    'regular_price' => (float) $cart_item['data']->get_regular_price('edit'),
                );

                // Get base price for reference amount calculation
                $base_price = $this->get_base_price_for_reference_amount_calculation($cart_item_key, $cart_item);

                // Calculate reference amount
                $adjustments[$cart_item_key]['reference_amount'] = $this->get_reference_amount($adjustments[$cart_item_key], $base_price, $cart_item['quantity'], $cart_item['data'], $cart_item);
            }
        }

        return $adjustments;
    }

    /**
     * Apply adjustment to prices
     *
     * Routes percentage fee and percentage discount methods to the dedicated
     * handler so that fees mark up the regular price and discounts create a
     * sale price from the (possibly marked-up) regular price.
     *
     * @access public
     * @param array $prices
     * @param array $adjustment
     * @param string $cart_item_key
     * @return array
     */
    public function apply_adjustment_to_prices($prices, $adjustment, $cart_item_key = null)
    {

        // Reference rule
        $rule = $adjustment['rule'];

        // Route percentage fee/discount to dedicated percentage handler
        if (isset($rule['pricing_method']) && in_array($rule['pricing_method'], array('fee__percentage', 'discount__percentage'), true)) {
            return $this->apply_percentage_adjustment_to_prices($prices, $adjustment, $cart_item_key);
        }

        // Delegate all other pricing methods to parent handler
        return parent::apply_adjustment_to_prices($prices, $adjustment, $cart_item_key);
    }

    /**
     * Apply percentage fee or percentage discount adjustment to prices
     *
     * Percentage fee marks up the regular price, percentage discount creates a
     * sale price from the current regular price (marked up by fee rules if any).
     *
     * @access public
     * @param array $prices
     * @param array $adjustment
     * @param string $cart_item_key
     * @return array
     */
    public function apply_percentage_adjustment_to_prices($prices, $adjustment, $cart_item_key = null)
    {

        // Reference rule
        $rule = $adjustment['rule'];

        // Get receive quantity
        $receive_quantity = !empty($adjustment['receive_quantity']) ? (int) $adjustment['receive_quantity'] : RightPress_Product_Price_Breakdown::get_price_ranges_total_quantity($prices['ranges']);

        // Track quantity left after each iteration
        $quantity_left = $receive_quantity;

        // Iterate over price ranges
        foreach ($prices['ranges'] as $price_range_index => $price_range) {

            // Get quantity to adjust
            $price_range_quantity = RightPress_Product_Price_Breakdown::get_price_range_quantity($price_range);
            $price_range_adjust_quantity = $quantity_left < $price_range_quantity ? $quantity_left : $price_range_quantity;
            $quantity_left -= $price_range_adjust_quantity;

            // Determine the current regular price to anchor percentage calculations to.
            // Preference: marked-up regular price (set by a previous fee rule), then the
            // product's original regular price, then the range base price as a fallback.
            $base_regular = $price_range['base_price'];
            if (isset($adjustment['regular_price']) && $adjustment['regular_price'] !== null) {
                $base_regular = (float) $adjustment['regular_price'];
            }
            if (isset($price_range['regular_price']) && $price_range['regular_price'] !== null) {
                $base_regular = (float) $price_range['regular_price'];
            }

            // Percentage fee: mark up the regular price (both price and regular_price channels)
            if ($rule['pricing_method'] === 'fee__percentage') {
                $marked_up_regular = RP_WCDPD_Pricing::adjust_amount($base_regular, $rule['pricing_method'], $rule['pricing_value']);
                $this->prepare_and_set_percentage_markup_price($prices, $price_range_index, $price_range_adjust_quantity, $marked_up_regular, $base_regular, $adjustment, $cart_item_key, array('receive_quantity' => $receive_quantity));
            }
            // Percentage discount: create a sale price from the (marked-up) regular price
            else {
                $sale_price = RP_WCDPD_Pricing::adjust_amount($base_regular, $rule['pricing_method'], $rule['pricing_value']);
                $this->prepare_and_set_adjusted_price($prices, $price_range_index, $price_range_adjust_quantity, $sale_price, $base_regular, $adjustment, $cart_item_key, array('receive_quantity' => $receive_quantity));
            }

            // No more units to adjust
            if ($quantity_left <= 0) {
                break;
            }
        }

        // Return adjusted prices
        return $prices;
    }

    /**
     * Prepare and set percentage fee markup price (regular price channel)
     *
     * @access public
     * @param array $prices
     * @param int $price_range_index
     * @param int $quantity
     * @param float $marked_up_regular
     * @param float $price_to_adjust
     * @param array $adjustment
     * @param string $cart_item_key
     * @param array $extra_filter_params
     * @return void
     */
    public function prepare_and_set_percentage_markup_price(&$prices, $price_range_index, $quantity, $marked_up_regular, $price_to_adjust, $adjustment, $cart_item_key = null, $extra_filter_params = array())
    {

        // Round marked-up regular price to get predictable results
        $marked_up_regular = RightPress_Product_Price::round($marked_up_regular);

        // Allow developers to override
        $marked_up_regular = (float) apply_filters('rp_wcdpd_product_pricing_adjusted_unit_price', $marked_up_regular, $price_to_adjust, $adjustment, $quantity, $extra_filter_params, $cart_item_key);

        // Get change key
        $change_key = RightPress_Help::get_hash(false, array(
            $adjustment['rule']['uid'],
            $cart_item_key,
        ));

        // Format changes for prices array
        $changes = array('rp_wcdpd' => array(
            $change_key => $adjustment,
        ));

        // Set adjusted price to prices array, carrying the marked-up regular price in a separate channel
        RightPress_Product_Price_Breakdown::set_price_to_prices_array($prices, 'rp_wcdpd', $marked_up_regular, $price_range_index, $quantity, $changes, false, $marked_up_regular);
    }



}

RP_WCDPD_Method_Product_Pricing_Simple::get_instance();
