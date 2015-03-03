<?php
add_action( 'admin_enqueue_scripts', 'import_epanel_javascript' );
function import_epanel_javascript( $hook_suffix ) {
	if ( 'admin.php' == $hook_suffix && isset( $_GET['import'] ) && isset( $_GET['step'] ) && 'wordpress' == $_GET['import'] && '1' == $_GET['step'] )
		add_action( 'admin_head', 'admin_headhook' );
}

function admin_headhook(){ ?>
	<script type="text/javascript">
		jQuery(document).ready(function($){
			$("p.submit").before("<p><input type='checkbox' id='importepanel' name='importepanel' value='1' style='margin-right: 5px;'><label for='importepanel'>Replace ePanel settings with sample data values</label></p>");
		});
	</script>
<?php }

add_action('import_end','importend');
function importend(){
	global $wpdb, $shortname;

	#make custom fields image paths point to sampledata/sample_images folder
	$sample_images_postmeta = $wpdb->get_results(
		$wpdb->prepare( "SELECT meta_id, meta_value FROM $wpdb->postmeta WHERE meta_value REGEXP %s", 'http://et_sample_images.com' )
	);
	if ( $sample_images_postmeta ) {
		foreach ( $sample_images_postmeta as $postmeta ){
			$template_dir = get_template_directory_uri();
			if ( is_multisite() ){
				switch_to_blog(1);
				$main_siteurl = site_url();
				restore_current_blog();

				$template_dir = $main_siteurl . '/wp-content/themes/' . get_template();
			}
			preg_match( '/http:\/\/et_sample_images.com\/([^.]+).jpg/', $postmeta->meta_value, $matches );
			$image_path = $matches[1];

			$local_image = preg_replace( '/http:\/\/et_sample_images.com\/([^.]+).jpg/', $template_dir . '/sampledata/sample_images/$1.jpg', $postmeta->meta_value );

			$local_image = preg_replace( '/s:55:/', 's:' . strlen( $template_dir . '/sampledata/sample_images/' . $image_path . '.jpg' ) . ':', $local_image );

			$wpdb->update( $wpdb->postmeta, array( 'meta_value' => esc_url_raw( $local_image ) ), array( 'meta_id' => $postmeta->meta_id ), array( '%s' ) );
		}
	}

	if ( !isset($_POST['importepanel']) )
		return;

	$importOptions = 'YTo5MTp7czowOiIiO047czo5OiJnbG93X2xvZ28iO3M6MDoiIjtzOjEyOiJnbG93X2Zhdmljb24iO3M6MDoiIjtzOjE3OiJnbG93X2NvbG9yX3NjaGVtZSI7czo2OiJQdXJwbGUiO3M6MTU6Imdsb3dfYmxvZ19zdHlsZSI7TjtzOjE1OiJnbG93X2dyYWJfaW1hZ2UiO047czoxNzoiZ2xvd19jYXRudW1fcG9zdHMiO3M6MToiNiI7czoyMToiZ2xvd19hcmNoaXZlbnVtX3Bvc3RzIjtzOjE6IjUiO3M6MjA6Imdsb3dfc2VhcmNobnVtX3Bvc3RzIjtzOjE6IjUiO3M6MTc6Imdsb3dfdGFnbnVtX3Bvc3RzIjtzOjE6IjUiO3M6MTY6Imdsb3dfZGF0ZV9mb3JtYXQiO3M6NjoiTSBqLCBZIjtzOjE2OiJnbG93X3VzZV9leGNlcnB0IjtOO3M6MTk6Imdsb3dfaG9tZXBhZ2VfcG9zdHMiO3M6MToiNiI7czoxOToiZ2xvd19leGxjYXRzX3JlY2VudCI7TjtzOjEzOiJnbG93X2ZlYXR1cmVkIjtzOjI6Im9uIjtzOjE0OiJnbG93X2R1cGxpY2F0ZSI7TjtzOjEzOiJnbG93X2ZlYXRfY2F0IjtzOjg6IkZlYXR1cmVkIjtzOjE4OiJnbG93X3NsaWRlcl9lZmZlY3QiO3M6NDoiZmFkZSI7czoxNjoiZ2xvd19zbGlkZXJfYXV0byI7TjtzOjE2OiJnbG93X3BhdXNlX2hvdmVyIjtOO3M6MjE6Imdsb3dfc2xpZGVyX2F1dG9zcGVlZCI7czo0OiIzMDAwIjtzOjE0OiJnbG93X21lbnVwYWdlcyI7TjtzOjIxOiJnbG93X2VuYWJsZV9kcm9wZG93bnMiO3M6Mjoib24iO3M6MTQ6Imdsb3dfaG9tZV9saW5rIjtzOjI6Im9uIjtzOjE1OiJnbG93X3NvcnRfcGFnZXMiO3M6MTA6InBvc3RfdGl0bGUiO3M6MTU6Imdsb3dfb3JkZXJfcGFnZSI7czozOiJhc2MiO3M6MjI6Imdsb3dfdGllcnNfc2hvd25fcGFnZXMiO3M6MToiMyI7czoxMzoiZ2xvd19tZW51Y2F0cyI7TjtzOjMyOiJnbG93X2VuYWJsZV9kcm9wZG93bnNfY2F0ZWdvcmllcyI7czoyOiJvbiI7czoyMToiZ2xvd19jYXRlZ29yaWVzX2VtcHR5IjtzOjI6Im9uIjtzOjI3OiJnbG93X3RpZXJzX3Nob3duX2NhdGVnb3JpZXMiO3M6MToiMyI7czoxMzoiZ2xvd19zb3J0X2NhdCI7czo0OiJuYW1lIjtzOjE0OiJnbG93X29yZGVyX2NhdCI7czozOiJhc2MiO3M6MTY6Imdsb3dfc3dhcF9uYXZiYXIiO047czoyMDoiZ2xvd19kaXNhYmxlX3RvcHRpZXIiO047czoxNDoiZ2xvd19wb3N0aW5mbzIiO2E6NDp7aTowO3M6NjoiYXV0aG9yIjtpOjE7czo0OiJkYXRlIjtpOjI7czoxMDoiY2F0ZWdvcmllcyI7aTozO3M6ODoiY29tbWVudHMiO31zOjE1OiJnbG93X3RodW1ibmFpbHMiO3M6Mjoib24iO3M6MjI6Imdsb3dfc2hvd19wb3N0Y29tbWVudHMiO3M6Mjoib24iO3M6MjY6Imdsb3dfdGh1bWJuYWlsX3dpZHRoX3Bvc3RzIjtzOjM6IjE3MiI7czoyNzoiZ2xvd190aHVtYm5haWxfaGVpZ2h0X3Bvc3RzIjtzOjM6IjE3MiI7czoyMDoiZ2xvd19wYWdlX3RodW1ibmFpbHMiO047czoyMzoiZ2xvd19zaG93X3BhZ2VzY29tbWVudHMiO047czoyNjoiZ2xvd190aHVtYm5haWxfd2lkdGhfcGFnZXMiO3M6MzoiMTcyIjtzOjI3OiJnbG93X3RodW1ibmFpbF9oZWlnaHRfcGFnZXMiO3M6MzoiMTcyIjtzOjE0OiJnbG93X3Bvc3RpbmZvMSI7YTozOntpOjA7czo2OiJhdXRob3IiO2k6MTtzOjQ6ImRhdGUiO2k6MjtzOjg6ImNvbW1lbnRzIjt9czoxODoiZ2xvd19jdXN0b21fY29sb3JzIjtOO3M6MTQ6Imdsb3dfY2hpbGRfY3NzIjtOO3M6MTc6Imdsb3dfY2hpbGRfY3NzdXJsIjtzOjA6IiI7czoxODoiZ2xvd19jb2xvcl9iZ2NvbG9yIjtzOjA6IiI7czoxOToiZ2xvd19jb2xvcl9tYWluZm9udCI7czowOiIiO3M6MTk6Imdsb3dfY29sb3JfbWFpbmxpbmsiO3M6MDoiIjtzOjE5OiJnbG93X2NvbG9yX3BhZ2VsaW5rIjtzOjA6IiI7czoxOToiZ2xvd19jb2xvcl9jYXRzbGluayI7czowOiIiO3M6MjU6Imdsb3dfY29sb3Jfc2lkZWJhcl90aXRsZXMiO3M6MDoiIjtzOjI3OiJnbG93X2NvbG9yX3NpZGViYXJfYmd0aXRsZXMiO3M6MDoiIjtzOjIzOiJnbG93X2NvbG9yX3NpZGViYXJfbGluayI7czowOiIiO3M6MTY6Imdsb3dfY29sb3JfcmVwbHkiO3M6MDoiIjtzOjE4OiJnbG93X2NvbG9yX2hlYWRpbmciO3M6MDoiIjtzOjE5OiJnbG93X3Nlb19ob21lX3RpdGxlIjtOO3M6MjU6Imdsb3dfc2VvX2hvbWVfZGVzY3JpcHRpb24iO047czoyMjoiZ2xvd19zZW9faG9tZV9rZXl3b3JkcyI7TjtzOjIzOiJnbG93X3Nlb19ob21lX2Nhbm9uaWNhbCI7TjtzOjIzOiJnbG93X3Nlb19ob21lX3RpdGxldGV4dCI7czowOiIiO3M6Mjk6Imdsb3dfc2VvX2hvbWVfZGVzY3JpcHRpb250ZXh0IjtzOjA6IiI7czoyNjoiZ2xvd19zZW9faG9tZV9rZXl3b3Jkc3RleHQiO3M6MDoiIjtzOjE4OiJnbG93X3Nlb19ob21lX3R5cGUiO3M6Mjc6IkJsb2dOYW1lIHwgQmxvZyBkZXNjcmlwdGlvbiI7czoyMjoiZ2xvd19zZW9faG9tZV9zZXBhcmF0ZSI7czozOiIgfCAiO3M6MjE6Imdsb3dfc2VvX3NpbmdsZV90aXRsZSI7TjtzOjI3OiJnbG93X3Nlb19zaW5nbGVfZGVzY3JpcHRpb24iO047czoyNDoiZ2xvd19zZW9fc2luZ2xlX2tleXdvcmRzIjtOO3M6MjU6Imdsb3dfc2VvX3NpbmdsZV9jYW5vbmljYWwiO047czoyNzoiZ2xvd19zZW9fc2luZ2xlX2ZpZWxkX3RpdGxlIjtzOjk6InNlb190aXRsZSI7czozMzoiZ2xvd19zZW9fc2luZ2xlX2ZpZWxkX2Rlc2NyaXB0aW9uIjtzOjE1OiJzZW9fZGVzY3JpcHRpb24iO3M6MzA6Imdsb3dfc2VvX3NpbmdsZV9maWVsZF9rZXl3b3JkcyI7czoxMjoic2VvX2tleXdvcmRzIjtzOjIwOiJnbG93X3Nlb19zaW5nbGVfdHlwZSI7czoyMToiUG9zdCB0aXRsZSB8IEJsb2dOYW1lIjtzOjI0OiJnbG93X3Nlb19zaW5nbGVfc2VwYXJhdGUiO3M6MzoiIHwgIjtzOjI0OiJnbG93X3Nlb19pbmRleF9jYW5vbmljYWwiO047czoyNjoiZ2xvd19zZW9faW5kZXhfZGVzY3JpcHRpb24iO047czoxOToiZ2xvd19zZW9faW5kZXhfdHlwZSI7czoyNDoiQ2F0ZWdvcnkgbmFtZSB8IEJsb2dOYW1lIjtzOjIzOiJnbG93X3Nlb19pbmRleF9zZXBhcmF0ZSI7czozOiIgfCAiO3M6Mjg6Imdsb3dfaW50ZWdyYXRlX2hlYWRlcl9lbmFibGUiO3M6Mjoib24iO3M6MjY6Imdsb3dfaW50ZWdyYXRlX2JvZHlfZW5hYmxlIjtzOjI6Im9uIjtzOjMxOiJnbG93X2ludGVncmF0ZV9zaW5nbGV0b3BfZW5hYmxlIjtzOjI6Im9uIjtzOjM0OiJnbG93X2ludGVncmF0ZV9zaW5nbGVib3R0b21fZW5hYmxlIjtzOjI6Im9uIjtzOjIxOiJnbG93X2ludGVncmF0aW9uX2hlYWQiO3M6MDoiIjtzOjIxOiJnbG93X2ludGVncmF0aW9uX2JvZHkiO3M6MDoiIjtzOjI3OiJnbG93X2ludGVncmF0aW9uX3NpbmdsZV90b3AiO3M6MDoiIjtzOjMwOiJnbG93X2ludGVncmF0aW9uX3NpbmdsZV9ib3R0b20iO3M6MDoiIjtzOjE1OiJnbG93XzQ2OF9lbmFibGUiO047czoxNDoiZ2xvd180NjhfaW1hZ2UiO3M6MDoiIjtzOjEyOiJnbG93XzQ2OF91cmwiO3M6MDoiIjt9';

	/*global $options;

	foreach ($options as $value) {
		if( isset( $value['id'] ) ) {
			update_option( $value['id'], $value['std'] );
		}
	}*/

	$importedOptions = unserialize(base64_decode($importOptions));

	foreach ($importedOptions as $key=>$value) {
		if ($value != '') update_option( $key, $value );
	}
} ?>