<?php
/**
 * Plugin Name: UK Address Finder
 * Description: UK Address Finder from postcode with GetAddress.io API
 * Version:     1.6.2
 * Author:      Nick Papazetis
 * Author URI:  http://www.papazetis.com
 * License:     GPL2
 *
 * @package UK Address Finder

 * UK Address Finder is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.

 * UK Address Finder is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License along with UK Address Finder.
 * If not, see https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class UAF_Init {
	public static $basename = null;
	public function __construct() {
		self::$basename = plugin_basename( __FILE__ );
	}
}
new uaf_init();

if ( is_admin() === true ) {
	include dirname( __FILE__ ) . '/admin/class-uk-address-finder-settings.php';
	$uaf_settings = new UK_Address_Finder_Settings();
}

require dirname( __FILE__ ) . '/includes/class-uk-address-finder.php';

$uaf_init = new UK_Address_Finder();
