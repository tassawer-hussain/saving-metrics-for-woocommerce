<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://tassawer.com/
 * @since      1.0.0
 *
 * @package    Cleaning_Delivery_Saving_Metrics
 * @subpackage Cleaning_Delivery_Saving_Metrics/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Cleaning_Delivery_Saving_Metrics
 * @subpackage Cleaning_Delivery_Saving_Metrics/public
 * @author     Tassawer <hello@tassawer.com>
 */
class Cleaning_Delivery_Saving_Metrics_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	private $options;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->options = get_option( 'cdsm_settings' );

		add_action('woocommerce_before_thankyou', array( $this, 'cdsm_calculations_on_placing_order' ) );
		add_action('woocommerce_account_dashboard', array( $this, 'cdsm_output_total_save_on_dashboard' ) );
		add_action('woocommerce_order_details_before_order_table', array( $this, 'cdsm_output_saving_on_my_account' ), 20, 1);
		add_action('woocommerce_after_checkout_billing_form', array( $this, 'cdsm_get_max_turnaround_time' ), 20);
		add_action('woocommerce_after_cart_item_name', array( $this, 'cdsm_show_turnaround_on_cart' ), 20, 2);
		add_filter('woocommerce_checkout_cart_item_quantity', array( $this, 'cdsm_show_turnaround_on_checkout' ), 20, 3);
		//add_action('woocommerce_admin_order_data_after_order_details', array( $this, 'cdsm_output_saving_in_admin_order_screen' ), 20, 1);
		//add_action('woocommerce_email_after_order_table', array( $this, 'cdsm_output_saving_in_order_email'), 999, 1);

		add_filter( 'woocommerce_my_account_my_orders_actions', array ( $this, 'cdsm_add_order_again_to_my_orders_actions' ), 50, 2 );

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Cleaning_Delivery_Saving_Metrics_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Cleaning_Delivery_Saving_Metrics_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/cleaning-delivery-saving-metrics-public.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name.'-fa', plugin_dir_url( __FILE__ ) . 'css/flaticon.css', array(), '', ' ' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Cleaning_Delivery_Saving_Metrics_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Cleaning_Delivery_Saving_Metrics_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/cleaning-delivery-saving-metrics-public.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Calcualte the saving against order and save it against order as order meta
	 */
	public function cdsm_calculations_on_placing_order($order_id) {
		$order_saving = array(
			'cdsm_weight'		=> 0,
			'cdsm_time'			=> 0,
			'cdsm_water'		=> 0,
			'cdsm_electricity'	=> 0,
			'cdsm_detergent'	=> 0,
		);
		
		//getting order object
		$order = wc_get_order( $order_id );

		$items = $order->get_items();

		foreach ($items as $item_id => $item_data)
		{
			//getting product object
			$_product = $item_data->get_product();
			$qty = $item_data->get_quantity();

			// get current Product saving matrices
			$options = get_post_meta( $_product->get_ID(), 'cdsm_saving_matrices', true );

			foreach($options as $key => $value) {
				$order_saving[$key] = $order_saving[$key] + ( floatval($value) * $qty );
			}			
		}

		update_post_meta( $order_id, 'cdsm_saving_matrices', $order_saving );
		$this->cdsm_addup_saving($order_id, $order_saving);
		
		$this->cdsm_output_saving_text($order_id, 'per_order');

	}

	/**
	 * Out put the saving text on thankyou page and order details page
	 */
	public function cdsm_output_saving_text($order_id, $message_for) {

		if( $message_for == 'per_order' ) {
			$message = $this->options['cdsm_per_order_message'];
			$saved = get_post_meta( $order_id, 'cdsm_saving_matrices', true );
		} else if( $message_for == 'all_order' ) {
			$message = $this->options['cdsm_all_order_message'];
			$current_user = wp_get_current_user();
			$saved = get_user_meta( $current_user->ID, 'cdsm_total_savings', true );
		}
    
    	if(empty($saved)) {
        	return;
		}
		

		echo '
		<div class="'.$message_for.'">
		<div class="counterBlock cdsm-alerts success">
			<h2>'. $message .'</h2>
			<div class="row cdsm-counter-row">
				<!-- product-total -->
				<div class="product-total-box cdsm">
					<div class="product-total animation animated fadeInUp" data-animation="fadeInUp" data-animation-delay="0ms" style="animation-delay: 0ms;">
						<div class="icon">
							<i aria-hidden="true" class="glyph-icon flaticon-weight"></i>						
						</div>
						<div class="title">
							<span data-to="'.$saved['cdsm_weight'].'" data-speed="1000">'.$saved['cdsm_weight'].'</span>'.$this->options['cdsm_weight'].'
						</div>
						<div class="description">Cloth Washed</div>
					</div>
					<!-- /product-total -->
				</div>
				<!-- product-total -->
				<div class="product-total-box cdsm">
					<div class="product-total animation animated fadeInUp" data-animation="fadeInUp" data-animation-delay="0ms" style="animation-delay: 0ms;">
						<div class="icon">
							<i aria-hidden="true" class="glyph-icon flaticon-time"></i>						
						</div>
						<div class="title">
							<span data-to="'.$saved['cdsm_time'].'" data-speed="1000">'.$saved['cdsm_time'].'</span>'.$this->options['cdsm_time'].'
						</div>
						<div class="description">Time</div>
					</div>
					<!-- /product-total -->
				</div>
				<!-- product-total -->
				<div class="product-total-box cdsm">
					<div class="product-total animation animated fadeInUp" data-animation="fadeInUp" data-animation-delay="0ms" style="animation-delay: 0ms;">
						<div class="icon">
							<i aria-hidden="true" class="glyph-icon flaticon-water"></i>						
						</div>
						<div class="title">
							<span data-to="'.$saved['cdsm_water'].'" data-speed="1000">'.$saved['cdsm_water'].'</span>'.$this->options['cdsm_water'].'		
						</div>
						<div class="description">Water</div>
					</div>
					<!-- /product-total -->
				</div>
				<!-- product-total -->
				<div class="product-total-box cdsm">
					<div class="product-total animation animated fadeInUp" data-animation="fadeInUp" data-animation-delay="0ms" style="animation-delay: 0ms;">
						<div class="icon">
							<i aria-hidden="true" class="glyph-icon flaticon-electricity"></i>						
						</div>
						<div class="title">
							<span data-to="'.$saved['cdsm_electricity'].'" data-speed="1000">'.$saved['cdsm_electricity'].'</span>'.$this->options['cdsm_electricity'].'
						</div>
						<div class="description">Electricity</div>
					</div>
					<!-- /product-total -->
				</div>
				<!-- product-total -->
				<div class="product-total-box cdsm">
					<div class="product-total animation animated fadeInUp" data-animation="fadeInUp" data-animation-delay="0ms" style="animation-delay: 0ms;">
						<div class="icon">
							<i aria-hidden="true" class="glyph-icon flaticon-detergent"></i>						
						</div>
						<div class="title">
							<span data-to="'.$saved['cdsm_detergent'].'" data-speed="1000">'.$saved['cdsm_detergent'].'</span>'.$this->options['cdsm_detergent'].'
						</div>
						<div class="description">Detergent</div>
					</div>
					<!-- /product-total -->
				</div>
			</div>
		</div>
		</div>';

		// $save_with_units = array();

		// foreach( $saved as $key => $value ) {
		// 	$msg_key = str_replace('cdsm_', '', $key);
		// 	$save_with_units[$msg_key] = $value . " " . $this->options[$key];
		// }

		// return $save_with_units;

		// foreach( $save_with_units as $key => $value ) {
		// 	$message = str_replace('{{'.$key.'}}', '<span class="'.$key.'">'.$value.'</span>', $message);
		// }

		// $html = "";
		// $html .= "<div class='". $message_for ."'>";
		// $html .= "<p class='cdsm-alerts success'>". $message ."</p>";
		// $html .= "</div>";
    	// echo $html;

	}

	/**
	 * Add the current order saving to existing savings
	 */
	public function cdsm_addup_saving($order_id, $order_saving) {
		$added_up = get_post_meta( $order_id, 'cdsm_added_up', true );

		if( !$added_up ) {
			$current_user = wp_get_current_user();
			$existing_saving = get_user_meta( $current_user->ID, 'cdsm_total_savings', true );

			if($existing_saving) {
				foreach($order_saving as $key => $value) {
					$existing_saving[$key] = floatval( $existing_saving[$key] ) + floatval( $value );
				}
				update_user_meta( $current_user->ID, 'cdsm_total_savings', $existing_saving );
			} else {
				update_user_meta( $current_user->ID, 'cdsm_total_savings', $order_saving );
			}
			
			update_post_meta( $order_id, 'cdsm_added_up', true );
		}
		
	}

	/**
	 * Output the total order saving on userdashboard
	 */
	public function cdsm_output_total_save_on_dashboard() {
		$this->cdsm_output_saving_text('', 'all_order');
	}

	/**
	 * Output the total order saving on my-account order details
	 */
	public function cdsm_output_saving_on_my_account($order) {
		global $pagename; 
		if($pagename == 'my-account'){
			// do what you want here.
			$this->cdsm_output_saving_text($order->id, 'per_order');
		}
	}

	
	function cdsm_output_saving_in_admin_order_screen($order) {
		$this->cdsm_output_saving_text($order->id, 'per_order');
	}

	function cdsm_output_saving_in_order_email($order) {
    
        $message = $this->options['cdsm_per_order_message'];
		$saved = get_post_meta( $order_id, 'cdsm_saving_matrices', true );
		
		$save_with_units = array();

		foreach( $saved as $key => $value ) {
			$msg_key = str_replace('cdsm_', '', $key);
			$save_with_units[$msg_key] = $value . " " . $this->options[$key];
		}

		foreach( $save_with_units as $key => $value ) {
			$message = str_replace('{{'.$key.'}}', '<span class="'.$key.'">'.$value.'</span>', $message);
		}

		$html = "";
		$html .= "<div class='". $message_for ."'>";
		$html .= "<p>". $message ."</p>";
		$html .= "</div>";
		echo $html;
    
		//$this->cdsm_output_saving_text($order->id, 'per_order');
	}

	/**
	 * Add order again button in my orders actions.
	 *
	 * @param  array $actions
	 * @param  WC_Order $order
	 * @return array
	 */
	function cdsm_add_order_again_to_my_orders_actions( $actions, $order ) {
		if ( $order->has_status( 'completed' ) ) {
			$actions['order-again'] = array(
				'url'  => wp_nonce_url( add_query_arg( 'order_again', $order->id ) , 'woocommerce-order_again' ),
				'name' => __( 'Order Again', 'woocommerce' )
			);
		}

		return $actions;
	}

	/**
	 * Create an hidden field on checkout page that store max turn around time
	 */
	public function cdsm_get_max_turnaround_time() {
		// Loop over $cart items
		$max = 0;
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$product_id = $cart_item['product_id'];
			$cdsm_turnaround = get_post_meta( $product_id, 'cdsm_turnaround', true );
			if($cdsm_turnaround > $max) {
				$max = $cdsm_turnaround;
			}
		}
		echo "<input type='hidden' value='$max' name='max_turnaround' />";
	}

	/**
	 * Show product turn around time on cart page
	 */
	public function cdsm_show_turnaround_on_cart($cart_item, $cart_item_key) {
		$pro_id = $cart_item['product_id'];
		$turnaround = get_post_meta( $pro_id, 'cdsm_turnaround', true );

		if($turnaround > 1) {
			$turnaround_time = "<br><strong>Turnaround Time:</strong> ";
			$turnaround_time .= ($turnaround == 1) ? $turnaround." day" : $turnaround." days";
			echo $turnaround_time; 
		}

	}

	/**
	 * Show product turn around time on checkout page
	 */
	public function cdsm_show_turnaround_on_checkout($message, $cart_item, $cart_item_key) {
		$pro_id = $cart_item['product_id'];
		$turnaround = get_post_meta( $pro_id, 'cdsm_turnaround', true );

		if($turnaround > 1) {
			$turnaround_time = "<br><strong>Turnaround Time:</strong> ";
			$turnaround_time .= ($turnaround == 1) ? $turnaround." day" : $turnaround." days";
			return $message.$turnaround_time; 
		}
		return $message;
	}

}
