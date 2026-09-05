<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Promotion: Cart Item Descriptions
 *
 * @class RP_WCDPD_Promotion_Cart_Item_Descriptions
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
class RP_WCDPD_Promotion_Cart_Item_Descriptions
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

        // Register settings structure
        add_filter('rp_wcdpd_settings_structure', array($this, 'register_settings_structure'), 160);

        // Set up promotion tool
        add_action('init', array($this, 'set_up_promotion_tool'));
    }

    /**
     * Register settings structure
     *
     * @access public
     * @param array $settings
     * @return array
     */
    public function register_settings_structure($settings)
    {

        $settings['promo']['children']['cart_item_descriptions'] = array(
            'title' => esc_html__('Cart Item Descriptions', 'rp_wcdpd'),
            'info'  => esc_html__('Displays public descriptions of product pricing rules that are applicable to cart items.', 'rp_wcdpd'),
            'children' => array(
                'promo_cart_item_descriptions' => array(
                    'title'     => esc_html__('Enable', 'rp_wcdpd'),
                    'type'      => 'checkbox',
                    'default'   => '0',
                ),
                'promo_cart_item_descriptions_label' => array(
                    'title'     => esc_html__('Label', 'rp_wcdpd'),
                    'type'      => 'text',
                    'default'   => esc_html__('Promotion', 'rp_wcdpd'),
                    'required'  => true,
                ),
            ),
        );

        return $settings;
    }

    /**
     * Set up promotion tool
     *
     * @access public
     * @return void
     */
    public function set_up_promotion_tool()
    {

        // Check this promotion tool is active
        if (!RP_WCDPD_Settings::get('promo_cart_item_descriptions')) {
            return;
        }

        // Maybe display public description after cart item name (classic cart)
        add_action('woocommerce_after_cart_item_name', array($this, 'maybe_display_public_description_after_cart_item_name'), 10, 2);

        // Maybe add public description to item data (cart block)
        add_filter('woocommerce_get_item_data', array($this, 'maybe_add_description_to_cart_block_item_data'), 10, 2);
    }

    /**
     * Maybe display public description after cart item name
     *
     * @access public
     * @param array $cart_item
     * @param string $cart_item_key
     * @return void
     */
    public function maybe_display_public_description_after_cart_item_name($cart_item, $cart_item_key)
    {

        $descriptions = array();

        // Get cart item price changes
        $price_changes = RightPress_Product_Price_Cart::get_cart_item_price_changes($cart_item_key);

        // No price changes to work with
        if (empty($price_changes['all_changes']['rp_wcdpd'])) {
            return;
        }

        // Iterate over price changes
        foreach ($price_changes['all_changes']['rp_wcdpd'] as $price_change_key => $price_change) {

            // Check if public description is set
            if (!empty($price_change['rule']['public_note'])) {

                // Format description
                $formatted_description = '<p class="rp_wcdpd_promotion_cart_item_description">' . $price_change['rule']['public_note'] . '</p>';

                // Allow developers to override and add to main array
                $descriptions[$price_change_key] = apply_filters('rp_wcdpd_promotion_cart_item_descriptions_formatted_description', $formatted_description, $price_change['rule']['public_note'], $price_change['rule'], $cart_item, $cart_item_key);
            }
        }

        // No descriptions to display
        if (empty($descriptions)) {
            return;
        }

        // Format descriptions
        $formatted_descriptions = '<div class="rp_wcdpd_promotion_cart_item_descriptions">' . join(' ', $descriptions) . '</div>';

        // Allow developers to override and print descriptions
        echo wp_kses_post(apply_filters('rp_wcdpd_promotion_cart_item_descriptions_formatted_descriptions', $formatted_descriptions));
    }

    /**
     * Maybe add description to Cart Block item data (Store API)
     *
     * @access public
     * @param array $item_data
     * @param array $cart_item
     * @return array
     */
    public function maybe_add_description_to_cart_block_item_data($item_data, $cart_item)
    {

        // Ensure we have the cart item key
        if (empty($cart_item['key'])) {
            return $item_data;
        }

        $cart_item_key = $cart_item['key'];

        // Get cart item price changes
        $price_changes = RightPress_Product_Price_Cart::get_cart_item_price_changes($cart_item_key);

        // No price changes to work with
        if (empty($price_changes['all_changes']['rp_wcdpd'])) {
            return $item_data;
        }

        // Iterate over price changes
        foreach ($price_changes['all_changes']['rp_wcdpd'] as $price_change_key => $price_change) {

            // Check if public description is set
            if (!empty($price_change['rule']['public_note'])) {

                // Get label
                $label = apply_filters('rp_wcdpd_promotion_cart_item_descriptions_label', wp_kses_post(RP_WCDPD_Settings::get('promo_cart_item_descriptions_label')), $price_change['rule'], $price_change['rule']['public_note'], $price_change);

                // Clean the note (blocks handle HTML differently, text is safer here)
                $note_text = wp_strip_all_tags($price_change['rule']['public_note']);

                // Add to item data
                $item_data[] = array(
                    'key'       => $label,
                    'value'     => $note_text,
                    'display'   => $note_text,
                );
            }
        }

        return $item_data;
    }





}

RP_WCDPD_Promotion_Cart_Item_Descriptions::get_instance();
