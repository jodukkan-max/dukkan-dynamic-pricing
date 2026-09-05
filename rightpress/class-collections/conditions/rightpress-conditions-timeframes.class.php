<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Timeframes management class
 *
 * @class RightPress_Conditions_Timeframes
 * @package RightPress
 * @author RightPress
 */
final class RightPress_Conditions_Timeframes
{

    protected $timeframes = null;

    // Singleton control
    protected static $instance = false; public static function get_instance() { return self::$instance ? self::$instance : (self::$instance = new self()); }

    /**
     * Get timeframes
     *
     * NOTE:
     * - 'value' stands for start datetime
     * - 'value_to' stands for end datetime
     * - 'value_to' is optional, current datetime will be used if not specified
     * - 'value' was not changed to 'value_from' after introducing 'value_to' in order to maintain compatibility with 3rd party code
     *
     * @access public
     * @return array
     */
    public static function get_timeframes()
    {

        // Get instance
        $instance = self::get_instance();

        // Check if timeframes are defined
        if ($instance->timeframes === null) {

            // Define timeframes
            $instance->timeframes = array(

                // Current
                'current' => array(
                    'label'     => esc_html__('Current', 'rightpress'),
                    'children'  => array(
                        'current_day'   => array(
                            'label' => esc_html__('current day', 'rightpress'),
                            'value' => 'midnight',
                        ),
                        'current_week'   => array(
                            'label' => esc_html__('current week', 'rightpress'),
                            'value' => RightPress_Conditions_Timeframes::get_current_week_value(),
                        ),
                        'current_month'   => array(
                            'label' => esc_html__('current month', 'rightpress'),
                            'value' => 'midnight first day of this month',
                        ),
                        'current_year'   => array(
                            'label' => esc_html__('current year', 'rightpress'),
                            'value' => 'midnight first day of january',
                        ),
                    ),
                ),

                // Last
                'last' => array(
                    'label'     => esc_html__('Last', 'rightpress'),
                    'children'  => array(
                        'last_day'   => array(
                            'label'     => esc_html__('last day', 'rightpress'),
                            'value'     => 'midnight last day',
                            'value_to'  => '23:59:59 last day',
                        ),
                        'last_week'   => array(
                            'label'     => esc_html__('last week', 'rightpress'),
                            'value'     => RightPress_Conditions_Timeframes::get_last_week_value(),
                            'value_to'  => ('23:59:59 -1 ' . RightPress_Help::get_literal_end_of_week()),
                        ),
                        'last_month'   => array(
                            'label'     => esc_html__('last month', 'rightpress'),
                            'value'     => 'midnight first day of last month',
                            'value_to'  => '23:59:59 last day of last month',
                        ),
                        'last_year'   => array(
                            'label'     => esc_html__('last year', 'rightpress'),
                            'value'     => 'midnight first day of January last year',
                            'value_to'  => '23:59:59 last day of December last year',
                        ),
                    ),
                ),

                // Days
                'days' => array(
                    'label'     => esc_html__('Days', 'rightpress'),
                    'children'  => array(),
                ),

                // Weeks
                'weeks' => array(
                    'label'     => esc_html__('Weeks', 'rightpress'),
                    'children'  => array(),
                ),

                // Months
                'months' => array(
                    'label'     => esc_html__('Months', 'rightpress'),
                    'children'  => array(),
                ),

                // Years
                'years' => array(
                    'label'     => esc_html__('Years', 'rightpress'),
                    'children'  => array(),
                ),
            );

            // Generate list of days
            for ($i = 1; $i <= 6; $i++) {
                $instance->timeframes['days']['children'][$i . '_day'] = array(
                    'label' => $i . ' ' . _n('day', 'days', $i, 'rightpress'),
                    'value' => '-' . $i . ($i === 1 ? ' day' : ' days'),
                );
            }

            // Generate list of weeks
            for ($i = 1; $i <= 4; $i++) {
                $instance->timeframes['weeks']['children'][$i . '_week'] = array(
                    'label' => $i . ' ' . _n('week', 'weeks', $i, 'rightpress'),
                    'value' => '-' . $i . ($i === 1 ? ' week' : ' weeks'),
                );
            }

            // Generate list of months
            for ($i = 1; $i <= 12; $i++) {
                $instance->timeframes['months']['children'][$i . '_month'] = array(
                    'label' => $i . ' ' . _n('month', 'months', $i, 'rightpress'),
                    'value' => '-' . $i . ($i === 1 ? ' month' : ' months'),
                );
            }

            // Generate list of years
            for ($i = 2; $i <= 10; $i++) {
                $instance->timeframes['years']['children'][$i . '_year'] = array(
                    'label' => $i . ' ' . _n('year', 'years', $i, 'rightpress'),
                    'value' => '-' . $i . ($i === 1 ? ' year' : ' years'),
                );
            }

            // Allow developers to override
            $instance->timeframes = apply_filters('rightpress_conditions_timeframes', $instance->timeframes);
        }

        return $instance->timeframes;
    }

    /**
     * Get start of current week value
     *
     * @access public
     * @return string
     */
    public static function get_current_week_value()
    {

        // Today is first day of week
        if ((int) RightPress_Help::get_adjusted_datetime(null, 'w') === RightPress_Help::get_start_of_week()) {
            return 'midnight';
        }
        else {
            return 'midnight last ' . RightPress_Help::get_literal_start_of_week();
        }
    }

    /**
     * Get start of last week value
     *
     * @access public
     * @return string
     */
    public static function get_last_week_value()
    {

        $literal_start_of_week = RightPress_Help::get_literal_start_of_week();

        // Today is first day of week
        if ((int) RightPress_Help::get_adjusted_datetime(null, 'w') === RightPress_Help::get_start_of_week()) {
            return 'midnight -1 ' . $literal_start_of_week;
        }
        else {
            return 'midnight -2 ' . $literal_start_of_week;
        }
    }

    /**
     * Get start datetime from timeframe
     *
     * @access public
     * @param string $option_key
     * @return object
     */
    public static function get_start_datetime_from_timeframe($option_key)
    {

        return RightPress_Conditions_Timeframes::get_datetime_from_timeframe($option_key, 'value');
    }

    /**
     * Get end datetime from timeframe
     *
     * @access public
     * @param string $option_key
     * @return object
     */
    public static function get_end_datetime_from_timeframe($option_key)
    {

        return RightPress_Conditions_Timeframes::get_datetime_from_timeframe($option_key, 'value_to');
    }

    /**
     * Get datetime from timeframe
     *
     * @access public
     * @param string $option_key
     * @param string $context
     * @return object
     */
    public static function get_datetime_from_timeframe($option_key, $context)
    {

        // Get timeframes
        $timeframes = RightPress_Conditions_Timeframes::get_timeframes();

        // Iterate over timeframes
        foreach ($timeframes as $timeframe_group_key => $timeframe_group) {

            // Check if current timeframe was requested
            if (isset($timeframe_group['children'][$option_key])) {

                // Check if context is defined
                if (isset($timeframe_group['children'][$option_key][$context])) {

                    // Get datetime object
                    return RightPress_Help::get_datetime_object($timeframe_group['children'][$option_key][$context], false);
                }
            }
        }

        return null;
    }





}

RightPress_Conditions_Timeframes::get_instance();
