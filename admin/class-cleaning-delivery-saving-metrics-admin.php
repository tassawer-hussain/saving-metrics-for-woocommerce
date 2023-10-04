<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://tassawer.com/
 * @since      1.0.0
 *
 * @package    Cleaning_Delivery_Saving_Metrics
 * @subpackage Cleaning_Delivery_Saving_Metrics/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Cleaning_Delivery_Saving_Metrics
 * @subpackage Cleaning_Delivery_Saving_Metrics/admin
 * @author     Tassawer <hello@tassawer.com>
 */
class Cleaning_Delivery_Saving_Metrics_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->options = get_option( 'cdsm_settings' );

		add_action( 'admin_init', array($this, 'cdsm_settings') );
		add_action( 'admin_menu', array($this, 'cdsm_settings_menu') );

		add_filter( 'woocommerce_product_data_tabs', array($this, 'cdsm_product_data_tab') , 99 , 1 );
		add_action( 'woocommerce_product_data_panels', array($this, 'cdsm_product_data_fields' ) );
		add_action( 'woocommerce_process_product_meta', array($this, 'cdsm_process_product_meta_fields_save' ) );
	

	}

	/**
	 * Register the stylesheets for the admin area.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/cleaning-delivery-saving-metrics-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/cleaning-delivery-saving-metrics-admin.js', array( 'jquery' ), $this->version, false );

	}

	public function cdsm_settings_menu() {
		
		// Create top-level menu item
		add_menu_page( 
            'Cleaning Delivery Saving Metrics', //$page_title
            'CD Saving Metrics',//$menu_title
            'manage_options', // $capability
            'cd-saving-metrices', // $menu_slug
            array($this, 'cd_saving_matrics_callback' ), // $function
			'dashicons-editor-table',
			32
		); // $icon_url
	}

	public function cd_saving_matrics_callback() { ?>
		<div id="cd-saving" class="wrap">
			<h2>Cleaning Delivery Saving Matrics – Settings</h2>
			<?php settings_errors(); ?>
			<form method="post" action="options.php">
				<?php //settings_fields($option_group); ?>
				<?php // $option_group of register setting func
				settings_fields( 'cdsm_settings' ); ?>
				<?php //do_settings_sections($page); ?>
				<?php // $page of add_settings_section func
				do_settings_sections( 'cd-saving-metrices' ); ?> 
				<input type="submit" value="Submit" class="button-primary" />
			</form>
		</div>
	<?php }

	public function cdsm_settings() {
		// Register a setting group with a validation function
		// so that post data handling is done automatically for us
		register_setting( 
			'cdsm_settings', //$option_group, UNIQUE NAME
			'cdsm_settings', //$option_name, SAME AS IN DATABASE
			array( $this, 'cdsm_validate_options' )); //$args, CALL BACK VALIDATING FUNC 

			
		// Add a new settings section within the group
		add_settings_section( 
			'cdsm_requested_zipcode_section', //$id, unique name
			'Saving Matrics - Units', //$title
			array( $this, 'cdsm_requested_zipcode_section_callback' ),//$callback
			'cd-saving-metrices' );//$page

		// Add each field with its name and function to use for
		// our new settings, put them in our new section
		add_settings_field( 
			'cdsm_weight', //$id
			'Unit for weight i.e KG or Pound', //$title, a label that will be display next to the field
			array( $this, 'cdsm_display_text_field' ), //$callback
			'cd-saving-metrices', //$page
			'cdsm_requested_zipcode_section', //$section
			array( 'name' => 'cdsm_weight' ) ); //$args

		add_settings_field( 
			'cdsm_time', //$id
			'Unit for time i.e minutes or hours', //$title, a label that will be display next to the field
			array( $this, 'cdsm_display_text_field' ), //$callback
			'cd-saving-metrices', //$page
			'cdsm_requested_zipcode_section', //$section
			array( 'name' => 'cdsm_time' ) ); //$args

		add_settings_field( 
			'cdsm_water', //$id
			'Unit for water i.e liter or gallon', //$title, a label that will be display next to the field
			array( $this, 'cdsm_display_text_field' ), //$callback
			'cd-saving-metrices', //$page
			'cdsm_requested_zipcode_section', //$section
			array( 'name' => 'cdsm_water' ) ); //$args

		add_settings_field( 
			'cdsm_electricity', //$id
			'Unit for electricity i.e watt or megawatt', //$title, a label that will be display next to the field
			array( $this, 'cdsm_display_text_field' ), //$callback
			'cd-saving-metrices', //$page
			'cdsm_requested_zipcode_section', //$section
			array( 'name' => 'cdsm_electricity' ) ); //$args

		add_settings_field( 
			'cdsm_detergent', //$id
			'Unit for detergent i.e grams or kilograms', //$title, a label that will be display next to the field
			array( $this, 'cdsm_display_text_field' ), //$callback
			'cd-saving-metrices', //$page
			'cdsm_requested_zipcode_section', //$section
			array( 'name' => 'cdsm_detergent' ) ); //$args

		add_settings_field( 
			'cdsm_per_order_message', //$id
			'Message against per order', //$title, a label that will be display next to the field
			array( $this, 'cdsm_display_text_area' ), //$callback
			'cd-saving-metrices', //$page
			'cdsm_requested_zipcode_section', //$section
			array( 'name' => 'cdsm_per_order_message' ) ); //$args

		add_settings_field( 
			'cdsm_all_order_message', //$id
			'Message against all order', //$title, a label that will be display next to the field
			array( $this, 'cdsm_display_text_area' ), //$callback
			'cd-saving-metrices', //$page
			'cdsm_requested_zipcode_section', //$section
			array( 'name' => 'cdsm_all_order_message' ) ); //$args
		
	}

	public function cdsm_validate_options( $input ) {
		$input['version'] = $this->version;
		return $input;
	}

	// Declare a body for the cdsm_requested_zipcode_section_callback function
	public function cdsm_requested_zipcode_section_callback() { ?>
		<p>Configure the saving metrices units to use.</p>
	<?php }
	
	// Provide an implementation for the ch3sapi_display_text_field function
	public function cdsm_display_text_field( $data = array() ) {
		extract( $data ); ?>
		<input type="text" name="cdsm_settings[<?php echo $name; ?>]" value="<?php if(isset($this->options[$name])) { echo esc_html($this->options[$name]); } ?>"/><br />
	<?php }

	public function cdsm_display_text_area( $data = array() ) {
		extract ( $data ); ?>
		<textarea type="text" name="cdsm_settings[<?php echo $name; ?>]"rows="3" cols="100"><?php if(isset($this->options[$name])) { echo esc_html($this->options[$name]); } ?></textarea>
	<?php }

	public function cdsm_product_data_tab( $product_data_tabs ) {
		$product_data_tabs['cdsm-saving-matrics'] = array(
			'label' => __( 'Saving Matrics', 'my_text_domain' ),
			'target' => 'cdsm_saving_matrics',
		);
		return $product_data_tabs;
	}

	public function cdsm_product_data_fields() {
		global $woocommerce, $post;
		$options = get_post_meta( $post->ID, 'cdsm_saving_matrices', true );
		$options = (!empty($options)) ? $options : '';
		
		$cdsm_turnaround = get_post_meta( $post->ID, 'cdsm_turnaround', true );	?>

		<!-- id below must match target registered in above add_my_custom_product_data_tab function -->
		<div id="cdsm_saving_matrics" class="panel woocommerce_options_panel">
			<?php
			woocommerce_wp_text_input( array( 
				'id'            => 'cdsm_weight', 
				'wrapper_class' => 'cdsm_wrapper_class', 
				'label'         => __( 'Average weight', 'my_text_domain' ),
				'placeholder' 	=> '',
				'desc_tip'      => true,
				'description'   => __( 'Avaerage weight of these type of laundry items', 'my_text_domain' ),
				'value'			=> ( isset($options['cdsm_weight']) ) ? $options['cdsm_weight'] : '' ,
			) );

			woocommerce_wp_text_input( array( 
				'id'            => 'cdsm_time', 
				'wrapper_class' => 'cdsm_wrapper_class', 
				'label'         => __( 'Time to wash', 'my_text_domain' ),
				'placeholder' 	=> '',
				'desc_tip'      => true,
				'description'   => __( 'Average time to wash this cloth', 'my_text_domain' ),
				'value'			=> ( isset($options['cdsm_time']) ) ? $options['cdsm_time'] : '' ,
			) );

			woocommerce_wp_text_input( array( 
				'id'            => 'cdsm_water', 
				'wrapper_class' => 'cdsm_wrapper_class', 
				'label'         => __( 'Water use', 'my_text_domain' ),
				'placeholder' 	=> '',
				'desc_tip'      => true,
				'description'   => __( 'Water use to wash this laundry item', 'my_text_domain' ),
				'value'			=> ( isset($options['cdsm_water']) ) ? $options['cdsm_water'] : '' ,
			) );

			woocommerce_wp_text_input( array( 
				'id'            => 'cdsm_electricity', 
				'wrapper_class' => 'cdsm_wrapper_class', 
				'label'         => __( 'Electricity Use', 'my_text_domain' ),
				'placeholder' 	=> '',
				'desc_tip'      => true,
				'description'   => __( 'How much electricity consumed in washing this laundry item', 'my_text_domain' ),
				'value'			=> ( isset($options['cdsm_electricity']) ) ? $options['cdsm_electricity'] : '' ,
			) );

			woocommerce_wp_text_input( array( 
				'id'            => 'cdsm_detergent', 
				'wrapper_class' => 'cdsm_wrapper_class', 
				'label'         => __( 'Detergent use', 'my_text_domain' ),
				'placeholder' 	=> '',
				'desc_tip'      => true,
				'description'   => __( 'How much detergent use in washing this item', 'my_text_domain' ),
				'value'			=> ( isset($options['cdsm_detergent']) ) ? $options['cdsm_detergent'] : '' ,
			) );

			woocommerce_wp_text_input( array( 
				'id'            => 'cdsm_turnaround', 
				'wrapper_class' => 'cdsm_wrapper_class', 
				'label'         => __( 'Turn Around Time', 'my_text_domain' ),
				'placeholder' 	=> '',
				'desc_tip'      => true,
				'description'   => __( 'How much turn around time in washing this item. Default is 1 day', 'my_text_domain' ),
				'value'			=> ( isset($cdsm_turnaround) ) ? $cdsm_turnaround : 1 ,
			) );
			
			?>
		</div>
		<?php
	}

	public function cdsm_process_product_meta_fields_save( $post_id ){
		// This is the case to save custom field data of checkbox. You have to do it as per your custom fields
		$cdsm = array(
			'cdsm_weight' 		=> isset( $_POST['cdsm_weight'] ) ? $_POST['cdsm_weight']  : '',
			'cdsm_time' 		=> isset( $_POST['cdsm_time'] ) ? $_POST['cdsm_time']  : '',
			'cdsm_water' 		=> isset( $_POST['cdsm_water'] ) ? $_POST['cdsm_water']  : '',
			'cdsm_electricity' 	=> isset( $_POST['cdsm_electricity'] ) ? $_POST['cdsm_electricity']  : '',
			'cdsm_detergent' 	=> isset( $_POST['cdsm_detergent'] ) ? $_POST['cdsm_detergent']  : '',
		);
		
		update_post_meta( $post_id, 'cdsm_saving_matrices', $cdsm );
		
		$cdsm_turnaround = isset( $_POST['cdsm_turnaround'] ) ? $_POST['cdsm_turnaround'] : 1;
		update_post_meta( $post_id, 'cdsm_turnaround', $cdsm_turnaround );
	}
}
