<?php
if ( ! class_exists( 'UK_Adrress_Finder' ) ) {
	class UK_Address_Finder {

		public function __construct() {
			add_action('init', array($this, 'uk_address_shortcode_init')) ;
			$this->inc_ajax();
		}

		public function uk_address_shortcode_init() {
			function uk_address_shortcode($atts = [], $content = null) {
			  $content = '<input type="text" name="uaf_postcode" class="uaf_postcode" autocomplete="off" placeholder="Please type your postcode">';
				$content .= '<form id="result"></form>';
				$content .= '<div id="gmap" style="display:none; width: 100%; height: 300px;"></div>';

				return $content;
			  }
				add_shortcode( 'uk-address-finder', 'uk_address_shortcode' );
		}

		private function inc_ajax() {
			require dirname( __FILE__ ) . '/class-ajax-init.php';
		}

}
}
