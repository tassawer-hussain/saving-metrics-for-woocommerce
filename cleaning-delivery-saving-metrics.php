<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://tassawer.com/
 * @since             1.0.0
 * @package           Cleaning_Delivery_Saving_Metrics
 *
 * @wordpress-plugin
 * Plugin Name:       Cleaning Delivery Saving Metrics
 * Plugin URI:        https://tassawer.com/
 * Description:       Add saving matrices against each product to show on order thank you page, main dashboard and each order record under my-account.
 * Version:           1.0.0
 * Author:            Tassawer
 * Author URI:        https://tassawer.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       cleaning-delivery-saving-metrics
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'CLEANING_DELIVERY_SAVING_METRICS_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-cleaning-delivery-saving-metrics-activator.php
 */
function activate_cleaning_delivery_saving_metrics() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cleaning-delivery-saving-metrics-activator.php';
	Cleaning_Delivery_Saving_Metrics_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-cleaning-delivery-saving-metrics-deactivator.php
 */
function deactivate_cleaning_delivery_saving_metrics() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cleaning-delivery-saving-metrics-deactivator.php';
	Cleaning_Delivery_Saving_Metrics_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_cleaning_delivery_saving_metrics' );
register_deactivation_hook( __FILE__, 'deactivate_cleaning_delivery_saving_metrics' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-cleaning-delivery-saving-metrics.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_cleaning_delivery_saving_metrics() {

	$plugin = new Cleaning_Delivery_Saving_Metrics();
	$plugin->run();

}

/**
 * Admin notice in case of WooCommerce not active.
 */
function cdsm_woo_admin_notice__error() {
    $class = 'notice notice-error';
    $message = __( 'Irks! WooCommerce is not active. Cleaning Delivery Saving Matrics need WooCommerce to be install and activate.', 'sample-text-domain' ); 
    printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) ); 
}

/**
 * Check if WooCommerce is active
 **/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	run_cleaning_delivery_saving_metrics();
} else {
	add_action( 'admin_notices', 'cdsm_woo_admin_notice__error' );
}