<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://tassawer.com/
 * @since      1.0.0
 *
 * @package    Cleaning_Delivery_Saving_Metrics
 * @subpackage Cleaning_Delivery_Saving_Metrics/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Cleaning_Delivery_Saving_Metrics
 * @subpackage Cleaning_Delivery_Saving_Metrics/includes
 * @author     Tassawer <hello@tassawer.com>
 */
class Cleaning_Delivery_Saving_Metrics_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'cleaning-delivery-saving-metrics',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
