<?php
/**
 * @package DevOps_and_Platforms
 * @version 1.0
 */
/*
Plugin Name: lp-date-widget
Plugin URI: http://wordpress.org/extend/plugins/devops_and_platforms/
Description: Create a date shortcode for use in the top sidebar element.  DevOps and Platforms is a modern web hosting platform that allows you to easily launch websites into the cloud.  Your pages will automatically be served from <cite>2</cite> datacenters.  For more information about doap.com or help with setting up your free site, drop a line to <a href="mailto:info@doap.com">info@doap.com</a>

Author: David Menache
Version: 1.1
Author URI: http://doap.com/
*/


// This echoes the formatted date
function formatted_date() {
        date_default_timezone_set("America/Managua");
        ini_set('default_charset', 'utf-8');
        header('Content-Type: text/html; charset=utf-8' );
        $x = time();
        $oldLocale = setlocale(LC_TIME, 'es_ES.UTF-8');
        echo ucwords(strftime("%A %d %B %Y", $x));
        //echo ucwords(strftime("%A %d %B %Y", $x));
        setlocale(LC_TIME, $oldLocale);
        add_filter('widget_text', 'do_shortcode');
}

add_shortcode('date-tag', 'formatted_date'); 

?>

