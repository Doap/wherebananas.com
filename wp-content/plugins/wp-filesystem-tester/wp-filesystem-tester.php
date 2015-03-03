<?php
/*
Plugin Name: Wp-Filesystem Tester
Version: 1.8
Plugin URI: http://dd32.id.au/
Description: Plugin to spew debug info about The Filesystem wrapper used by the Plugin updater.
Author: Dion Hulse
Author URI: http://dd32.id.au/
*/

add_action('admin_menu','wfst_init');
function wfst_init(){
	add_menu_page('Wp FS Tester', 'Wp FS Tester', 'administrator', __FILE__, 'wsts_page' );
}

function wpfs_get_base_dir($base = '.', $echo = false){
	global $wp_filesystem;

	$abspath = str_replace('\\','/',ABSPATH); //windows: Straighten up the paths..
	if( strpos($abspath, ':') ){ //Windows, Strip out the driveletter
		if( preg_match("|.{1}\:(.+)|i", $abspath, $mat) )
			$abspath = $mat[1];
	}

	if( empty( $base ) || '.' == $base ) $base = $wp_filesystem->cwd();
	if( empty( $base ) ) $base = '/';
	if( '/' != substr($base, -1) ) $base .= '/';

	if($echo) printf( __('Changing to %s') . '<br/>', $base );
	if( false === $wp_filesystem->chdir($base) )
		return false;

	if( $wp_filesystem->exists($base . 'wp-settings.php') ){
		if($echo) printf( __('Found %s'), $base . 'wp-settings.php<br/>' );
		$wp_filesystem->wp_base = $base;
		return $wp_filesystem->wp_base;
	}

	if( strpos($abspath, $base) > 0)
		$arrPath = split('/',substr($abspath,strpos($abspath, $base)));
	else
		$arrPath = split('/',$abspath);

	for($i = 0; $i <= count($arrPath); $i++)
		if( $arrPath[ $i ] == '' ) unset( $arrPath[ $i ] );

	foreach($arrPath as $key=>$folder){
		if( $wp_filesystem->is_dir($base . $folder) ){
			if($echo) echo sprintf( __('Found %s'),  $folder ) . ' ' . sprintf( __('Changing to %s') . '<br/>', $base . $folder . '/' );
			return $wp_filesystem->find_base_dir($base .  $folder . '/',$echo);
		}
	}

	if( $base == '/' )
		return false;
	//If we get this far, somethings gone wrong, change to / and restart the process.
	return $wp_filesystem->find_base_dir('/',$echo);
}

function wsts_page(){
	include_once ABSPATH . '/wp-admin/includes/file.php';
	include_once ABSPATH . '/wp-admin/update.php';

	$credentials = request_filesystem_credentials('admin.php?page=' . plugin_basename(__FILE__) );
	if( ! $credentials )
		return;
	WP_Filesystem($credentials);
	global $wp_filesystem;
	
?>
<div class="wrap">
<table class="form-table">
	<tr>
		<th>Connection Method</th>
		<td><?php echo get_filesystem_method() ?></td>
	</tr>
	<tr>
		<th>ABSPATH</th>
		<td><?php echo ABSPATH ?></td>
	</tr>
	<tr>
		<th>PLUGINDIR</th>
		<td><?php echo PLUGINDIR ?></td>
	</tr>
	<tr>
		<th>FS Errors</th>
		<td><?php 
			if( empty($wp_filesystem->errors->errors) )
				echo "None";
			else {
				echo "<pre>";
				print_r($wp_filesystem->errors);
				echo "</pre>";
			}
		?></td>
	</tr>
	<tr>
		<th>FS CWD</th>
		<td><?php $cwd = $wp_filesystem->cwd(); echo $cwd; ?></td>
	</tr>
	<tr>
		<th>FS WordPress Locator</th>
		<td><?php $base = $wp_filesystem->get_base_dir('.', true) ?></td>
	<tr>
		<th>FS WordPress Location</th>
		<td><?php echo $base ?></td>
	</tr>
	<?php if( 'direct' != get_filesystem_method() ){ ?>
	<tr style="background-color:#0099FF;">
		<th>FS0 WordPress Locator (Old code)</th>
		<td><?php
			$wp_filesystem->chdir($cwd);
			$base0 = wpfs_get_base_dir('.', true) ?></td>
	<tr style="background-color:#0099FF;">
		<th>FS0 WordPress Location (Old code)</th>
		<td><?php echo $base0 ?></td>
	</tr>
	<?php
	if( ! $base && ! $base0 ){
		echo "</table>Tests Stopped; <strong>Error:</strong> WordPress could not be located";
		?>
		<tr>
			<th>Files in folder</th>
			<td><pre><?php
			$files = $wp_filesystem->dirlist($cwd);
			foreach((array)$files as $file)	
				printf("%s\t%s\t%s\n", $file['name'], $file['type'], $file['perms']);
			?></pre></td>
		</tr>
		<?php
		return;
	}

	if( $base != $base0 ){
		echo "</table><div class='error'><p><strong>NOTICE:</strong> WordPress locations different. Which is the correct one? </p>
				<p>Please email me at <a href='mailto:wordpress@dd32.id.au'>wordpress@dd32.id.au</a> with the output from this page, and mention which is the correct path, Thanks :)</p></div>
			<table class='form-table'>";
		?>
		 	<tr>
				<th>Files in folder</th>
				<td><pre><?php
				$files = $wp_filesystem->dirlist($cwd);
				foreach((array)$files as $file)	
					printf("%s\t%s\t%s\n", $file['name'], $file['type'], $file['perms']);
				?></pre></td>
			</tr>
	<?php
	}
	if( ! $base && $base0 )
		$base = $base0;
	}//End if not direct
	 ?>
	<tr>
		<th>Plugin location: </th>
		<td><?php echo __FILE__ ?> (Local)<br />
			<?php echo $base . PLUGINDIR . '/' . plugin_basename(__FILE__) ?> (FTP)</td>
	</tr>
	<tr>
		<th>Plugin Locations</th>
		<td><table><?php
			$plugins = array('hello.php', 'akismet/akismet.php');
			foreach($plugins as $plugin){
				$plugin_dir = dirname($base . PLUGINDIR . "/$plugin");
				$plugin_dir = trailingslashit($plugin_dir);
				
				echo "<tr>";
				echo "<td>", $plugin, "</td>";
				echo "<td>", $plugin_dir, "</td>";
					
				if( strpos($plugin, '/') && $plugin_dir != $base . PLUGINDIR . '/' )
					echo "<td>", "Delete entire folder: $plugin_dir" , "</td>";
				else
					echo "<td>", "Delete file: " . $base . PLUGINDIR . "/$plugin" , "</td>";
				
				echo "</tr>";
			}
		?></table></td>
	</tr>
	<tr>
		<th>File IO Errors</th>
		<td><?php
			$no_conflict_plugin = 'wp-content/plugins/super-long-name-not-to-conflict.php';
			$md5 = md5(__FILE__ . time());
			$newfile = $base . $no_conflict_plugin;
			if( ! $wp_filesystem->put_contents($newfile, $md5, 0666) ){
				echo "<strong>Error: Could not create file <em>$newfile</em> on server</strong>";
			} elseif( ! file_exists(ABSPATH . $no_conflict_plugin) || $md5 != file_get_contents(ABSPATH . $no_conflict_plugin) ){
				echo "<strong>Error: Plugin file created, However, Not in FS(Or contents different)</strong>";
			} else {
				//File created, Lets try to delete it now.
				if( ! $wp_filesystem->delete( $newfile ) ){
					echo "<strong>Error: Could not delete test file $newfile</strong>";
				} else {
					echo "All File IO tests passed.<br>
						Created <em>$newfile</em><br>
						Verified conents<br>
						Deleted <em>$newfile</em>";
				}
			}
			
		?></td>
	</tr>
	<tr>
		<th>Downloading a zip</th>
		<td><?php
			$package = 'http://downloads.wordpress.org/plugin/akismet.zip';
			$file = download_url($package);
			echo "Downloading <em>$package</em>... ";
			if( ! $file )
				echo "Failed";
			else {
				echo "Suceeded";
			}
		?></td>
	</tr>
	<?php
	if( ! $file ){
		echo "</table>Tests Stopped;";
		return;
	}
	?>
	<tr>
		<th>Extracting Zip</th>
		<td>
			<?php
				$error = false;
				
				require_once(ABSPATH . 'wp-admin/includes/class-pclzip.php');

				$archive = new PclZip($file);
				
				// Is the archive valid?
				if ( false == ($archive_files = $archive->extract(PCLZIP_OPT_EXTRACT_AS_STRING)) ){
					echo "Error: " . $archive->errorInfo(true);
					$error = true;
				} else if ( 0 == count($archive_files) ){
					echo 'Error: Empty archive';
					$error = true;
				} else {
					echo "Suceeded";
				}
				unlink($file);
			?>
		</td>
	</tr>
	<?php
	if( $error ){
		echo "</table>Tests Stopped;";
		return;
	}
	?>
	<tr>
		<th>Zip Contents</th>
		<td><pre><?php 
			foreach($archive_files as $file){
				if( strpos($file['filename'], '.php') > -1 || strpos($file['filename'], '.txt') > -1){
					$line = wp_specialchars(substr($file['content'], 0, 50) . '...');
				} else {
					$line = 'binary';
				}
				printf("%s\t%s\t%s\t%s\n", $file['filename'], $file['size'], $file['status'],  $line);
			}
		 ?></pre></td>
	</tr>
</table>

</div>
<?php
}
?>