<?php
/*
Plugin Name: Freeform CSS - Genesis Design Pallete Pro
Plugin URI: http://andrewnorcross.com/plugins/
Description: Adds a setting space for freeform CSS
Author: Andrew Norcross
Version: 0.0.1.0
Requires at least: 3.5
Author URI: http://andrewnorcross.com
*/
/*  Copyright 2013 Andrew Norcross

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; version 2 of the License (GPL v2) only.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class GP_Pro_Freeform_CSS
{

	/**
	 * Static property to hold our singleton instance
	 * @var GP_Pro_Freeform_CSS
	 */
	static $instance = false;

	/**
	 * This is our constructor
	 *
	 * @return GP_Pro_Freeform_CSS
	 */
	private function __construct() {

		add_action			( 'admin_notices',				array(	$this,	'gppro_active_check'		),	10		);

	}

	/**
	 * If an instance exists, this returns it.  If not, it creates one and
	 * retuns it.
	 *
	 * @return GP_Pro_Freeform_CSS
	 */

	public static function getInstance() {
		if ( !self::$instance )
			self::$instance = new self;
		return self::$instance;
	}

	/**
	 * check for GP Pro being active
	 *
	 * @return GP_Pro_Freeform_CSS
	 */

	public function gppro_active_check() {

		$screen = get_current_screen();

		if ( $screen->parent_file !== 'plugins.php' )
			return;

		if ( is_plugin_active( 'genesis-palette-pro/genesis-palette-pro.php' ) )
			return;

		echo '<div id="message" class="error fade below-h2"><p><strong>'.__( 'This plugin requires Genesis Design Palette Pro to function.', 'gpcss' ).'</strong></p></div>';

		// hacky CSS
		echo '<style>div#message.updated{ display: none; }</style>';

		deactivate_plugins( plugin_basename( __FILE__ ) );

		return false;

	}




/// end class
}

// Instantiate our class
$GP_Pro_Freeform_CSS = GP_Pro_Freeform_CSS::getInstance();

