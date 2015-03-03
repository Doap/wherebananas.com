<?php
if (! class_exists('SLPEnhancedMap_UI')) {
    require_once(SLPLUS_PLUGINDIR.'/include/base_class.ui.php');

    /**
     * Holds the UI-only code.
     *
     * This allows the main plugin to only include this file in the front end
     * via the wp_enqueue_scripts call.   Reduces the back-end footprint.
     *
     * @package StoreLocatorPlus\EnhancedMap\UI
     * @author Lance Cleveland <lance@charlestonsw.com>
     * @copyright 2014 Charleston Software Associates, LLC
     */
    class SLPEnhancedMap_UI  extends SLP_BaseClass_UI {

        //-------------------------------------
        // Properties
        //-------------------------------------

        /**
         * This addon pack.
         *
         * @var \SLPEnhancedMap $addon
         */
        protected $addon;

        //-------------------------------------
        // Methods
        //-------------------------------------

        /**
         * Add WordPress and SLP hooks and filters.
         *
         * WP syntax reminder: add_filter( <filter_name> , <function> , <priority> , # of params )
         *
         * Remember: <function> can be a simple function name as a string
         *  - or - array( <object> , 'method_name_as_string' ) for a class method
         * In either case the <function> or <class method> needs to be declared public.
         *
         * @link http://codex.wordpress.org/Function_Reference/add_filter
         *
         */
        public function add_hooks_and_filters() {
            add_filter( 'slp_map_center'        , array( $this , 'set_MapCenter'            ) , 15 , 1 );
            add_filter( 'slp_shortcode_atts'    , array( $this , 'extend_main_shortcode'    ) , 15 , 1 );
            add_filter( 'slp_js_options'        , array( $this , 'filter_ModifyJSOptions'   ) , 15 , 1 );
            add_filter( 'slp_map_html'          , array( $this , 'filter_ModifyMapOutput'   ) , 05 , 1 );
            add_filter( 'slp_script_data'       , array( $this , 'filter_ModifyScriptData'  ) , 15 , 1 );

        }

        /**
         * Generate the HTML for the map on/off slider button if requested.
         *
         * @return string HTML for the map slider.
         */
        function createstring_MapDisplaySlider() {

            $content = '';

            if  ( $this->slplus->UI->ShortcodeOrSettingEnabled('show_maptoggle','show_maptoggle') ) {
                $content =
                    $this->slplus->UI->CreateSliderButton(
                        'maptoggle',
                        __('Map','csa-slp-em'),
                        !$this->slplus->UI->ShortcodeOrSettingEnabled('hide_map','enmap_hidemap'),
                        "jQuery('#map').toggle();jQuery('#slp_tagline').toggle();"
                    );
            }

            return $content;
        }

        /**
         * Extends the main SLP shortcode approved attributes list, setting defaults.
         *
         * This will extend the approved shortcode attributes to include the items listed.
         * The array key is the attribute name, the value is the default if the attribute is not set.
         *
         * @param mixed[] $valid_attributes - current list of approved attributes
         * @return mixed[]
         */
        public function extend_main_shortcode($valid_attributes) {
            return array_merge(
                array(
                    'center_map_at'     => null,
                    'hide_map'          => null,
                    'show_maptoggle'    => null,
                ),
                $valid_attributes
            );
        }

        /**
         * Modify the slplus.options object going into SLP.js
         *
         * @param mixed[] $options
         * @return mixed
         */
        function filter_ModifyJSOptions( $options ) {
            if ( $this->slplus->is_CheckTrue( $this->addon->options['hide_bubble'] ) ) { $this->addon->options['bubblelayout'] = ''; }
            return array_merge( $options , $this->addon->options );
        }

        /**
         * Modify the map layout.
         *
         * @param string  $HTML
         * @return string
         */
        function filter_ModifyMapOutput( $HTML ) {
            $this->addon->debugMP('msg','map_initial_display is:' . $this->addon->options['map_initial_display']);

            //---------------------
            // Hide The Map?
            //
            if ($this->slplus->UI->ShortcodeOrSettingEnabled('hide_map','enmap_hidemap')) {
                return '<div id="map" style="display:none;"></div>';
            }


            //---------------------
            // Map Layout
            //
            $HTML .=
                empty($this->addon->options['maplayout'])            ?
                    $this->slplus->defaults['maplayout']    :
                    $this->addon->options['maplayout']             ;

            // Map Toggle Interface Element
            //
            if (!isset($this->slplus->data['show_maptoggle'])) {
                $this->slplus->data['show_maptoggle'] = (
                ($this->slplus->settings->get_item('show_maptoggle',0) == 1) ?
                    'true' :
                    'false'
                );
            }
            $HTML = $this->createstring_MapDisplaySlider() . $HTML;

            // Map hidden
            //
            if ($this->addon->options['map_initial_display'] == 'hide') {
                $HTML =
                    '<div id="map_box_map">' .
                    $HTML .
                    '</div>';

            // Map Displayed
            //
            } else if ($this->addon->options['map_initial_display'] == 'image') {

                // Starting Image
                //
                $startingImage          = get_option('sl_starting_image','');
                $startingImageActive    = !empty($startingImage);
                if ($startingImageActive) {

                    // Make sure URL starts with the plugin URL if it is not an absolute URL
                    //
                    $startingImage =
                        ((preg_match('/^http/',$startingImage) <= 0) ?SLPLUS_PLUGINURL:'') .
                        $startingImage
                    ;

                    $HTML =
                        '<div id="map_box_image" ' .
                        'style="'.
                        "width:". $this->slplus->data['sl_map_width'].
                        $this->slplus->data['sl_map_width_units'] .
                        ';'.
                        "height:".$this->slplus->data['sl_map_height'].
                        $this->slplus->data['sl_map_height_units'].
                        ';'.
                        '"'.
                        '>'.
                        "<img src='{$startingImage}'>".
                        '</div>' .
                        '<div id="map_box_map">' .
                        $HTML .
                        '</div>'
                    ;
                }
            }

            return $HTML;
        }

        /**
         * Modify the script data array prior to localization.
         *
         * Here we merge the options from this add-on pack to the main options.
         *
         * @param mixed[] $scriptData
         * @return mixed[]
         */
        function filter_ModifyScriptData( $scriptData ) {
            return array_merge(
                $scriptData,
                array(
                    'options' => array_merge($scriptData['options'],$this->addon->options)
                )
            );
        }

        /**
         * Change the map center as specified.
         *
         * @param string $addy original address (center of country)
         * @return string
         */
        public function set_MapCenter($addy) {
            if (!empty($this->slplus->data['center_map_at']) && (preg_replace('/\W/','',$this->slplus->data['center_map_at']) != '')) {
                return str_replace(array("\r\n","\n","\r"),', ',esc_attr($this->slplus->data['center_map_at']));
            }
            return $addy;
        }
    }
}