<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

// Load dependencies
require_once 'rightpress-condition-time.class.php';

/**
 * Condition: Time - Days of Month
 *
 * @class RightPress_Condition_Time_Days_Of_Month
 * @package RightPress
 * @author RightPress
 */
abstract class RightPress_Condition_Time_Days_Of_Month extends RightPress_Condition_Time
{

    protected $key      = 'days_of_month';
    protected $method   = 'list';
    protected $fields   = array(
        'after' => array('days_of_month'),
    );
    protected $position = 50;

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

        return esc_html__('Days of month', 'rightpress');
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

        $value = [];

        // Get current datetime
        $date = RightPress_Help::get_datetime_object();

        // Get current day number
        $day_number = $date->format('j');

        // Add current day number
        $value[] = $day_number;

        // Get number of days in current month
        $number_of_days = $date->format('t');

        // Maybe add last day
        if ((int) $day_number === (int) $number_of_days) {
            $value[] = 'last_day';
        }

        return $value;
    }





}
