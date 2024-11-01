<?php

if ( ! class_exists( 'UK_Adrress_Finder_Settings' ) ) {
	class UK_Address_Finder_Settings {
		/**
		* Holds the values to be used in the fields callbacks
		*/
		private $options;

		/**
		* Start up
		*/
		public function __construct()
		{
			add_action( 'admin_menu', array( $this, 'uaf_add_admin_menu' ) );
			add_action( 'admin_init', array( $this, 'uaf_settings_init' ) );
			add_filter( 'plugin_action_links_' . UAF_Init::$basename, array( $this, 'uaf_setting_link' ) );
		}

		public function uaf_setting_link( $links ) {
			$links[] = '<a href="' . esc_url( get_admin_url( null, 'options-general.php?page=uk-address-finder' ) ) . '">Settings</a>';
			return $links;
		}

		/**
		* Add options page
		*/
		public function uaf_add_admin_menu() {
			add_options_page(
				'UK Address Finder from GetAddress.io',
				'UK Address Finder',
				'manage_options',
				'uk-address-finder',
				array( $this, 'uaf_options_page' )
			);
		}

		/**
		* Options page callback
		*/
		public function uaf_options_page()
		{
			// Set class property
			$this->options = get_option( 'uaf_settings' );
			?>
			<div class="wrap">
				<h1>UK Address Finder</h1>
				<form method="post" action="options.php">
					<?php
					// This prints out all hidden setting fields
					settings_fields( 'uaf_settings_page' );
					do_settings_sections( 'uaf_settings_page' );
					submit_button();
					?>
				</form>
			</div>
			<?php
		}

		/**
		* Register and add settings
		*/
		public function uaf_settings_init()
		{
			$args = array(
				'sanitize_callback' => array( $this, 'uaf_validate' ),
			);
			register_setting(
				'uaf_settings_page', // Option group
				'uaf_settings', // Option name
				$args // Sanitize
			);

			add_settings_section(
				'uaf_uaf_settings_page_section', // ID
				'', // Title
				array( $this, 'uaf_settings_section_callback' ), // Callback
				'uaf_settings_page' // Page
			);

			add_settings_field(
				'uaf_admin_api_key', // ID
				'GetAddress.io Admin API Key', // Title
				array( $this, 'uaf_admin_api_key_render' ), // Callback
				'uaf_settings_page', // Page
				'uaf_uaf_settings_page_section' // Section
			);

			add_settings_field(
				'uaf_api_key', // ID
				'GetAddress.io API Key', // Title
				array( $this, 'uaf_api_key_render' ), // Callback
				'uaf_settings_page', // Page
				'uaf_uaf_settings_page_section' // Section
			);
		}

		/**
		* Sanitize each setting field as needed
		*
		* @param array $input Contains all settings fields as array keys
		*/
		public function uaf_validate( $data ) {
			$new_data                      = array();
			$new_data['uaf_admin_api_key'] = null;
			$new_data['uaf_api_key']       = null;


			$uaf_admin_api_key  = $data['uaf_admin_api_key'];
			$response_admin_key = wp_remote_get( esc_url_raw( 'https://api.getAddress.io/v2/usage?api-key=' . $uaf_admin_api_key ) );
			$response_code      = wp_remote_retrieve_response_code( $response_admin_key );

			$response_key = wp_remote_get( esc_url_raw( 'https://api.getaddress.io/security/api-key?api-key=' . $uaf_admin_api_key ) );
			$api_key      = json_decode( wp_remote_retrieve_body( $response_key ), true );
			$api_key      = isset( $api_key['api-key'] ) ? $api_key['api-key'] : '';

			$message  = null;
			$type     = null;

			if ( null !== $data ) {
				if ( 401 !== $response_code ) {
					$type     = 'updated';
					$message  = __( 'API Key successfully added', 'uaf' );
					$new_data = $data;
					$new_data['uaf_api_key'] = $api_key;
				} else {
					$type    = 'error';
					$message = __( 'Your Administrator API Key is not valid or is expired', 'uaf' );
				}
				add_settings_error(
					'uaf_validate_msg',
					esc_attr( 'settings_updated' ),
					$message,
					$type
				);
			}
			return $new_data;
		}

		/**
		* Print the Section text
		*/
		public function uaf_settings_section_callback() {
			print 'You can get your API Key by selecting a plan from <a href="https://getaddress.io/#pricing-table" target="_blank">www.getaddress.io</a> and then use it in every page or post via shortcode <strong>[uk-address-finder]</strong>';
		}

		/**
		* Get the settings option array and print one of its values
		*/
		public function uaf_admin_api_key_render() {
			$options           = get_option( 'uaf_settings' );
			$uaf_admin_api_key = ( is_array( $options ) ) ? $options['uaf_admin_api_key'] : '';

			$response      = wp_remote_get( esc_url_raw( 'https://api.getAddress.io/v2/usage?api-key=' . $uaf_admin_api_key ) );
			$response_code = wp_remote_retrieve_response_code( $response );
			?>
			<input type='text' name='uaf_settings[uaf_admin_api_key]' value='<?php echo esc_attr( $uaf_admin_api_key ); ?>' size='50'><br>
			<?php

		}

		/**
		* Get the settings option array and print one of its values
		*/
		public function uaf_api_key_render() {
			$options     = get_option( 'uaf_settings' );
			$uaf_api_key = ( is_array( $options ) ) ? $options['uaf_api_key'] : '';

			$uaf_admin_api_key = ( is_array( $options ) ) ? $options['uaf_admin_api_key'] : '';

			$response      = wp_remote_get( esc_url_raw( 'https://api.getAddress.io/v2/usage?api-key=' . $uaf_admin_api_key ) );
			$response_code = wp_remote_retrieve_response_code( $response );

			$api_message          = json_decode( wp_remote_retrieve_body( $response ) );
			$dailyrequestqount    = isset( $api_message->count ) ? $api_message->count : '';
			$dailyrequestlimit1   = isset( $api_message->limit1 ) ? $api_message->limit1 : '';
			$dailyrequestlimit2   = isset( $api_message->limit2 ) ? $api_message->limit2 : '';
			$requests_after_limit = $dailyrequestlimit2 - $dailyrequestlimit1;
			?>

			<input type='text' name='uaf_settings[uaf_api_key]' value='<?php echo esc_attr( $uaf_api_key ); ?>' size='50' disabled><br><hr width="340px" align="left">
			<?php

			echo '<strong>Daily Requests: </strong>' . esc_attr( $dailyrequestqount ) . '<br>';
			echo '<strong>Daily Requests Limit: </strong>' . esc_attr( $dailyrequestlimit1 ) . '<br>';
			echo '<strong>After Daily Requests Limit: </strong>' . esc_attr( $requests_after_limit ) . ' more with a 5 second delay';

		}
	}
}
