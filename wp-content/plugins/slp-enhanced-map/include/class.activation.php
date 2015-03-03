<?php
if (! class_exists('SLPEnhancedMap_Activation')) {

    require_once(SLPLUS_PLUGINDIR.'/include/base_class.activation.php');

    /**
     * Manage plugin activation.
     *
     * @package StoreLocatorPlus\EnhancedMap\Activation
     * @author Lance Cleveland <lance@charlestonsw.com>
     * @copyright 2013 - 2014 Charleston Software Associates, LLC
     *
     */
    class SLPEnhancedMap_Activation extends SLP_BaseClass_Activation {

        /**
         * Update or create the data tables.
         */
        function update() {

            // If below 4.1.01 then set map_initial_display option base on sl_starting_image option
            //
            if ( version_compare( $this->addon->options['installed_version'] , '4.1.01' , '<' ) ) {
                $startingImage = get_option('sl_starting_image','');
                if (!empty($startingImage)) {
                    $this->addon->options['map_initial_display'] = 'image';
                } else {
                    $this->addon->options['map_initial_display'] = 'map';
                }
            }
        }
    }
}