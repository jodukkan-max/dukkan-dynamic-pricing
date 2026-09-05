<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

// Load dependencies
require_once 'rightpress-condition-field-multiselect.class.php';

/**
 * Condition Field: Multiselect - Days of Month
 *
 * @class RightPress_Condition_Field_Multiselect_Days_Of_Month
 * @package RightPress
 * @author RightPress
 */
abstract class RightPress_Condition_Field_Multiselect_Days_Of_Month extends RightPress_Condition_Field_Multiselect
{

    protected $key = 'days_of_month';

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
     * Load multiselect options
     *
     * @access public
     * @param array $ids
     * @param string $query
     * @return array
     */
    public function load_multiselect_options($ids = array(), $query = '')
    {

        return RightPress_Conditions::get_all_days_of_month($ids, $query);
    }

    /**
     * Get placeholder
     *
     * @access public
     * @return string
     */
    public function get_placeholder()
    {

        return esc_html__('Select days of month', 'rightpress');
    }





}
