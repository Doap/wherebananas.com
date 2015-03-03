<?php
/**
 * Plugin Name: Store Locator Plus : Enhanced Map
 * Plugin URI: http://www.storelocatorplus.com/product/slp4-enhanced-map/
 * Description: A premium add-on pack for Store Locator Plus that adds enhanced map UI to the plugin.
 * Version: 4.2.03
 * Author: Charleston Software Associates
 * Author URI: http://charlestonsw.com/
 * Requires at least: 3.4
 * Tested up to : 4.0
 *
 * Text Domain: csa-slp-em
 * Domain Path: /languages/
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// No SLP? Get out...
//
include_once( ABSPATH . 'wp-admin/includes/plugin.php' ); 
if ( !function_exists('is_plugin_active') ||  !is_plugin_active( 'store-locator-le/store-locator-le.php')) {
    return;
}

// Make sure the class is only defined once.
//
if (!class_exists('SLPEnhancedMap'   )) {

    require_once( WP_PLUGIN_DIR . '/store-locator-le/include/base_class.addon.php');

    /**
     * The Enhanced Map Add-On Pack for Store Locator Plus.
     *
     * @package StoreLocatorPlus\EnhancedMap
     * @author Lance Cleveland <lance@charlestonsw.com>
     * @copyright 2012-2014 Charleston Software Associates, LLC
     */
    class SLPEnhancedMap extends SLP_BaseClass_Addon  {

        //-------------------------------------
        // Properties
        //-------------------------------------

        /**
         * Settable options for this plugin.
         *
         * @var mixed[] $options
         */
        public  $options                = array(
            'bubblelayout'          => ''   ,
            'hide_bubble'           => '0'  ,
            'installed_version'     => ''   ,
            'no_autozoom'           => '0'  ,
            'no_homeicon_at_start'  => '0'  ,
            'maplayout'             => ''   ,
            'map_initial_display'   => 'map',
        );

        //------------------------------------------------------
        // METHODS
        //------------------------------------------------------

        /**
         * Invoke the plugin.
         *
         * This ensures a singleton of this plugin.
         *
         * @static
         */
        public static function init() {
            static $instance = false;
            if (!$instance) {
                load_plugin_textdomain('csa-slp-em', false, dirname(plugin_basename(__FILE__)) . '/languages/');
                $instance = new SLPEnhancedMap(
                    array(
                        'version'               => '4.2.03'                                     ,
                        'min_slp_version'       => '4.2.04'                                     ,

                        'name'                  => __('Enhanced Map', 'csa-slp-em')             ,
                        'option_name'           => 'csl-slplus-EM-options'                      ,
                        'slug'                  => plugin_basename(__FILE__)                    ,
                        'metadata'              => get_plugin_data(__FILE__, false, false)      ,

                        'url'                   => plugins_url('', __FILE__)                    ,
                        'dir'                   => plugin_dir_path(__FILE__)                    ,

                        'activation_class_name'     => 'SLPEnhancedMap_Activation'              ,
                        'admin_class_name'          => 'SLPEnhancedMap_Admin'                   ,
                        'ajax_class_name'           => 'SLPEnhancedMap_AJAX'                    ,
                        'userinterface_class_name'  => 'SLPEnhancedMap_UI'                      ,
                    )
                );
            }
            return $instance;
        }

        /**
         * Initialize the options properties from the WordPress database.
         */
        function init_options() {
            parent::init_options();
            if ( empty( $this->options['bubblelayout'] ) ) {
                $this->options['bubblelayout'] = $this->slplus->defaults['bubblelayout'];
            }
        }

        /**
         * Create a Map Settings Debug My Plugin panel.
         *
         * @return null
         */
        static function create_DMPPanels() {
            if (!isset($GLOBALS['DebugMyPlugin'])) { return; }
            if (class_exists('DMPPanelSLPEM') == false) {
                require_once(plugin_dir_path(__FILE__).'class.dmppanels.php');
            }
            $GLOBALS['DebugMyPlugin']->panels['slp.em']           = new DMPPanelSLPEM();
        }

        /**
         * Simplify the plugin debugMP interface.
         *
         * @param string $type
         * @param string $hdr
         * @param string $msg
         */
        function debugMP($type,$hdr,$msg='') {
            $this->slplus->debugMP('slp.em',$type,$hdr,$msg,NULL,NULL,true);
        }
    }

    // Hook to invoke the plugin.
    //
    add_action('init'           ,array('SLPEnhancedMap','init'              ));
    add_action('dmp_addpanel'   ,array('SLPEnhancedMap','create_DMPPanels'  ));
}
// Dad. Explorer. Rum Lover. Code Geek. Not necessarily in that order.
