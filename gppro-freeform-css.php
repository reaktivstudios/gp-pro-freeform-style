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

if( !defined( 'GPCSS_BASE' ) )
	define( 'GPCSS_BASE', plugin_basename(__FILE__) );

if( !defined( 'GPCSS_DIR' ) )
	define( 'GPCSS_DIR', dirname( __FILE__ ) );

if( !defined( 'GPCSS_VER' ) )
	define( 'GPCSS_VER', '0.0.1.0' );

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
		add_filter			(	'gppro_admin_blocks',				array(	$this,	'freeform_block'			),	20		);
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
		wp_enqueue_script( 'textarea-size', 	plugins_url( 'lib/js/autosize.min.js',		__FILE__),	array( 'jquery' ), '1.18.1', true );
		wp_enqueue_script( 'gppro-freeform',	plugins_url( 'lib/js/gppro.freeform.js',	__FILE__),	array( 'jquery' ), GPCSS_VER, true );


	}

	/**
	 * add block to side
	 *
	 * @return
	 */

	public function freeform_block( $blocks ) {

		$blocks['freeform-css'] = array(
			'tab'		=> __( 'Freeform CSS', 'gpcss' ),
			'title'		=> __( 'Freeform CSS', 'gpcss' ),
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

			'freeform-css-setup'	=> array(
				'headline'	=> __( 'Freeform CSS', 'gpcss' ),
				'intro'		=> __( 'Enter any extra or unique CSS in the field below. Please note this will not be displayed in the preview pane during entry.', 'gpcss' ),
				'title'		=> '',
				'data'		=> array(
					'freeform-css'	=> array(
						'label'		=> __( 'Freeform', 'gpcss' ),
						'input'		=> 'custom',
						'field'		=> 'freeform-css',
						'target'	=> '',
						'type'		=> '',
						'callback'	=> array( $this, 'freeform_input' )
					),

				),
			),
		); // end section


		return $sections;

	}

	/**
	 * create CSS input
	 *
	 * @return
	 */

	static function freeform_input( $item ) {

			$id			= GP_Pro_Helper::get_field_id( $item['field'] );
			$name		= GP_Pro_Helper::get_field_name( $item['field'] );
			$value		= esc_attr( GP_Pro_Helper::get_field_value( $item['field'] ) );

			$input	= '';

			$input	.= '<div class="gppro-input gppro-freeform-input">';

				$input	.= '<div class="gppro-input-wrap">';

				$input	.= '<textarea name="'.$name.'" id="'.$id.'" class="widefat code css-entry">'.esc_attr( $value ).'</textarea>';

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

		if ( !isset( $data['freeform-css'] ) || isset( $data['freeform-css'] ) && empty( $data['freeform-css'] )  )
			return;

		$custom	= '/* custom freeform CSS */'."\n";

		$custom	.= $data['freeform-css'];

		return $custom;

	}

/// end class
}

// Instantiate our class
$GP_Pro_Freeform_CSS = GP_Pro_Freeform_CSS::getInstance();

