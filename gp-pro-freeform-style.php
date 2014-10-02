<?php
/*
Plugin Name: Genesis Design Palette Pro - Freeform Style
Plugin URI: https://genesisdesignpro.com/
Description: Adds a setting space for freeform CSS
Author: Reaktiv Studios
Version: 1.0.3
Requires at least: 3.7
Author URI: http://andrewnorcross.com
*/
/*  Copyright 2014 Andrew Norcross

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

if( ! defined( 'GPCSS_BASE' ) ) {
	define( 'GPCSS_BASE', plugin_basename(__FILE__) );
}

if( ! defined( 'GPCSS_DIR' ) ) {
	define( 'GPCSS_DIR', dirname( __FILE__ ) );
}

if( ! defined( 'GPCSS_VER' ) ) {
	define( 'GPCSS_VER', '1.0.3' );
}

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

		// general backend
		add_action(	'plugins_loaded',					array(	$this,	'textdomain'				)			);
		add_action(	'admin_enqueue_scripts',			array(	$this,	'admin_scripts'				)			);
		add_action(	'admin_notices',					array(	$this,	'gppro_active_check'		),	10		);
		add_action(	'admin_notices',					array(	$this,	'gppro_version_check'		),	10		);

		// GP Pro specific
		add_action( 'gppro_before_save',                array(  $this,  'save_custom_css'           )           );
		add_action( 'gppro_after_save',                 array(  $this,  'remove_custom_css'         )           );

		add_filter(	'gppro_admin_block_add',			array(	$this,	'freeform_block'			),	81		);
		add_filter(	'gppro_sections',					array(	$this,	'freeform_section'			),	10,	2	);
		add_filter(	'gppro_css_builder',				array(	$this,	'freeform_builder'			),	10,	3	);
	}

	/**
	 * If an instance exists, this returns it.  If not, it creates one and
	 * retuns it.
	 *
	 * @return GP_Pro_Freeform_CSS
	 */

	public static function getInstance() {

		if ( !self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * load textdomain
	 *
	 * @return
	 */

	public function textdomain() {

		load_plugin_textdomain( 'gp-pro-freeform-style', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	}

	/**
	 * check for GP Pro being active
	 *
	 * @return GP_Pro_Freeform_CSS
	 */

	public function gppro_active_check() {
		// get the current screen
		$screen = get_current_screen();
		// bail if not on the plugins page
		if ( $screen->parent_file !== 'plugins.php' ) {
			return;
		}
		// run the active check
		$coreactive	= class_exists( 'Genesis_Palette_Pro' ) ? Genesis_Palette_Pro::check_active() : false;
		// active. bail
		if ( $coreactive ) {
			return;
		}
		// not active. show message
		echo '<div id="message" class="error fade below-h2"><p><strong>'.__( sprintf( 'This plugin requires Genesis Design Palette Pro to function and cannot be activated.' ), 'gp-pro-freeform-style' ).'</strong></p></div>';
		// hide activation method
		unset( $_GET['activate'] );
		// deactivate the plugin
		deactivate_plugins( plugin_basename( __FILE__ ) );
		// and finish
		return;
	}

	/**
	 * Check for valid Design Palette Pro Version
	 *
	 * Requires version 1.3.0+
	 *
	 * @since 1.0.1
	 *
	 */
	public function gppro_version_check() {

		$dpp_version = defined( 'GPP_VER' ) ? GPP_VER : 0;

		if ( version_compare( $dpp_version, '1.3.0', '<' ) ) {
			printf(
				'<div class="updated"><p>' . esc_html__( 'Please upgrade %2$sDesign Palette Pro to version 1.3.0 or greater%3$s to continue using the %1$s extension.', 'gppro' ) . '</p></div>',
				'<strong>' . 'Genesis Design Palette Pro - eNews Widget' . '</strong>',
				'<a href="' . esc_url( admin_url( 'plugins.php?plugin_status=upgrade' ) ) . '">',
				'</a>'
			);
		}

	}

	/**
	 * call admin CSS and JS files
	 *
	 * @return
	 */
	public function admin_scripts() {

		$screen	= get_current_screen();

		if ( $screen->base != 'genesis_page_genesis-palette-pro' ) {
			return;
		}

		wp_enqueue_style( 'gppro-freeform',		plugins_url( 'lib/css/gppro.freeform.css',	__FILE__ ),	array(), GPCSS_VER, 'all' );

		wp_enqueue_script( 'textarea-size', 	plugins_url( 'lib/js/autosize.min.js',		__FILE__ ),	array( 'jquery' ), '1.18.2', true );
		wp_enqueue_script( 'gppro-freeform',	plugins_url( 'lib/js/gppro.freeform.js',	__FILE__ ),	array( 'jquery' ), GPCSS_VER, true );


	}

	/**
	 * add block to side
	 *
	 * @return
	 */
	public function freeform_block( $blocks ) {

		if ( is_multisite() && ! current_user_can( 'unfiltered_html' ) ) {
			return $blocks;
		}

		$blocks['freeform-css'] = array(
			'tab'		=> __( 'Freeform CSS', 'gp-pro-freeform-style' ),
			'title'		=> __( 'Freeform CSS', 'gp-pro-freeform-style' ),
			'intro'		=> __( 'Enter any extra or unique CSS in the field below.', 'gp-pro-freeform-style' ),
			'slug'		=> 'freeform_css',
		);

		return $blocks;

	}

	/**
	 * add section to side
	 *
	 * @return
	 */
	public function freeform_section( $sections, $class ) {

		$sections['freeform_css']	= array(

			'freeform-css-global-setup'	=> array(
				'title'		=> __( 'Global CSS', 'gp-pro-freeform-style' ),
				'data'		=> array(
					'freeform-css-global'	=> array(
						'input'		=> 'custom',
						'desc'		=> __( 'This CSS will apply site-wide.', 'gp-pro-freeform-style' ),
						'viewport'	=> 'global',
						'callback'	=> array( $this, 'freeform_css_input' )
					),
				),
			),

			'freeform-css-mobile-setup'	=> array(
				'title'		=> __( 'Mobile CSS', 'gp-pro-freeform-style' ),
				'data'		=> array(
					'freeform-css-mobile'	=> array(
						'input'		=> 'custom',
						'desc'		=> __( 'This CSS will apply to 480px and below', 'gp-pro-freeform-style' ),
						'viewport'	=> 'mobile',
						'callback'	=> array( $this, 'freeform_css_input' )
					),
				),
			),

			'freeform-css-tablet-setup'	=> array(
				'title'		=> __( 'Tablet CSS', 'gp-pro-freeform-style' ),
				'data'		=> array(
					'freeform-css-tablet'	=> array(
						'input'		=> 'custom',
						'desc'		=> __( 'This CSS will apply to 768px and below', 'gp-pro-freeform-style' ),
						'viewport'	=> 'tablet',
						'callback'	=> array( $this, 'freeform_css_input' )
					),
				),
			),

			'freeform-css-desktop-setup'	=> array(
				'title'		=> __( 'Desktop CSS', 'gp-pro-freeform-style' ),
				'data'		=> array(
					'freeform-css-desktop'	=> array(
						'input'		=> 'custom',
						'desc'		=> __( 'This CSS will apply to 1024px and above', 'gp-pro-freeform-style' ),
						'viewport'	=> 'desktop',
						'callback'	=> array( $this, 'freeform_css_input' )
					),
				),
			),

		); // end section

		return $sections;

	}

	/**
	 * create the input fields for the
	 * custom CSS entry
	 *
	 * @return
	 */
	static function freeform_css_input( $field, $item ) {
		// get the standard field info
		$id			= GP_Pro_Helper::get_field_id( $field );
		$name		= GP_Pro_Helper::get_field_name( $field );
		// fetch the viewport field
		$viewport	= isset( $item['viewport'] ) ? esc_attr( $item['viewport'] ) : 'global';
		// get our custom data
		$value		= self::get_custom_css( $viewport );

		// start the field
		$input	= '';

		$input	.= '<div class="gppro-input gppro-freeform-input">';

			$input	.= '<div class="gppro-input-wrap gppro-freeform-wrap">';

			if ( isset( $item['desc'] ) ) {
				$input	.= '<p class="description">' . esc_attr( $item['desc'] ) . '</p>';
			}

			$input	.= '<textarea name="' . $name . '" id="' . $id . '" class="widefat code css-entry css-global">' . esc_textarea( $value ) . '</textarea>';

			$input	.= '<span data-viewport="' . $viewport . '" class="button button-secondary button-small gppro-button-right gppro-freeform-preview">'. __( 'Preview CSS', 'gp-pro-freeform-style' ).'</span>';
			$input	.= '</div>';

		$input	.= '</div>';

		// send it back
		return $input;

	}

	/**
	 * save the custom CSS if it exists
	 *
	 * @param  array  $choices [description]
	 * @return [type]          [description]
	 */
	public function save_custom_css( $choices = array() ) {
		// set an empty
		$custom	= array();
		// check for global
		if ( ! empty( $choices['freeform-css-global'] ) ) {
			$custom['global']	= wp_kses_post( stripslashes( $choices['freeform-css-global'] ) );
		}
		// check for desktop
		if ( ! empty( $choices['freeform-css-desktop'] ) ) {
			$custom['desktop']	= wp_kses_post( stripslashes( $choices['freeform-css-desktop'] ) );
		}
		// check for tablet
		if ( ! empty( $choices['freeform-css-tablet'] ) ) {
			$custom['tablet']	= wp_kses_post( stripslashes( $choices['freeform-css-tablet'] ) );
		}
		// check for mobile
		if ( ! empty( $choices['freeform-css-mobile'] ) ) {
			$custom['mobile']	= wp_kses_post( stripslashes( $choices['freeform-css-mobile'] ) );
		}
		// save our custom CSS
		if ( ! empty( $custom ) ) {
			update_option( 'gppro-custom-css', $custom );
		} else {
			delete_option( 'gppro-custom-css' );
		}
	}

	/**
	 * remove the custom CSS values from the global array
	 *
	 * @param  array  $updated [description]
	 * @return [type]           [description]
	 */
	public function remove_custom_css( $updated = array() ) {
		// check for global
		if ( ! empty( $updated['freeform-css-global'] ) ) {
			unset( $updated['freeform-css-global'] );
		}
		// check for desktop
		if ( ! empty( $updated['freeform-css-desktop'] ) ) {
			unset( $updated['freeform-css-desktop'] );
		}
		// check for tablet
		if ( ! empty( $updated['freeform-css-tablet'] ) ) {
			unset( $updated['freeform-css-tablet'] );
		}
		// check for mobile
		if ( ! empty( $updated['freeform-css-mobile'] ) ) {
			unset( $updated['freeform-css-mobile'] );
		}
		// save our custom CSS
		if ( ! empty( $updated ) ) {
			update_option( 'gppro-settings', $updated );
		}
	}

	/**
	 * retrieve the saved value if it exists
	 *
	 * @param  string $viewport [description]
	 * @return [type]           [description]
	 */
	public static function get_custom_css( $viewport = '' ) {
		// first check for custom CSS
		$custom	= get_option( 'gppro-custom-css' );
		// if no custom for that viewport, just return
		if ( empty( $custom ) || empty( $custom[$viewport] ) ) {
			return;
		}
		// send it back
		return $custom[$viewport];
	}

	/**
	 * add freeform CSS to builder file
	 *
	 * @param  [type] $setup [description]
	 * @param  [type] $data  [description]
	 * @param  [type] $class [description]
	 * @return [type]        [description]
	 */
	public function freeform_builder( $setup, $data, $class ) {
		// first check for custom CSS
		$custom	= get_option( 'gppro-custom-css' );
		// if no custom, just return
		if ( empty( $custom ) ) {
			return $setup;
		}
		// now add our custom CSS to the mix
		$setup	.= '/* custom freeform CSS */'."\n";
		// our global CSS
		if ( ! empty( $custom['global'] ) ) {
			$setup	.= $custom['global']."\n\n";
		}
		// our mobile CSS
		if ( ! empty( $custom['mobile'] )  ) {
			$setup	.= '@media only screen and (max-width: 480px) {'."\n";
			$setup	.= $custom['mobile']."\n";
			$setup	.= '}'."\n\n";
		}
		// our tablet CSS
		if ( ! empty( $custom['tablet'] )  ) {
			$setup	.= '@media only screen and (max-width: 768px) {'."\n";
			$setup	.= $custom['tablet']."\n";
			$setup	.= '}'."\n\n";
		}
		// our desktop CSS
		if ( ! empty( $custom['desktop'] )  ) {
			$setup	.= '@media only screen and (min-width: 1024px) {'."\n";
			$setup	.= $custom['desktop']."\n";
			$setup	.= '}'."\n\n";
		}
		// return the new data to be written
		return $setup;
	}

/// end class
}

// Instantiate our class
$GP_Pro_Freeform_CSS = GP_Pro_Freeform_CSS::getInstance();