<?php /*
Contributors: inventivogermany
Plugin Name:  Scroll to top | inventivo
Plugin URI:   https://www.inventivo.de/webseiten/wordpress-plugins
Description:  Display scroll to top button at page bottom
Version:      1.0.6
Author:       Nils Harder
Author URI:   https://www.inventivo.de
Tags: scroll to top, back to top, scroll, button
Requires at least: 5.0
Requires PHP: 7.4
Tested up to: 7.1
Stable tag: 1.0.6
Text Domain: inventivo-scroll-to-top
Domain Path: /languages
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if (!defined('ABSPATH')) {
	exit;
}

// Version of the plugin
define('INVENTIVO_SCROLL_TO_TOP_CURRENT_VERSION', '1.0.6');

add_action('wp_enqueue_scripts', array('InventivoScrollToTopSettingsPage','inventivo_scroll_to_top_js' ));
add_action('wp_enqueue_scripts', array('InventivoScrollToTopSettingsPage','inventivo_scroll_to_top_publiccss' ));

class InventivoScrollToTopSettingsPage
{
	/**
	 * Holds the values to be used in the fields callbacks
	 */
	public $options;

	/**
	 * Start up
	 */
	public function __construct()
	{
		add_action('init', array($this, 'inventivo_scroll_to_top_load_textdomain'));
		add_action('admin_menu', array( $this, 'add_plugin_page' ));
		add_action('admin_init', array( $this, 'page_init' ));
		add_action('admin_notices', array($this, 'admin_notice_get_pro'));
		add_action('admin_init', array($this, 'notice_dismissed'));
		register_deactivation_hook( __FILE__, array($this, 'plugin_deactivate') );
		add_action('admin_enqueue_scripts', array($this, 'inv_admin_style'));
	}


	public function inventivo_scroll_to_top_load_textdomain()
	{
		load_plugin_textdomain('inventivo-scroll-to-top', false, dirname(plugin_basename(__FILE__)) . '/languages');
	}

	/**
	 * Add options page
	 */
	public function add_plugin_page()
	{
		// This page will be under "Settings"
		add_options_page(
			'Settings Admin',
			esc_html__('Scroll top top | inventivo', 'inventivo-scroll-to-top'),
			'manage_options',
			'inventivo_scroll_to_top_setting_admin',
			array( $this, 'create_admin_page' )
		);
	}

	/**
	 * Options page callback
	 */
	public function create_admin_page()
	{
		// Set class property
		$this->options = wp_parse_args( self::get_options_array('inventivo_scroll_to_top_option_name'), self::get_default_options() );
		?>
        <div class="wrap">
            <h1><?php esc_html_e('Scroll to top | inventivo', 'inventivo-scroll-to-top'); ?></h1>
            <form method="post" action="options.php">
				<?php
				// This prints out all hidden setting fields
				settings_fields('inventivo_scroll_to_top_option_group');
				do_settings_sections('inventivo_scroll_to_top_setting_admin');
				submit_button();
				?>
            </form>
        </div>
		<?php
	}

	// Add admin styles
	/**
	 * Reads an option and always returns an array.
	 *
	 * A cast to (array) would turn false into the stray key 0.
	 */
	public static function get_options_array( $name )
	{
		$stored = get_option( $name, array() );

		return is_array( $stored ) ? $stored : array();
	}

	/**
	 * Standardwerte der Einstellungen.
	 *
	 * Ohne sie liefert get_option() vor dem ersten Speichern false, und jeder
	 * Feldzugriff erzeugt unter PHP 8 eine Warnung.
	 */
	public static function get_default_options()
	{
		return array(
			'background_color' => '#a6ce38',
			'icon_color'       => '#FFFFFF',
			'alignment'        => 'left',
		);
	}

	// Add public styles
	public static function inventivo_scroll_to_top_publiccss()
	{
		wp_register_style('inventivo-scroll-to-top-publiccss', plugins_url('/public/css/scroll-to-top.css', __FILE__));
		wp_enqueue_style('inventivo-scroll-to-top-publiccss');
		wp_register_style('inventivo-scroll-to-top-genericons', plugins_url('/public/css/genericons.css', __FILE__));
		wp_enqueue_style('inventivo-scroll-to-top-genericons');
	}


	public static function inventivo_scroll_to_top_js()
	{
		$options = wp_parse_args( self::get_options_array('inventivo_scroll_to_top_option_name'), self::get_default_options() );

		switch($options['alignment']) {
			case 'left':
				$alignment = 'left';
				break;
			case 'right':
				$alignment = 'right';
				break;
			default:
				$alignment = 'left';
				break;
		}

		wp_register_script('inventivo-scroll-to-top', plugins_url('/public/js/scroll-to-top.js', __FILE__), array( 'jquery' ), '1.0', true);

		$invscrolltotopoptions = array(
			'background_color' => esc_attr($options['background_color']),
			'icon_color' => esc_attr($options['icon_color']),
			'alignment' => $alignment
		);
		wp_localize_script('inventivo-scroll-to-top', 'invscrolltotopoptions', $invscrolltotopoptions);

		// Enqueued script with localized data.
		wp_enqueue_script('inventivo-scroll-to-top');
	}


	/**
	 * Register and add settings
	 */
	public function page_init()
	{
		register_setting(
			'inventivo_scroll_to_top_option_group', // Option group
			'inventivo_scroll_to_top_option_name', // Option name
			array( $this, 'sanitize' ) // Sanitize
		);

		add_settings_section(
			'setting_section_id', // ID
			esc_html__('Setup', 'inventivo-scroll-to-top'), // Title
			array( $this, 'print_section_info' ), // Callback
			'inventivo_scroll_to_top_setting_admin' // Page
		);

		add_settings_field(
			'background_color', // ID
			'Button background color', // Title
			array( $this, 'background_color_callback' ), // Callback
			'inventivo_scroll_to_top_setting_admin', // Page
			'setting_section_id' // Section
		);

		add_settings_field(
			'icon_color', // ID
			'Icon background color', // Title
			array( $this, 'icon_color_callback' ), // Callback
			'inventivo_scroll_to_top_setting_admin', // Page
			'setting_section_id' // Section
		);

		add_settings_field(
			'alignment', // ID
			'Alignment on page', // Title
			array( $this, 'alignment_callback' ), // Callback
			'inventivo_scroll_to_top_setting_admin', // Page
			'setting_section_id' // Section
		);
	}

	public function sanitize( $input )
	{
		$newInput = array();
		if(isset($input['background_color']) )
			$newInput['background_color'] = sanitize_text_field($input['background_color']);

		if(isset($input['icon_color']) )
			$newInput['icon_color'] = sanitize_text_field($input['icon_color']);

		if(isset($input['alignment']) )
			$newInput['alignment'] = sanitize_text_field($input['alignment']);

		return $newInput;
	}

	public function print_section_info()
	{
		_e('Please adapt colors and button alignment to your needs:', 'inventivo-scroll-to-top');
	}

	public function background_color_callback()
	{
		printf(
			'<input type="text" id="background_color" name="inventivo_scroll_to_top_option_name[background_color]" value="%s" /> '.__('Example: #a6ce38', 'inventivo-scroll-to-top'),
			isset($this->options['background_color']) ? esc_attr($this->options['background_color']) : __('#a6ce38', 'inventivo-scroll-to-top')
		);
	}
	public function icon_color_callback()
	{
		printf(
			'<input type="text" id="icon_color" name="inventivo_scroll_to_top_option_name[icon_color]" value="%s" /> '.__('Example: #FFFFFF', 'inventivo-scroll-to-top'),
			isset($this->options['icon_color']) ? esc_attr($this->options['icon_color']) : __('#FFFFFF', 'inventivo-scroll-to-top')
		);
	}

	public function alignment_callback()
	{
		if ($this->options['alignment'] == 'left') {
			$selectedA = 'selected';
		}

		if ($this->options['alignment'] == 'right') {
			$selectedB = 'selected';
		}

		printf(
			'<select id="alignment" name="inventivo_scroll_to_top_option_name[alignment]">
                <option value="left" '.$selectedA.'>Left</option>
                <option value="right" '.$selectedB.'>Right</option>
		    </select>',
			isset($this->options['alignment']) ? esc_attr($this->options['alignment']) : __('Alignment', 'inventivo-scroll-to-top')
		);
	}

	public function admin_notice_get_pro() {
		if ( get_user_meta( get_current_user_id(), 'inv_notice_dismissed_scroll_to_top' ) || get_user_meta( get_current_user_id(), 'inv_notice_dismissed' ) ) {
			return;
		}

		echo '<div class="notice notice-success is-dismissible">
            <div class="hreflang-x-default-tag-for-wpml-inventivo-wrapper">
                <div class="hreflang-x-default-tag-for-wpml-inventivo-element">
                    <a href="https://www.inventivo.de/webseiten/wordpress-plugins" target="_blank">
                        <img src="' . esc_url( plugins_url( 'admin/images/icon-256x256-1.png', __FILE__ ) ) . '" />
                    </a>
                </div>
                <div class="hreflang-x-default-tag-for-wpml-inventivo-element">
                    <h2>More free WordPress plugins</h2>
                    <p><strong>Thanks for using this plugin.</strong><br />
                        We build small and focused WordPress plugins and publish them for free.<br />
                        </p>
                    <p>
                        <a class="wp-core-ui button" target="_blank" href="https://www.inventivo.de/webseiten/wordpress-plugins" style="color: #FFFFFF; background: #A6CE38; border-color: #A6CE38">
                        See all our plugins
                        </a>
                        <br /><br />
                        <a href="' . esc_url( wp_nonce_url( add_query_arg( 'inv-notice-dismissed', '1' ), 'inv_notice_dismiss' ) ) . '" style="color: #AAAAAA;">Dismiss</a>
                    </p>
                </div>
            </div>
        </div>';
	}

	public function notice_dismissed()
	{
		if ( ! isset( $_GET['inv-notice-dismissed'] ) ) {
			return;
		}

		if ( ! current_user_can( 'read' ) ) {
			return;
		}

		$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, 'inv_notice_dismiss' ) ) {
			return;
		}

		add_user_meta( get_current_user_id(), 'inv_notice_dismissed_scroll_to_top', 'true', true );
	}

	public function plugin_deactivate()
	{
		delete_user_meta( get_current_user_id(), 'inv_notice_dismissed_scroll_to_top' );
	}

	public function inv_admin_style() {
		if ( get_user_meta( get_current_user_id(), 'inv_notice_dismissed_scroll_to_top' ) || get_user_meta( get_current_user_id(), 'inv_notice_dismissed' ) ) {
			return;
		}

		wp_enqueue_style('inventivo-scroll-to-top-admin-styles', plugins_url('admin/css/admin-styles.css', __FILE__));
	}
}

if (is_admin()) {
	$mySettingsPage = new InventivoScrollToTopSettingsPage();
}