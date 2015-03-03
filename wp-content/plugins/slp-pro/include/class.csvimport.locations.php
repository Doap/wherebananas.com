<?php
if (!defined( 'ABSPATH'     )) { exit;   } // Exit if accessed directly, dang hackers

// Make sure the class is only defined once.
//
if (!class_exists('CSVImport')) {
    require_once(SLPLUS_PLUGINDIR . '/include/class.csvimport.php');
}

// Make sure the class is only defined once.
//
if (!class_exists('CSVImportLocations')) {

    /**
     * CSV Import of Locations
     *
     * @package StoreLocatorPlus\ProPack\CSVImportLocations
     * @author Lance Cleveland <lance@charlestonsw.com>
     * @copyright 2013 - 2014 Charleston Software Associates, LLC
     */
    class CSVImportLocations extends CSVImport {

        //----------------------------------------------------------------------------
        // Properties
        //----------------------------------------------------------------------------


        /**
         * The params received during the cron job call.
         *
         * @var mixed[]
         */
        private $action_params;

        /**
         * This addon pack.
         *
         * @var \SLPPro $addon
         */
        public $addon;

        /**
         *
         * @var _FILES
         */
        public $file_meta;

        /**
         * Should goecoding be skipped?
         *
         * @var boolean $skip_geocoding
         */
        private $skip_geocoding = false;

        /**
         * The manage locations settings interface object.
         *
         * @var \wpCSL_settings__slplus
         */
        private $settings;

        //----------------------------------------------------------------------------
        // Methods
        //----------------------------------------------------------------------------

        /**
         * Setup a standard CSV Import object and attach the processing method and data filters.
         *
         * @param mixed $params
         */
        function __construct($params) {
            parent::__construct($params);

            if ( ! defined('DOING_CRON') ) {
                $this->settings = $this->slplus->AdminUI->ManageLocations->settings;
            }

            // Set private properties that are only for this class.
            //
            $this->skip_geocoding= $this->slplus->is_CheckTrue( $this->addon->options['csv_skip_geocoding' ] );

            // Set inherited properties specific to this class.
            //
            $this->strip_prefix = 'sl_';
            $this->firstline_has_fieldname  = $this->slplus->is_CheckTrue( $this->addon->options['csv_first_line_has_field_name'] );
            $this->skip_firstline           = $this->firstline_has_fieldname || $this->slplus->is_CheckTrue( $this->addon->options['csv_skip_first_line'] );

            // Add filters and hooks for this class.
            //
            add_filter('slp_csv_processing_messages', array($this,'filter_SetMessages'          ));
            add_filter('slp_csv_default_fieldnames' , array($this,'filter_SetDefaultFieldNames' ));
            add_action('slp_csv_processing'         , array($this,'action_ProcessCSVFile'       ));
        }

        /**
         * Process the lines of the CSV file.
         */
        function action_ProcessCSVFile() {
            $this->addon->debugMP('msg', get_class() . '::' . __FUNCTION__ );

            $num = count($this->data);
            $locationData = array();
            if ($num <= $this->maxcols) {
                for ($fldno=0; $fldno < $num; $fldno++) {
                    $locationData[$this->fieldnames[$fldno]] = $this->data[$fldno];
                }

                // Record Add/Update
                //

                // FILTER: slp_csv_locationdata
                // Pre-location import processing.
                //
                $locationData = apply_filters('slp_csv_locationdata',$locationData);

                // Go add the CSV Data to the locations table.
                //
                $resultOfAdd = $this->slplus->currentLocation->add_to_database(
                        $locationData,
                        $this->addon->options['csv_duplicates_handling'],
                        $this->skip_geocoding ||
                            (
                            isset     ($locationData['sl_longitude']) && isset     ($locationData['sl_latitude']) &&
                            is_numeric($locationData['sl_longitude']) && is_numeric($locationData['sl_latitude'])
                            )
                        );

                // FILTER: slp_csv_locationdata_added
                // Post-location import processing.
                //
                apply_filters('slp_csv_locationdata_added',$locationData, $resultOfAdd);

                // Update processing counts.
                //
                $this->processing_counts[$resultOfAdd]++;
            } else {
                $this->processing_report[] = __('CSV Records has too many fields.','csa-slp-pro');
                $this->processing_report[] =
                    sprintf( __('Got %d expected less than %d.', 'csa-slp-pro') , $num , $this->maxcols );
            }
        }

        /**
         * Add the File Settings panel to the Import section of Locations.
         *
         * @param $section_name
         */
        private function  add_FileSettingsPanel( $section_name ) {
            $panel_name = __( 'File Settings', 'csa-slp-pro' );

            // Skip First Line
            //
            $this->settings->add_ItemToGroup(
                array(
                    'section'       => $section_name     ,
                    'group'         => $panel_name       ,
                    'type'          => 'checkbox'        ,
                    'setting'       => 'PRO-options[csv_skip_first_line]',
                    'value'         => $this->addon->options['csv_skip_first_line'],
                    'label'         => __('Skip First Line','csa-slp-pro'),
                    'description'   => __('Skip the first line of the import file.','csa-slp-pro')
                )
            );

            // First Line Has Field Name
            //
            $this->settings->add_ItemToGroup(
                array(
                    'section'       => $section_name     ,
                    'group'         => $panel_name       ,
                    'type'          => 'checkbox'       ,
                    'setting'       => 'PRO-options[csv_first_line_has_field_name]',
                    'label'         => __('First Line Has Field Name','csa-slp-pro'),
                    'value'         => $this->addon->options['csv_first_line_has_field_name'],
                    'description'   =>
                        __('Check this if the first line contains the field names.','csa-slp-pro') . ' ' .
                        sprintf(__('Text must match the <a href="%s">approved field name list</a>.','csa-slp-pro'),$this->slplus->support_url)
                )
            );

            // Skip Geocoding
            //
            $this->settings->add_ItemToGroup(
                array(
                    'section'       => $section_name     ,
                    'group'         => $panel_name       ,
                    'type'          => 'checkbox'       ,
                    'setting'       => 'PRO-options[csv_skip_geocoding]',
                    'value'         => $this->addon->options['csv_skip_geocoding'],
                    'label'         => __('Skip Geocoding','csa-slp-pro'),
                    'description'   =>
                        __('Do not check with the Geocoding service to get latitude/longitude.  Locations without a latitude/longitude will NOT appear on map base searches.','csa-slp-pro')
                )
            );

            // Duplicates Handling
            //
            $this->settings->add_ItemToGroup(
                array(
                    'section'       => $section_name     ,
                    'group'         => $panel_name       ,
                    'type'          => 'dropdown'       ,
                    'setting'       => 'PRO-options[csv_duplicates_handling]',
                    'selectedVal'   => $this->addon->options['csv_duplicates_handling'],
                    'label'         => __('Duplicates Handling','csa-slp-pro'),
                    'description'   =>
                        __('How should duplicates be handled? ','csa-slp-pro') .
                        __('Duplicates are records that match on name and complete address with country. ','csa-slp-pro') .
                        __('Add (default) will add new records when duplicates are encountered. ','csa-slp-pro') . '<br/>' .
                        __('Skip will not process duplicate records. ','csa-slp-pro') . '<br/>' .
                        __('Update will update duplicate records. ','csa-slp-pro') .
                        __('To update name and address fields the CSV must have the ID column with the ID of the existing location.','csa-slp-pro')
                ,
                    'custom'    =>
                        array(
                            array(
                                'label'     => __('Add','csa-slp-pro'),
                                'value'     =>'add',
                            ),
                            array(
                                'label'     => __('Skip','csa-slp-pro'),
                                'value'     =>'skip',
                            ),
                            array(
                                'label'     => __('Update','csa-slp-pro'),
                                'value'     =>'update',
                            ),
                        )
                )
            );

            // Add help text
            //
            $this->settings->add_ItemToGroup(
                array(
                    'section'       => $section_name    ,
                    'group'         => $panel_name      ,
                    'type'          => 'subheader'      ,
                    'label'         => '',
                    'description'   =>
                        sprintf(__('See the %s for more details on the import format.','csa-slp-pro'),
                            sprintf('<a href="%slocations/bulk-data-import/">',$this->slplus->support_url) .
                            __('online documentation','csa-slp-pro') .
                            '</a>'
                        ),
                    'show_label'    => false
                ));
        }

        /**
         * Local File Import Panel.
         *
         * @param $section_name
         */
        function add_UploadCSVPanel( $section_name ) {
            $panel_name = __('Upload CSV File', 'csa-slp-pro');

            // Form Start with Media Input
            //
            $this->settings->add_ItemToGroup(
                array(
                    'section'       => $section_name    ,
                    'group'         => $panel_name      ,
                    'type'          => 'custom'         ,
                    'show_label'    => false            ,
                    'custom'         =>
                            '<input type="file" name="csvfile" value="" id="bulk_file" size="40">'              .
                            "<input type='submit' value='".__('Upload Locations', 'csa-slp-pro')."' "           .
                            "class='button-primary'>"
                )
            );
        }


        /**
         * Remote File Import Panel.
         *
         * @param $section_name
         */
        function add_RemoteFileImportPanel( $section_name ) {
            $panel_name = __('File Import', 'csa-slp-pro');

            // Remove File URL
            //
            $this->settings->add_ItemToGroup(
                array(
                    'section'       => $section_name                            ,
                    'group'         => $panel_name                              ,
                    'label'         => __('CSV File URL','csa-slp-pro')         ,
                    'setting'       => 'PRO-options[csv_file_url]'              ,
                    'type'          => 'text'                                   ,
                    'value'         => $this->addon->options['csv_file_url']    ,
                    'description'   =>
                        __('Enter a full URL for a CSV file you wish to import', 'csa-slp-pro')
                )
            );

            // Cron Import Recurrence
            //
            $this->settings->add_ItemToGroup(
                array(
                    'section'       => $section_name     ,
                    'group'         => $panel_name       ,
                    'type'          => 'dropdown'       ,
                    'setting'       => 'PRO-options[cron_import_recurrence]',
                    'selectedVal'   => $this->addon->options['cron_import_recurrence'],
                    'label'         => __('Import Recurrence','csa-slp-pro'),
                    'description'   =>
                        __('How often to fetch the file from the URL. ','csa-slp-pro') .
                        __('None loads the remote file immediately with no background processing. ','csa-slp-pro') .
                        __('At loads the file one time on or after the time specified. ','csa-slp-pro')
                        ,
                    'custom'    =>
                        array(
                            array(
                                'label'     => __('None','csa-slp-pro'),
                                'value'     =>'none',
                            ),
                            array(
                                'label'     => __('At','csa-slp-pro'),
                                'value'     =>'at',
                            ),
                            array(
                                'label'     => __('Hourly','csa-slp-pro'),
                                'value'     =>'hourly',
                            ),
                            array(
                                'label'     => __('Twice Daily','csa-slp-pro'),
                                'value'     =>'twicedaily',
                            ),
                            array(
                                'label'     => __('Daily','csa-slp-pro'),
                                'value'     =>'daily',
                            ),
                        )
                )
            );

            // Cron Import Time
            //
            //
            $this->settings->add_ItemToGroup(
                array(
                    'section'       => $section_name     ,
                    'group'         => $panel_name       ,
                    'type'          => 'text'            ,
                    'setting'       => 'PRO-options[cron_import_timestamp]'             ,
                    'value'         => $this->addon->options['cron_import_timestamp']   ,
                    'label'         => __('Daily Import Time','csa-slp-pro')            ,
                    'description'   =>
                        __('What time to run the recurring import from this URL.  '                                     , 'csa-slp-pro') .
                        __('WordPress cron is not exact, it executes the next time a visitor comes to your site.  '     , 'csa-slp-pro') .
                        __('WordPress times are UTC/GMT time NOT local time.  '                                         , 'csa-slp-pro') .
                        __('Set to none and leave the URL blank to clear the cron job.  '                               , 'csa-slp-pro') .
                        __('Example: 14:25.  (Default: empty = do not run daily cron)'                                  , 'csa-slp-pro')
                )
            );

            // Show Cron Info
            //
            $this->settings->add_ItemToGroup(
                array(
                    'section'       => $section_name    ,
                    'group'         => $panel_name      ,
                    'type'          => 'subheader'      ,
                    'label'         => '',
                    'description'   => $this->createstring_CronInfo(),
                    'show_label'    => false
                ));


            // Form Start with Media Input
            //
            $this->settings->add_ItemToGroup(
                array(
                    'section'       => $section_name    ,
                    'group'         => $panel_name      ,
                    'type'          => 'custom'         ,
                    'show_label'    => false            ,
                    'custom'         =>
                        "<input type='submit' value='".__('Import Locations', 'csa-slp-pro')."' "           .
                        "class='button-primary'>"
                )
            );

        }

        /**
         * Add the bulk upload form to add locations.
         */
        function create_BulkUploadForm() {
            $section_name = __('Import','csa-slp-pro');

            $this->settings->add_section(
                array(
                    'name'          => $section_name ,

                    'opening_html'  =>
                        "<form id='importForm' name='importForm' method='post' enctype='multipart/form-data'>"  .
                        "<input type='hidden' name='act' id='act' value='import' />" ,

                    'closing_html' =>
                        '</form>'
                )
            );

            // File Settings Panel
            //
            $this->add_FileSettingsPanel( $section_name );

            // Upload CSV Local
            //
            $this->add_UploadCSVPanel( $section_name );

            // File Import Remoete
            //
            $this->add_RemoteFileImportPanel( $section_name );

        }

        function createstring_CronInfo() {
            $box_title = __('Scheduled Import Activity', 'csa-slp-pro');

            // Opening Divs
            //
            $html =
                '<div class="metabox-holder">' .
                '<div class="meta-box-sortables">' .
                '<div id="location_import_cron_messages" class="postbox">'                      .
                    '<div class="handlediv" title="Click to toggle"><br></br></div>'    .
                    "<h3 class='hndle'><span>{$box_title}</span></h3>"                              .
                    '<div class="inside">'
            ;

            $html .= $this->createstring_CronSchedule();

            $html .= $this->createstring_CronMessages();

            // Closing Divs
            //
            $html .=
                   '</div>' .
                   '</div>' .
                   '</div>' .
                '</div>'
                ;


            return $html;
        }

        /**
         * Create cron messages box.
         */
        function createstring_CronMessages() {
            $title= __('Location Import Cron Messages','csa-slp-pro');

            $this->cron_status  = get_option('slp-pro-cron',array());

            if ( count( $this->cron_status ) ) {
                $html =
                    '<div class="activity-block">' .
                    "<h4>{$title}</h4>";
                foreach ($this->cron_status as $message) {
                    $html .= sprintf('<span class="cron_message">%s</span>', $message);
                }

                $html .= '</div>';
            } else {
                $html = '';
            }

            return $html;
        }

        /**
         * Get the cron schedule as a formatted HTML string.
         *
         * @return string
         */
        function createstring_CronSchedule() {
            $html = '';
            $schedule = wp_get_schedule('cron_csv_import', array('import_csv', $this->file_meta ) );
            if ( !empty($schedule) ) {
                $html =
                    sprintf( __('CSV file imports are currently scheduled to run %s.', 'csa-slp-pro') , $schedule ) .
                    '<br/><br/>'
                    ;
            }
            $html .= sprintf( __('The current WordPress time (GMT) is %s.','csa-slp-pro') , current_time( 'mysql' , true ) );
            return $html;
        }

        /**
         * Set the process count output strings the users sees after an upload.
         *
         * @param string[] $message_array
         * @return mixed[]
         */
        function filter_SetMessages($message_array) {
            return array_merge(
                    $message_array,
                    array(
                        'added'             => __(' new locations added.'                                                   ,'csa-slp-pro'),
                        'location_exists'   => __(' pre-existing locations skipped.'                                        ,'csa-slp-pro'),
                        'not_updated'       => __(' locations did not need to be updated.'                                  ,'csa-slp-pro'),
                        'skipped'           => __(' locations were skipped due to duplicate name and address information.'  ,'csa-slp-pro'),
                        'updated'           => __(' locations were updated.'                                                ,'csa-slp-pro'),
                    )
                );
        }

        /**
         * Set the default field names if the CSV Import header is not provided.
         *
         * Default:
         * 'sl_store'   [ 0],'sl_address'  [ 1],'sl_address2'[ 2],'sl_city'       [ 3],'sl_state'[ 4],
         * 'sl_zip'     [ 5],'sl_country'  [ 6],'sl_tags'    [ 7],'sl_description'[ 8],'sl_url'  [ 9],
         * 'sl_hours'   [10],'sl_phone'    [11],'sl_email'   [12],'sl_image'      [13],'sl_fax'  [14],
         * 'sl_latitude'[15],'sl_longitude'[16],'sl_private' [17],'sl_neat_title' [18]
         *
         * @param string[] $name_array
         * @return string[]
         */
        function filter_SetDefaultFieldNames($name_array) {
            return array_merge(
                    $name_array,
                    array(
                        'sl_store','sl_address','sl_address2','sl_city','sl_state',
                        'sl_zip','sl_country','sl_tags','sl_description','sl_url',
                        'sl_hours','sl_phone','sl_email','sl_image','sl_fax',
                        'sl_latitude','sl_longitude','sl_private','sl_neat_title'
                    )
                );
        }

        /**
         * Set file meta for the import.
         *
         * Use the standard browser file upload objects $_FILES if set.
         *
         * If not set check for a remote file URL and use that.
         *
         */
        function set_FileMeta( $file_meta = NULL ) {
            $this->addon->debugMP('msg', get_class() . '::' . __FUNCTION__ );

            // Browser File Upload
            //
            if ( isset( $_FILES ) && ( ! empty( $_FILES['csvfile']['name'] ) ) ) {
                $this->file_meta = $_FILES;
                $this->addon->debugMP('pr', 'meta set from FILES' , $this->file_meta );
                return 'immediate';
            }

            $remote_file =  isset( $_REQUEST['csv_file_url'] ) ? $_REQUEST['csv_file_url'] :'';
            if ( defined('DOING_CRON') ) {
                $remote_file = $this->addon->options['csv_file_url'];
                include_once( ABSPATH . 'wp-admin/includes/file.php');
            }


            // Remote File URL
            //
            if ( ! empty( $remote_file ) ) {
                if ( $this->slplus->helper->webItemExists( $remote_file ) ) {
                    $ftp_file = file_get_contents($remote_file);

                    // File opened without any issues.
                    //
                    if ($ftp_file !== false) {
                        $local_file = wp_tempnam();

                        file_put_contents($local_file, $ftp_file);

                        $this->file_meta['csvfile'] = array(
                            'name' => 'slp_locations.csv',
                            'type' => 'text/csv',
                            'tmp_name' => $local_file,
                            'error' => (is_bool($ftp_file) ? '4' : '0'),
                            'size' => strlen($ftp_file),
                            'source' => 'direct_url',
                        );

                        // Houston, we have a problem...
                        //
                    } else {
                        $this->addon->cron_job->add_cron_status(__('Could not fetch the remote file.', 'csa-slp-pro'));

                    }

                // Remote File does not exist.
                //
                } else {
                    $this->addon->cron_job->add_cron_status(
                        sprintf(
                            __('%s does not exist.', 'csa-slp-pro') ,
                            $remote_file
                        )
                    );
                }
            }
            return NULL;
        }

        /**
         * Process the file being imported.
         *
         * cron_csv_import action takes 2 parameters:
         * param 1: the action to perform
         * param 2: the params to be sent to the action processor
         *
         * @param mixed[] $file_meta the details about the file being processed.
         */
        public function process_File( $file_meta = NULL , $mode = NULL ) {
            $this->addon->debugMP('msg', get_class() . '::' . __FUNCTION__ );

            if ( $mode === NULL ) {
                $mode = $this->addon->options['cron_import_recurrence'];
            }
            $this->addon->debugMP('msg', '', "processing mode: {$mode}" );

            // Cron Job CSV Import Processing
            //
            switch ( $mode ) {

                // Immediate Processing
                //
                case 'immediate':
                case 'none'     :
                    wp_clear_scheduled_hook('cron_csv_import',  array('import_csv', $this->file_meta ));
                    parent::process_File( $file_meta );
                    break;

                // ASAP
                //
                case 'at'  :
                    wp_schedule_single_event(
                        $this->addon->options['cron_import_timestamp'],
                        'cron_csv_import',
                        array('import_csv', $this->file_meta )
                    );
                    break;

                // Hourly, Twice Daily, Daily
                //
                default     :
                    wp_schedule_event(
                        $this->addon->options['cron_import_timestamp'],
                        $this->addon->options['cron_import_recurrence'],
                        'cron_csv_import',
                        array('import_csv', $this->file_meta )
                    );
                    break;
            }
        }
    }
}

// Dad. Explorer. Rum Lover. Code Geek. Not necessarily in that order.