<?php /*
Contributors: inventivogermany
Plugin Name:  Scroll to top | inventivo
Plugin URI:   https://www.inventivo.de/wordpress-agentur/wordpress-plugins
Description:  Display scroll to top button at page bottom
Version:      0.0.4
Author:       Nils Harder
Author URI:   https://www.inventivo.de
Tags: scroll top
Requires at least: 3.0
Tested up to: 5.2.2
Stable tag: 0.0.4
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

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Version of the plugin
define('INVENTIVO_SCROLL_TO_TOP_CURRENT_VERSION', '1.0.0' );

add_action('admin_enqueue_scripts', array('InventivoScrollToTopSettingsPage','inventivo_scroll_to_top_admincss'));
add_action( 'wp_enqueue_scripts', array('InventivoScrollToTopSettingsPage','inventivo_scroll_to_top_js' ));
add_action( 'wp_enqueue_scripts', array('InventivoScrollToTopSettingsPage','inventivo_scroll_to_top_publiccss' ));

class InventivoScrollToTopSettingsPage {

    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action('plugins_loaded',array($this,'inventivo_scroll_to_top_load_textdomain'));
        //add_action('init',array($this,'my_i18n_debug'));
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }


    function inventivo_scroll_to_top_load_textdomain() {
        load_plugin_textdomain( 'inventivo-scroll-to-top', false, dirname( plugin_basename( __FILE__ ) ) . '/languages');
    }

    function my_i18n_debug(){

        $loaded=load_plugin_textdomain( 'inventivo-scroll-to-top', false, dirname( plugin_basename( __FILE__ ) ) . '/languages');

        if ( ! $loaded ){
            echo "<hr/>";
            echo "Error: the mo file was not found! ";
            exit();
        }else{
            echo "<hr/><strong>Debug info</strong>:<br/>";
            //echo "WPLANG: ". WPLANG;
            echo "<br/>";
            echo "translate test: ". __('Scroll to top | inventivo','inventivo-scroll-to-top');
            exit();
        }
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Settings Admin',
            esc_html__( 'Scroll top top | inventivo', 'inventivo-scroll-to-top' ),
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
        $this->options = get_option( 'inventivo_scroll_to_top_option_name' );
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Scroll to top | inventivo', 'inventivo-scroll-to-top' ); ?></h1>
            <form method="post" action="options.php">
                <?php
                // This prints out all hidden setting fields
                settings_fields( 'inventivo_scroll_to_top_option_group' );
                do_settings_sections( 'inventivo_scroll_to_top_setting_admin' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    // Add admin styles
    public function inventivo_scroll_to_top_admincss() {
        wp_enqueue_style('inventivo-scroll-to-top-admin-styles', plugins_url('/admin/css/admin-styles.css', __FILE__));
    }

    // Add public styles
    public function inventivo_scroll_to_top_publiccss() {
        wp_register_style( 'inventivo-scroll-to-top-publiccss', plugins_url('/public/css/scroll-to-top.css', __FILE__) );
        wp_enqueue_style( 'inventivo-scroll-to-top-publiccss' );
        wp_register_style( 'inventivo-scroll-to-top-genericons', plugins_url('/public/css/genericons.css', __FILE__) );
        wp_enqueue_style( 'inventivo-scroll-to-top-genericons' );
    }


    public function inventivo_scroll_to_top_js() {
        $options = get_option( 'inventivo_scroll_to_top_option_name' );

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

        wp_register_script( 'inventivo-scroll-to-top', plugins_url( '/public/js/scroll-to-top.js', __FILE__ ), array( 'jquery' ), '1.0', true );

        $invscrolltotopoptions = array(
            'background_color' => esc_attr($options['background_color']),
            'icon_color' => esc_attr($options['icon_color']),
            'alignment' => $alignment
        );
        wp_localize_script( 'inventivo-scroll-to-top', 'invscrolltotopoptions', $invscrolltotopoptions );

        // Enqueued script with localized data.
        wp_enqueue_script( 'inventivo-scroll-to-top' );
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
        $new_input = array();
        if( isset( $input['background_color'] ) )
            $new_input['background_color'] = sanitize_text_field( $input['background_color'] );

        if( isset( $input['icon_color'] ) )
            $new_input['icon_color'] = sanitize_text_field( $input['icon_color'] );

        if( isset( $input['alignment'] ) )
            $new_input['alignment'] = sanitize_text_field( $input['alignment'] );

        return $new_input;
    }

    public function print_section_info()
    {
        _e( 'Please adapt colors and button alignment to your needs:', 'inventivo-scroll-to-top' );
    }

    public function background_color_callback()
    {
        printf(
            '<input type="text" id="background_color" name="inventivo_scroll_to_top_option_name[background_color]" value="%s" /> '.__( 'Example: #a6ce38', 'inventivo-scroll-to-top' ),
            isset( $this->options['background_color'] ) ? esc_attr( $this->options['background_color']) : __( '#a6ce38', 'inventivo-scroll-to-top' )
        );
    }
    public function icon_color_callback()
    {
        printf(
            '<input type="text" id="icon_color" name="inventivo_scroll_to_top_option_name[icon_color]" value="%s" /> '.__( 'Example: #FFFFFF', 'inventivo-scroll-to-top' ),
            isset( $this->options['icon_color'] ) ? esc_attr( $this->options['icon_color']) : __( '#FFFFFF', 'inventivo-scroll-to-top' )
        );
    }

    public function alignment_callback()
    {
        if($this->options['alignment'] == 'left') { $selected1 = 'selected'; }
        if($this->options['alignment'] == 'right') { $selected2 = 'selected'; }
        printf(
            '<select id="alignment" name="inventivo_scroll_to_top_option_name[alignment]">
                <option value="left" '.$selected1.'>Left</option>
                <option value="right" '.$selected2.'>Right</option>
		    </select>',
            isset( $this->options['alignment'] ) ? esc_attr( $this->options['alignment']) : __('Alignment','inventivo-scroll-to-top')
        );
    }
}

if( is_admin() )
    $my_settings_page = new InventivoScrollToTopSettingsPage();