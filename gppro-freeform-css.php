<?php
/*
Plugin Name: Genesis Design Palette Pro - Freeform CSS
Plugin URI: http://genesisdesignpro.com/
Description: Adds a setting space for freeform CSS
Author: Reaktiv Studios
Version: 1.0.0.0
Requires at least: 3.7
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

if( !defined( 'GPCSS_BASE' ) )
	define( 'GPCSS_BASE', plugin_basename(__FILE__) );

if( !defined( 'GPCSS_DIR' ) )
	define( 'GPCSS_DIR', dirname( __FILE__ ) );

if( !defined( 'GPCSS_VER' ) )
	define( 'GPCSS_VER', '1.0.0.0' );

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
		add_action			(	'plugins_loaded',					array(	$this,	'textdomain'				)			);
		add_action			(	'admin_enqueue_scripts',			array(	$this,	'admin_scripts'				)			);
		add_action			(	'admin_notices',					array(	$this,	'gppro_active_check'		),	10		);

		// GP Pro specific
		add_filter			(	'gppro_admin_block_add',			array(	$this,	'freeform_block'			),	81		);
		add_filter			(	'gppro_sections',					array(	$this,	'freeform_section'			),	10,	2	);
		add_filter			(	'gppro_css_builder',				array(	$this,	'freeform_builder'			),	10,	3	);
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
	 * load textdomain
	 *
	 * @return
	 */

	public function textdomain() {

		load_plugin_textdomain( 'gpcss', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

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

		if ( !is_plugin_active( 'genesis-palette-pro/genesis-palette-pro.php' ) ) :

			echo '<div id="message" class="error fade below-h2"><p><strong>'.__( 'This plugin requires Genesis Design Palette Pro to function.', 'gpcss' ).'</strong></p></div>';

			// hide activation method
			unset( $_GET['activate'] );

			deactivate_plugins( plugin_basename( __FILE__ ) );

		endif;

	}

	/**
	 * call admin CSS and JS files
	 *
	 * @return
	 */

	public function admin_scripts() {

		$screen	= get_current_screen();

		if ( $screen->base != 'genesis_page_genesis-palette-pro' )
			return;

		wp_enqueue_style( 'gppro-freeform',		plugins_url( 'lib/css/gppro.freeform.css',	__FILE__ ),	array(), GPCSS_VER, 'all' );

		wp_enqueue_script( 'textarea-size', 	plugins_url( 'lib/js/autosize.min.js',		__FILE__ ),	array( 'jquery' ), '1.18.1', true );
		wp_enqueue_script( 'gppro-freeform',	plugins_url( 'lib/js/gppro.freeform.js',	__FILE__ ),	array( 'jquery' ), GPCSS_VER, true );


	}

	/**
	 * add block to side
	 *
	 * @return
	 */

	public function freeform_block( $blocks ) {

		if ( is_multisite() && ! current_user_can( 'unfiltered_html' ) )
			return $blocks;

		$blocks['freeform-css'] = array(
			'tab'		=> __( 'Freeform CSS', 'gpcss' ),
			'title'		=> __( 'Freeform CSS', 'gpcss' ),
			'intro'		=> __( 'Enter any extra or unique CSS in the field below.', 'gpcss' ),
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
				'title'		=> __( 'Global CSS', 'gpcss' ),
				'data'		=> array(
					'freeform-css-global'	=> array(
						'input'		=> 'custom',
						'desc'		=> __( 'This CSS will apply site-wide.', 'gpcss' ),
						'viewport'	=> 'global',
						'callback'	=> array( $this, 'freeform_css_input' )
					),
				),
			),

			'freeform-css-mobile-setup'	=> array(
				'title'		=> __( 'Mobile CSS', 'gpcss' ),
				'data'		=> array(
					'freeform-css-mobile'	=> array(
						'input'		=> 'custom',
						'desc'		=> __( 'This CSS will apply to 480px and below', 'gpcss' ),
						'viewport'	=> 'mobile',
						'callback'	=> array( $this, 'freeform_css_input' )
					),
				),
			),

			'freeform-css-tablet-setup'	=> array(
				'title'		=> __( 'Tablet CSS', 'gpcss' ),
				'data'		=> array(
					'freeform-css-tablet'	=> array(
						'input'		=> 'custom',
						'desc'		=> __( 'This CSS will apply to 768px and below', 'gpcss' ),
						'viewport'	=> 'tablet',
						'callback'	=> array( $this, 'freeform_css_input' )
					),
				),
			),

			'freeform-css-desktop-setup'	=> array(
				'title'		=> __( 'Desktop CSS', 'gpcss' ),
				'data'		=> array(
					'freeform-css-desktop'	=> array(
						'input'		=> 'custom',
						'desc'		=> __( 'This CSS will apply to 1024px and above', 'gpcss' ),
						'viewport'	=> 'desktop',
						'callback'	=> array( $this, 'freeform_css_input' )
					),
				),
			),

		); // end section


		return $sections;

	}

	/**
	 * create CSS inputs
	 *
	 * @return
	 */

	static function freeform_css_input( $field, $item ) {

		$id			= GP_Pro_Helper::get_field_id( $field );
		$name		= GP_Pro_Helper::get_field_name( $field );
		$value		= GP_Pro_Helper::get_field_value( $field );

		$viewport	= isset( $item['viewport'] ) ? esc_attr( $item['viewport'] ) : 'global';

		$input	= '';

		$input	.= '<div class="gppro-input gppro-freeform-input">';

			$input	.= '<div class="gppro-input-wrap gppro-freeform-wrap">';

			if ( isset( $item['desc'] ) )
				$input	.= '<p class="description">'.esc_attr( $item['desc'] ).'</p>';

			$input	.= '<textarea name="'.$name.'" id="'.$id.'" class="widefat code css-entry css-global">'.esc_attr( $value ).'</textarea>';

			$input	.= '<span data-viewport="'.$viewport.'" class="button button-secondary button-small gppro-button-right gppro-freeform-preview">'. __( 'Preview CSS', 'gpcss' ).'</span>';
			$input	.= '</div>';

		$input	.= '</div>';


		return $input;

	}


	/**
	 * add freeform CSS to builder file
	 *
	 * @return
	 */

	public function freeform_builder( $custom, $data, $class ) {

		$custom	= '/* custom freeform CSS */'."\n";

		if ( isset( $data['freeform-css-global'] ) && !empty( $data['freeform-css-global'] )  )
			$custom	.= $data['freeform-css-global'];

		if ( isset( $data['freeform-css-mobile'] ) && !empty( $data['freeform-css-mobile'] )  )
			$custom	.= '@media only screen and (max-width: 480px) {'.$data['freeform-css-mobile'].'}'."\n";

		if ( isset( $data['freeform-css-tablet'] ) && !empty( $data['freeform-css-tablet'] )  )
			$custom	.= '@media only screen and (max-width: 768px) {'.$data['freeform-css-tablet'].'}'."\n";

		if ( isset( $data['freeform-css-desktop'] ) && !empty( $data['freeform-css-desktop'] )  )
			$custom	.= '@media only screen and (min-width: 1024px) {'.$data['freeform-css-desktop'].'}'."\n";

		return $custom;

	}

/// end class
}

// Instantiate our class
$GP_Pro_Freeform_CSS = GP_Pro_Freeform_CSS::getInstance();

