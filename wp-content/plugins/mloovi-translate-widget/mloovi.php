<?php
/**
 * Plugin Name: Mloovi Translate Widget
 * Plugin URI: http://mloovi.com/pages/wordpress-plugin
 * Description: Translate your blog into 52 languages instantly!
 * Version: 0.2.4
 * Author: Mike Robinson
 * Author URI: http://www.digitalegg.net
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * Add function to widgets_init that'll load our widget.
 * @since 0.1
 */
add_action( 'widgets_init', 'mloovi_load_widgets' );

/**
 * Register our widget.
 * 'Example_Widget' is the widget class used below.
 *
 * @since 0.1
 */
function mloovi_load_widgets() {
	register_widget( 'Mloovi_Widget' );
}

/**
 * Example Widget class.
 * This class handles everything that needs to be handled with the widget:
 * the settings, form, display, and update.  Nice!
 *
 * @since 0.1
 */
class Mloovi_Widget extends WP_Widget {

	/**
	 * Widget setup.
	 */
	function Mloovi_Widget() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'mloovi', 'description' => __('Translate your blog into 52 languages instantly!', 'mloovi') );

		/* Widget control settings. */
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'mloovi-widget' );
	
		/* Create the widget. */
		$this->WP_Widget( 'mloovi-widget', __('Mloovi Widget', 'mloovi'), $widget_ops, $control_ops );
		$language_url = "http://mloovi.com/api/languages";
		
		/* Get Languages from Mloovi.com */
		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_URL, $language_url); 
		curl_setopt($ch, CURLOPT_HEADER, 0); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
		curl_setopt($ch, CURLOPT_TIMEOUT, 2);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7.5) Gecko/20041107 Firefox/1.0'); 
		$languages = curl_exec($ch); 
		curl_close($ch);	
   		$this->languages = unserialize( $languages );
   		wp_enqueue_script("jquery");
	}

	/**
	 * How to display the widget on the screen.
	 */
	function widget( $args, $instance ) {
		extract( $args );

		/* Our variables from the widget settings. */
		$title = apply_filters('widget_title', $instance['title'] );
		$feed_url = $instance['feed_url'];

		/* Before widget (defined by themes). */
		echo $before_widget;
?>
<script type="text/javascript">
var $j = jQuery.noConflict();

	$j(function(){
		$j('#mloovi-more-languages').click(function(){
			if ( $j('#mloovi-languages-hidden').is(':visible') ) {
				$j('#mloovi-more-languages').text('more');
				$j('#mloovi-languages-hidden').slideUp('slow');
			} else {
				$j('#mloovi-more-languages').text('less');
				$j('#mloovi-languages-hidden').slideDown('slow');
			}
			return false;
		});
	});
</script>

<?php 
		
		/* Display the widget title if one was input (before and after defined by themes). */
		if ( $title )
			echo $before_title . $title . $after_title;

		/* Display name from widget settings if one was input. */
		echo '<div id="mloovi-languages-show"><ul>';			
		$x=1;
		foreach( $this->languages AS $key => $value ) {
			if ( $instance[$key] == "on" ) {
				$x++;
				echo "<li><a href=\"http://{$key}.mloovi.com/{$feed_url}\">{$value}</a></li>";
			}
		}
		echo '</ul>';
		if ( count($this->languages) > $x ) {
			echo '<div style="display:block;">';
			echo '<a href="#" id="mloovi-more-languages">more</a></div>';
			echo '</div>';
		}
		echo '<div id="mloovi-languages-hidden" style="display:none;"><ul>';
		if ( !empty( $this->languages ) ) {
			foreach( $this->languages AS $key => $value ) {
				if ( $instance[$key] != "on" ) {
					echo "<li><a href=\"http://{$key}.mloovi.com/{$feed_url}\">{$value}</a></li>";
				}
			}
		}
		echo '</ul></div>';
		/* After widget (defined by themes). */
		echo $after_widget;
	}

	/**
	 * Update the widget settings.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['feed_url'] = strip_tags( $new_instance['feed_url'] );
		if ( !empty( $this->languages ) ) {
			foreach( $this->languages AS $key => $value ) {
				$instance[$key] = $new_instance[$key]; 	
			}
		}
		$ch = curl_init();
		$url = "http://mloovi.com/mloovi/translate";
		$rss_feed = get_bloginfo( 'rss2_url' );
		$params = "data[to]=cs&data[url]={$rss_feed}&data[short_tag]={$new_instance['feed_url']}";
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		if ( $params ) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params );
		}
		$body = curl_exec($ch);
		curl_close($ch);		
		return $instance;
	}

	/**
	 * Displays the widget settings controls on the widget panel.
	 * Make use of the get_field_id() and get_field_name() function
	 * when creating your form elements. This handles the confusing stuff.
	 */
	function form( $instance ) {

		/* Set up some default widget settings. */
		$wp_url = str_replace("http://","",get_bloginfo( 'wpurl' ));
		$wp_url = str_replace("/","_",$wp_url);
		$defaults = array( 'title' => __('Mloovi', 'mloovi'),'feed_url' => $wp_url );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
		<script type="text/javascript">
			jQuery(function(){
				jQuery("#selectall").click(function() {
					var checked_status = this.checked;
					alert("clicked");
					jQuery(".mloovilangcheckbox").each(function()  {
						this.checked = checked_status;
					});
					return false;
				});
			});
		</script>
		<!-- Widget Title: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'hybrid'); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
		</p>
		<p>
			<!--  <label for="<?php echo $this->get_field_id( 'feed_url' ); ?>"><?php _e('Mloovi Custom URL:', 'mloovi'); ?></label> -->
			<input id="<?php echo $this->get_field_id( 'feed_url' ); ?>" name="<?php echo $this->get_field_name( 'feed_url' ); ?>" value="<?php echo $instance['feed_url']; ?>" style="width:100%;" type="hidden" />
		</p>
		<!-- <a href="#" id="selectall" style="clear:both;">Select All</a> -->
		<p>Which languages do you want to show?</p>
		<div style="display:block;overflow:auto;height:300px;">
		<?php if ( !empty( $this->languages ) ) { ?>
		<?php foreach( $this->languages AS $key => $value ) { ?>
			<?php if ( $key != substr(get_bloginfo('language'),0,2)) { ?>
			<div style="width:50%;float:left">
			<input class="checkbox mloovilangcheckbox" type="checkbox" name="<?php echo $this->get_field_name( $key ); ?>" id="<?php echo $this->get_field_id( $key ); ?>" <?php echo $instance[$key] == "on" ? 'checked="checked"' : ''; ?> />
			<label for="<?php echo $this->get_field_id( $key ); ?>"><?php echo _e($value); ?></label>
			</div>
			<?php } ?>
		<?php } ?>
		<?php } ?>
		</div>
	<?php
	}
}

?>