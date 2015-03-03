<?php
/**
 * Youtube Feeder Widget
 * @author awensley
 * @package YoutubeFeeder
 * @subpackage Widgets
 */
class YoutubeFeederWidget extends WP_Widget
{
	/**
	 * Constructor. Register widget with WordPress.
	 *
	 * @return YoutubeFeederWidget
	 */
	public function __construct()
	{
		parent::__construct(
			'youtubefeederwidget', // Base ID
			'YoutubeFeeder', // Name
			array('description' => __('Embed a Youtube Feed in your sidebar.', 'YoutubeFeeder')) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 * @param array $args Widget arguments.
	 * @param array $instance Saved values from database.
	 * @return void
	 */
	public function widget($args, $instance)
	{
		extract($args);
		$title = apply_filters('widget_title', $instance['widgettitle']);
		echo $before_widget;
		if(!empty($title))
		{
			echo $before_title . $title . $after_title;
		}
		$yf = getYfInstance();
		echo $yf->youtubeFeederShortcode($instance); // Do the Youtube Feeder Work
		echo $after_widget;
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously-saved values from database.
	 * @return array Updated safe values to be saved.
	 */
	public function update($new_instance, $old_instance)
	{
		$yf = getYfInstance();
		$instance = shortcode_atts($yf->getSavedDefaults(), $new_instance);
		$instance['widgettitle'] = strip_tags($new_instance['widgettitle']);
		$instance['cache'] = ($new_instance['cachenum'] == 0 ? '0' : $new_instance['cachenum'] . ' '
			. (in_array($new_instance['cacheunit'], array('minutes', 'hours', 'weeks', 'months', 'days'))
				? $new_instance['cacheunit']
				: 'days'));
		$instance['feedid'] = (!empty($new_instance['standardfeedregion'])
			? $new_instance['standardfeedregion'] . '/' . $new_instance['feedid']
			: $new_instance['feedid']);
		return $instance;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 * @param array $instance Previously-saved values from database.
	 * @return void
	 */
	public function form($instance)
	{
		$yf = getYfInstance();
		$settings = shortcode_atts($yf->getSavedDefaults(), $instance);
		if(isset($instance['widgettitle']))
		{
			$title = $instance['widgettitle'];
		}
		else
		{
			$title = '';
		}
		?><div class="yfSettingsForm">
			<p>
				<label><?php
					_e('Title:');
					?><input class="widefat" name="<?php echo $this->get_field_name('widgettitle');
						?>" type="text" value="<?php echo esc_attr($title); ?>" />
				</label>
			</p>
			<hr/>
			<p style="font-weight:bold;">Feed Options</p>
			<p>
				<label><?php
					_e('Feed Type:');
					?><select class="widefat feedtype" name="<?php echo $this->get_field_name('feedtype');
						?>"><?php
						foreach(array(
								'uploads' => 'User Uploads',
								'favorites' => 'User Favorites',
								'subscriptions' => 'User Subscriptions',
								'playlist' => 'User Playlist',
								'standard' => 'Standard Feed',
								'category' => 'Category / Tag',
								'search' => 'Search'
							) as $k => $v)
						{
							?><option value="<?php echo $k ?>"<?php echo ($settings['feedtype'] == $k ? ' selected' : ''); ?>><?php
								echo $v;
							?></option><?php
						}
					?></select>
				</label>
			</p>
			<p class="feedtypeid feedtypeid-id">
				<label><?php
					_e('Feed ID:');
					?><input class="widefat feedid" type="text" name="<?php echo $this->get_field_name('feedid');
						?>" value="<?php echo $settings['feedid']; ?>" size="30" /><br/>
					<span class="feedtypeiddesc feedtypeiddesc-username">The Youtube Username</span>
					<span class="feedtypeiddesc feedtypeiddesc-playlist">
						The Playlist ID.<br/><br/>
						To find your Playlist ID, play a video from the playlist and copy the part of the URL after
						<mark>&amp;list=</mark> and before the next <mark>&amp;</mark>.<br/>
						<b>Example:</b><br/>
						If your URL is:
						<code>http://www.youtube.com/watch?v=Uolz7V12evc<mark>&amp;list=</mark><span style="text-decoration:underline;"><b>PLF125407272F3C1A4</b></span><mark>&amp;</mark>index=1</code><br/>
						Copy <span style="text-decoration:underline;"><b>PLF125407272F3C1A4</b></span> into the box above.
					</span>
					<span class="feedtypeiddesc feedtypeiddesc-category">Enter Category / Tag</span>
					<span class="feedtypeiddesc feedtypeiddesc-search">Enter Search Terms</span>
				</label>
			</p>
			<p class="feedtypeid feedtypeid-standard">
				<label><?php
					_e('Feed:');
					?><select class="widefat feedidstandard" name="<?php echo $this->get_field_name('feedid');
						?>"><?php
						$standardFeeds = array(
								'top_rated' => 'Top Rated',
								'top_favorites' => 'Top Favorites',
								'most_viewed' => 'Most Viewed',
								'most_shared' => 'Most Shared',
								'most_popular' => 'Most Popular',
								'most_recent' => 'Most Recent',
								'most_discussed' => 'Most Discussed',
								'most_responded' => 'Most Responded',
								'recently_featured' => 'Recently Featured',
								'on_the_web' => 'Trending Videos'
							);
						foreach($standardFeeds as $k => $v)
						{
							?><option value="<?php echo $k;?>"<?php
								echo (strstr($settings['feedid'], $k) !== false ? ' selected' : ''); ?>><?php
								echo $v;
							?></option><?php
						}
					?></select>
				</label>
			</p>
			<p class="feedtypeid feedtypeid-standard">
				<label><?php
					_e('Region (optional):');
					?><select class="widefat standardfeedregion" name="<?php echo $this->get_field_name('standardfeedregion');
						?>"><option></option><?php
						$regions = array(
								'AR' => 'Argentina',
								'AU' => 'Australia',
								'BR' => 'Brazil',
								'CA' => 'Canada',
								'CZ' => 'Czech Republic',
								'FR' => 'France',
								'DE' => 'Germany',
								'GB' => 'Great Britain',
								'HK' => 'Hong Kong',
								'HU' => 'Hungary',
								'IN' => 'India',
								'IE' => 'Ireland',
								'IL' => 'Israel',
								'IT' => 'Italy',
								'JP' => 'Japan',
								'MX' => 'Mexico',
								'NL' => 'Netherlands',
								'NZ' => 'New Zealand',
								'PL' => 'Poland',
								'RU' => 'Russia',
								'ZA' => 'South Africa',
								'KR' => 'South Korea',
								'ES' => 'Spain',
								'SE' => 'Sweden',
								'TW' => 'Taiwan',
								'US' => 'United States'
							);
						foreach($regions as $k => $v)
						{
							?><option value="<?php echo $k;?>"<?php
								echo (strstr($settings['feedid'], $k) !== false ? ' selected' : ''); ?>><?php
								echo $v;
							?></option><?php
						}
					?></select>
				</label>
			</p>
			<p>
				<label><?php
					_e('Order:');
					?><select class="widefat" name="<?php echo $this->get_field_name('orderby'); ?>"><?php
						foreach(array(
								'relevance' => 'Relevance',
								'published' => 'Date Published',
								'viewCount' => 'View Count',
								'commentCount' => 'Comment Count',
								'position' => 'Position',
								'duration' => 'Duration',
								'title' => 'Title',
								'rating' => 'Rating'
							) as $k => $v)
						{
							?><option value="<?php echo $k; ?>"<?php
								$so = $yf->getSpecificOrders();
								if(array_key_exists($k, $so))
								{
									?> class="orderby-<?php echo $so[$k]; ?>"<?php
								}
								echo ($settings['orderby'] == $k ? ' selected' : ''); ?>><?php
								echo $v;
							?></option><?php
						}
					?></select>
				</label>
			</p>
			<hr/>
			<p style="font-weight:bold;">Display Options</p>
			<p>
				<label><?php
					_e('Style:');
					?><select class="widefat" name="<?php echo $this->get_field_name('style'); ?>"><?php
						foreach(array('minimal' => 'Minimal', 'list' => 'Simple List') as $k => $v)
						{
							?><option value="<?php echo $k;?>"<?php
								echo ($settings['style'] == $k ? ' selected' : ''); ?>><?php
								echo $v;
							?></option><?php
						}
					?></select>
				</label>
			</p>
			<p>
				<label><?php
					_e('Limit:');
					?><input type="text" class="widefat" name="<?php echo $this->get_field_name('limit'); ?>" value="<?php echo $settings['limit']; ?>" size="5"/>
				</label>
			</p>
			<p><?php
				_e('Size:');
				?><br/>
				<label><?php _e('Width:'); ?> <input type="text" name="<?php echo $this->get_field_name('width'); ?>" value="<?php echo $settings['width']; ?>" size="4"/></label>
				x <label><?php _e('Height:'); ?> <input type="text" name="<?php echo $this->get_field_name('height'); ?>" value="<?php echo $settings['height']; ?>" size="4"/></label>
			</p>
			<p>
				<label><?php
					_e('Show Title:');
					?><select class="widefat" name="<?php echo $this->get_field_name('title'); ?>"><?php
						foreach(array('1' => 'Yes', '0' => 'No') as $k => $v)
						{
							?><option value="<?php echo $k;?>"<?php
								echo ($settings['title'] == $k ? ' selected' : ''); ?>><?php
								echo $v;
							?></option><?php
						}
					?></select>
				</label>
			</p>
			<p>
				<label><?php
					_e('Date Format:');
					?><input type="text" class="widefat" name="<?php echo $this->get_field_name('dateformat'); ?>" value="<?php
						echo $settings['dateformat']; ?>" size="20"/>
					<strong><?php echo date($settings['dateformat']); ?></strong>
					&lt;--Current format ('<?php echo $settings['dateformat']; ?>')
				</label>
			</p>
			<hr/>
			<p style="font-weight:bold;">Playback Options</p>
			<p>
				<label><?php
					_e('Autoplay:');
					?><select class="widefat" name="<?php echo $this->get_field_name('autoplay'); ?>"><?php
						foreach(array(
								'0' => 'No',
								'1' => 'Yes'
							) as $k => $v)
						{
							?><option value="<?php echo $k; ?>"<?php
								echo ($settings['autoplay'] == $k ? ' selected' : ''); ?>><?php
								echo $v;
							?></option><?php
						}
					?></select>
				</label>
			</p>
			<p>
				<label><?php
					_e('Loop:');
					?><select class="widefat" name="<?php echo $this->get_field_name('loop'); ?>"><?php
						foreach(array(
								'0' => 'No',
								'1' => 'Yes'
							) as $k => $v)
						{
							?><option value="<?php echo $k; ?>"<?php
								echo ($settings['loop'] == $k ? ' selected' : ''); ?>><?php
								echo $v;
							?></option><?php
						}
					?></select>
				</label>
			</p>
			<p>
				<label><?php
					_e('Related Videos:');
					?><select class="widefat" name="<?php echo $this->get_field_name('rel'); ?>"><?php
						foreach(array(
								'1' => 'Display',
								'0' => 'Don\'t Display'
							) as $k => $v)
						{
							?><option value="<?php echo $k; ?>"<?php
								echo ($settings['rel'] == $k ? ' selected' : ''); ?>><?php
								echo $v;
							?></option><?php
						}
					?></select>
				</label>
			</p>
			<p>
				<label><?php
					_e('Video Information:');
					?><select class="widefat" name="<?php echo $this->get_field_name('showinfo'); ?>"><?php
						foreach(array(
								'1' => 'Display',
								'0' => 'Don\'t Display'
							) as $k => $v)
						{
							?><option value="<?php echo $k; ?>"<?php
								echo ($settings['showinfo'] == $k ? ' selected' : ''); ?>><?php
								echo $v;
							?></option><?php
						}
					?></select>
				</label>
			</p>
			<p>
				<label><?php
					_e('Controls:');
					?><select class="widefat" name="<?php echo $this->get_field_name('controls'); ?>"><?php
						foreach(array(
								'1' => 'Display',
								'0' => 'Don\'t Display'
							) as $k => $v)
						{
							?><option value="<?php echo $k; ?>"<?php
								echo ($settings['controls'] == $k ? ' selected' : ''); ?>><?php
								echo $v;
							?></option><?php
						}
					?></select>
				</label>
			</p>
			<p>
				<label><?php
					_e('Keyboard Controls:');
					?><select class="widefat" name="<?php echo $this->get_field_name('disablekb'); ?>"><?php
						foreach(array(
								'0' => 'Enabled',
								'1' => 'Disabled'
							) as $k => $v)
						{
							?><option value="<?php echo $k; ?>"<?php
								echo ($settings['disablekb'] == $k ? ' selected' : ''); ?>><?php
								echo $v;
							?></option><?php
						}
					?></select>
				</label>
			</p>
			<p>
				<label><?php
					_e('FullScreen Button:');
					?><select class="widefat" name="<?php echo $this->get_field_name('fs'); ?>"><?php
						foreach(array(
								'1' => 'Display',
								'0' => 'Don\'t Display'
							) as $k => $v)
						{
							?><option value="<?php echo $k; ?>"<?php
								echo ($settings['fs'] == $k ? ' selected' : ''); ?>><?php
								echo $v;
							?></option><?php
						}
					?></select>
				</label>
			</p>
			<p>
				<label><?php
					_e('Control Bar Theme:');
					?><select class="widefat" name="<?php echo $this->get_field_name('theme'); ?>"><?php
						foreach(array(
								'dark' => 'Dark',
								'light' => 'Light'
							) as $k => $v)
						{
							?><option value="<?php echo $k; ?>"<?php
								echo ($settings['theme'] == $k ? ' selected' : ''); ?>><?php
								echo $v;
							?></option><?php
						}
					?></select>
				</label>
			</p>
			<p>
				<label><?php
					_e('Autohide:');
					?><select class="widefat" name="<?php echo $this->get_field_name('autohide'); ?>"><?php
						foreach(array(
								'2' => 'Progress Bar Only',
								'1' => 'Progress Bar and Controls',
								'0' => 'Disabled'
							) as $k => $v)
						{
							?><option value="<?php echo $k; ?>"<?php
								echo ($settings['autohide'] == $k ? ' selected' : ''); ?>><?php
								echo $v;
							?></option><?php
						}
					?></select>
				</label>
			</p>
			<p>
				<label><?php
					_e('Progress Bar Color:');
					?><select class="widefat" name="<?php echo $this->get_field_name('color'); ?>"><?php
						foreach(array(
								'red' => 'Red',
								'white' => 'White'
							) as $k => $v)
						{
							?><option value="<?php echo $k; ?>"<?php
								echo ($settings['color'] == $k ? ' selected' : ''); ?>><?php
								echo $v;
							?></option><?php
						}
					?></select>
				</label>
			</p>
			<p>
				<label><?php
					_e('Closed Captions:');
					?><select class="widefat" name="<?php echo $this->get_field_name('cc_load_policy'); ?>"><?php
						foreach(array(
								'0' => 'User Preference',
								'1' => 'Force On'
							) as $k => $v)
						{
							?><option value="<?php echo $k; ?>"<?php
								echo ($settings['cc_load_policy'] == $k ? ' selected' : ''); ?>><?php
								echo $v;
							?></option><?php
						}
					?></select>
				</label>
			</p>
			<p>
				<label><?php
					_e('Video Annotations:');
					?><select class="widefat" name="<?php echo $this->get_field_name('iv_load_policy'); ?>"><?php
						foreach(array(
								'1' => 'Display',
								'3' => 'Don\'t Display'
							) as $k => $v)
						{
							?><option value="<?php echo $k; ?>"<?php
								echo ($settings['iv_load_policy'] == $k ? ' selected' : ''); ?>><?php
								echo $v;
							?></option><?php
						}
					?></select>
				</label>
			</p>
			<p>
				<label><?php
					_e('Youtube Logo:');
					?><select class="widefat" name="<?php echo $this->get_field_name('modestbranding'); ?>"><?php
						foreach(array(
								'0' => 'Display',
								'1' => 'Don\'t Display'
							) as $k => $v)
						{
							?><option value="<?php echo $k; ?>"<?php
								echo ($settings['modestbranding'] == $k ? ' selected' : ''); ?>><?php
								echo $v;
							?></option><?php
						}
					?></select>
				</label>
			</p>
			<hr/>
			<p>
				<label><b><?php
					_e('Cache:');
					?></b><input type="text" name="<?php echo $this->get_field_name('cachenum'); ?>" value="<?php
						echo substr($settings['cache'], 0, stripos($settings['cache'], ' '));
						$unit = substr($settings['cache'], (stripos($settings['cache'], ' ') + 1));
						?>" size="5"/><select name="<?php echo $this->get_field_name('cacheunit'); ?>"><?php
							foreach(array('minutes', 'hours', 'days', 'weeks', 'months') as $u)
							{
								?><option<?php echo ($unit == $u ? ' selected' : ''); ?>><?php echo $u; ?></option><?php
							}
						?></select>
				</label>
			</p>
		</div><script>jQuery(setupYfAdmin);</script><?php
	}
} // class YoutubeFeederWidget

/**
 * Youtube Feeder Published Date Widget
 * @author awensley
 * @package YoutubeFeeder
 * @subpackage Widgets
 */
class YoutubeFeederPublishedDateWidget extends WP_Widget
{
	/**
	 * Constructor. Register widget with WordPress.
	 *
	 * @return YoutubeFeederPublishedDateWidget
	 */
	public function __construct()
	{
		parent::__construct(
			'youtubefeederpublisheddatewidget', // Base ID
			'YoutubeFeeder Published Date', // Name
			array('description' => __('Embed the published date of a Youtube Feed\'s first video in your sidebar.', 'YoutubeFeeder')) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 * @param array $args Widget arguments.
	 * @param array $instance Saved values from database.
	 * @return void
	 */
	public function widget($args, $instance)
	{
		extract($args);
		$title = apply_filters('widget_title', $instance['widgettitle']);
		echo $before_widget;
		if(!empty($title))
		{
			echo $before_title . $title . $after_title;
		}
		$yf = getYfInstance();
		echo $yf->getPublishedDate($instance); // Do the Youtube Feeder Work
		echo $after_widget;
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously-saved values from database.
	 * @return array Updated safe values to be saved.
	 */
	public function update($new_instance, $old_instance)
	{
		$yf = getYfInstance();
		$instance = shortcode_atts($yf->getSavedDefaults(), $new_instance);
		$instance['widgettitle'] = strip_tags($new_instance['widgettitle']);
		$instance['cache'] = ($new_instance['cachenum'] == 0 ? '0' : $new_instance['cachenum'] . ' '
			. (in_array($new_instance['cacheunit'], array('minutes', 'hours', 'weeks', 'months', 'days'))
				? $new_instance['cacheunit']
				: 'days'));
		$instance['feedid'] = (!empty($new_instance['standardfeedregion'])
			? $new_instance['standardfeedregion'] . '/' . $new_instance['feedid']
			: $new_instance['feedid']);
		return $instance;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 * @param array $instance Previously-saved values from database.
	 * @return void
	 */
	public function form($instance)
	{
		$yf = getYfInstance();
		$settings = shortcode_atts($yf->getSavedDefaults(), $instance);
		if(isset($instance['widgettitle']))
		{
			$title = $instance['widgettitle'];
		}
		else
		{
			$title = '';
		}
		?><div class="yfSettingsForm">
			<p>
				<label><?php
					_e('Title:');
					?><input class="widefat" name="<?php echo $this->get_field_name('widgettitle');
						?>" type="text" value="<?php echo esc_attr($title); ?>" />
				</label>
			</p>
			<hr/>
			<p style="font-weight:bold;">Feed Options</p>
			<p>
				<label><?php
					_e('Feed Type:');
					?><select class="widefat feedtype" name="<?php echo $this->get_field_name('feedtype');
						?>"><?php
						foreach(array(
								'uploads' => 'User Uploads',
								'favorites' => 'User Favorites',
								'subscriptions' => 'User Subscriptions',
								'playlist' => 'User Playlist',
								'standard' => 'Standard Feed',
								'category' => 'Category / Tag',
								'search' => 'Search'
							) as $k => $v)
						{
							?><option value="<?php echo $k ?>"<?php echo ($settings['feedtype'] == $k ? ' selected' : ''); ?>><?php
								echo $v;
							?></option><?php
						}
					?></select>
				</label>
			</p>
			<p class="feedtypeid feedtypeid-id">
				<label><?php
					_e('Feed ID:');
					?><input class="widefat feedid" type="text" name="<?php echo $this->get_field_name('feedid');
						?>" value="<?php echo $settings['feedid']; ?>" size="30" /><br/>
					<span class="feedtypeiddesc feedtypeiddesc-username">The Youtube Username</span>
					<span class="feedtypeiddesc feedtypeiddesc-playlist">
						The Playlist ID.<br/><br/>
						To find your Playlist ID, play a video from the playlist and copy the part of the URL after
						<mark>&amp;list=</mark> and before the next <mark>&amp;</mark>.<br/>
						<b>Example:</b><br/>
						If your URL is:
						<code>http://www.youtube.com/watch?v=Uolz7V12evc<mark>&amp;list=</mark><span style="text-decoration:underline;"><b>PLF125407272F3C1A4</b></span><mark>&amp;</mark>index=1</code><br/>
						Copy <span style="text-decoration:underline;"><b>PLF125407272F3C1A4</b></span> into the box above.
					</span>
					<span class="feedtypeiddesc feedtypeiddesc-category">Enter Category / Tag</span>
					<span class="feedtypeiddesc feedtypeiddesc-search">Enter Search Terms</span>
				</label>
			</p>
			<p class="feedtypeid feedtypeid-standard">
				<label><?php
					_e('Feed:');
					?><select class="widefat feedidstandard" name="<?php echo $this->get_field_name('feedid');
						?>"><?php
						$standardFeeds = array(
								'top_rated' => 'Top Rated',
								'top_favorites' => 'Top Favorites',
								'most_viewed' => 'Most Viewed',
								'most_shared' => 'Most Shared',
								'most_popular' => 'Most Popular',
								'most_recent' => 'Most Recent',
								'most_discussed' => 'Most Discussed',
								'most_responded' => 'Most Responded',
								'recently_featured' => 'Recently Featured',
								'on_the_web' => 'Trending Videos'
							);
						foreach($standardFeeds as $k => $v)
						{
							?><option value="<?php echo $k;?>"<?php
								echo (strstr($settings['feedid'], $k) !== false ? ' selected' : ''); ?>><?php
								echo $v;
							?></option><?php
						}
					?></select>
				</label>
			</p>
			<p class="feedtypeid feedtypeid-standard">
				<label><?php
					_e('Region (optional):');
					?><select class="widefat standardfeedregion" name="<?php echo $this->get_field_name('standardfeedregion');
						?>"><option></option><?php
						$regions = array(
								'AR' => 'Argentina',
								'AU' => 'Australia',
								'BR' => 'Brazil',
								'CA' => 'Canada',
								'CZ' => 'Czech Republic',
								'FR' => 'France',
								'DE' => 'Germany',
								'GB' => 'Great Britain',
								'HK' => 'Hong Kong',
								'HU' => 'Hungary',
								'IN' => 'India',
								'IE' => 'Ireland',
								'IL' => 'Israel',
								'IT' => 'Italy',
								'JP' => 'Japan',
								'MX' => 'Mexico',
								'NL' => 'Netherlands',
								'NZ' => 'New Zealand',
								'PL' => 'Poland',
								'RU' => 'Russia',
								'ZA' => 'South Africa',
								'KR' => 'South Korea',
								'ES' => 'Spain',
								'SE' => 'Sweden',
								'TW' => 'Taiwan',
								'US' => 'United States'
							);
						foreach($regions as $k => $v)
						{
							?><option value="<?php echo $k;?>"<?php
								echo (strstr($settings['feedid'], $k) !== false ? ' selected' : ''); ?>><?php
								echo $v;
							?></option><?php
						}
					?></select>
				</label>
			</p>
			<p>
				<label><?php
					_e('Order:');
					?><select class="widefat" name="<?php echo $this->get_field_name('orderby'); ?>"><?php
						foreach(array(
								'relevance' => 'Relevance',
								'published' => 'Date Published',
								'viewCount' => 'View Count',
								'commentCount' => 'Comment Count',
								'position' => 'Position',
								'duration' => 'Duration',
								'title' => 'Title',
								'rating' => 'Rating'
							) as $k => $v)
						{
							?><option value="<?php echo $k; ?>"<?php
								$so = $yf->getSpecificOrders();
								if(array_key_exists($k, $so))
								{
									?> class="orderby-<?php echo $so[$k]; ?>"<?php
								}
								echo ($settings['orderby'] == $k ? ' selected' : ''); ?>><?php
								echo $v;
							?></option><?php
						}
					?></select>
				</label>
			</p>
			<hr/>
			<p style="font-weight:bold;">Display Options</p>
			<p>
				<label><?php
					_e('Date Format:');
					?><input type="text" class="widefat" name="<?php echo $this->get_field_name('dateformat'); ?>" value="<?php
						echo $settings['dateformat']; ?>" size="20"/>
					<strong><?php echo date($settings['dateformat']); ?></strong>
					&lt;--Current format ('<?php echo $settings['dateformat']; ?>')
				</label>
			</p>
			<hr/>
			<p>
				<label><b><?php
					_e('Cache:');
					?></b><input type="text" name="<?php echo $this->get_field_name('cachenum'); ?>" value="<?php
						echo substr($settings['cache'], 0, stripos($settings['cache'], ' '));
						$unit = substr($settings['cache'], (stripos($settings['cache'], ' ') + 1));
						?>" size="5"/><select name="<?php echo $this->get_field_name('cacheunit'); ?>"><?php
							foreach(array('minutes', 'hours', 'days', 'weeks', 'months') as $u)
							{
								?><option<?php echo ($unit == $u ? ' selected' : ''); ?>><?php echo $u; ?></option><?php
							}
						?></select>
				</label>
			</p>
		</div><script>jQuery(setupYfAdmin);</script><?php
	}
} // class YoutubeFeederPublishedDateWidget

/**
 * Youtube Feeder Thumbnail Widget
 * @author awensley
 * @package YoutubeFeeder
 * @subpackage Widgets
 */
class YoutubeFeederThumbnailWidget extends WP_Widget
{
	/**
	 * Constructor. Register widget with WordPress.
	 *
	 * @return YoutubeFeederThumbnailWidget
	 */
	public function __construct()
	{
		parent::__construct(
			'youtubefeederthumbnailwidget', // Base ID
			'YoutubeFeeder Thumbnail', // Name
			array('description' => __('Embed the thumbnail of a Youtube Feed\'s first video in your sidebar.', 'YoutubeFeeder')) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 * @param array $args Widget arguments.
	 * @param array $instance Saved values from database.
	 * @return void
	 */
	public function widget($args, $instance)
	{
		extract($args);
		$title = apply_filters('widget_title', $instance['widgettitle']);
		echo $before_widget;
		if(!empty($title))
		{
			echo $before_title . $title . $after_title;
		}
		$yf = getYfInstance();
		echo $yf->getThumbnail($instance); // Do the Youtube Feeder Work
		echo $after_widget;
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously-saved values from database.
	 * @return array Updated safe values to be saved.
	 */
	public function update($new_instance, $old_instance)
	{
		$yf = getYfInstance();
		$instance = shortcode_atts($yf->getSavedDefaults(), $new_instance);
		$instance['widgettitle'] = strip_tags($new_instance['widgettitle']);
		$instance['cache'] = ($new_instance['cachenum'] == 0 ? '0' : $new_instance['cachenum'] . ' '
			. (in_array($new_instance['cacheunit'], array('minutes', 'hours', 'weeks', 'months', 'days'))
				? $new_instance['cacheunit']
				: 'days'));
		$instance['feedid'] = (!empty($new_instance['standardfeedregion'])
			? $new_instance['standardfeedregion'] . '/' . $new_instance['feedid']
			: $new_instance['feedid']);
		return $instance;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 * @param array $instance Previously-saved values from database.
	 * @return void
	 */
	public function form($instance)
	{
		$yf = getYfInstance();
		$settings = shortcode_atts($yf->getSavedDefaults(), $instance);
		if(isset($instance['widgettitle']))
		{
			$title = $instance['widgettitle'];
		}
		else
		{
			$title = '';
		}
		?><div class="yfSettingsForm">
			<p>
				<label><?php
					_e('Title:');
					?><input class="widefat" name="<?php echo $this->get_field_name('widgettitle');
						?>" type="text" value="<?php echo esc_attr($title); ?>" />
				</label>
			</p>
			<hr/>
			<p style="font-weight:bold;">Feed Options</p>
			<p>
				<label><?php
					_e('Feed Type:');
					?><select class="widefat feedtype" name="<?php echo $this->get_field_name('feedtype');
						?>"><?php
						foreach(array(
								'uploads' => 'User Uploads',
								'favorites' => 'User Favorites',
								'subscriptions' => 'User Subscriptions',
								'playlist' => 'User Playlist',
								'standard' => 'Standard Feed',
								'category' => 'Category / Tag',
								'search' => 'Search'
							) as $k => $v)
						{
							?><option value="<?php echo $k ?>"<?php echo ($settings['feedtype'] == $k ? ' selected' : ''); ?>><?php
								echo $v;
							?></option><?php
						}
					?></select>
				</label>
			</p>
			<p class="feedtypeid feedtypeid-id">
				<label><?php
					_e('Feed ID:');
					?><input class="widefat feedid" type="text" name="<?php echo $this->get_field_name('feedid');
						?>" value="<?php echo $settings['feedid']; ?>" size="30" /><br/>
					<span class="feedtypeiddesc feedtypeiddesc-username">The Youtube Username</span>
					<span class="feedtypeiddesc feedtypeiddesc-playlist">
						The Playlist ID.<br/><br/>
						To find your Playlist ID, play a video from the playlist and copy the part of the URL after
						<mark>&amp;list=</mark> and before the next <mark>&amp;</mark>.<br/>
						<b>Example:</b><br/>
						If your URL is:
						<code>http://www.youtube.com/watch?v=Uolz7V12evc<mark>&amp;list=</mark><span style="text-decoration:underline;"><b>PLF125407272F3C1A4</b></span><mark>&amp;</mark>index=1</code><br/>
						Copy <span style="text-decoration:underline;"><b>PLF125407272F3C1A4</b></span> into the box above.
					</span>
					<span class="feedtypeiddesc feedtypeiddesc-category">Enter Category / Tag</span>
					<span class="feedtypeiddesc feedtypeiddesc-search">Enter Search Terms</span>
				</label>
			</p>
			<p class="feedtypeid feedtypeid-standard">
				<label><?php
					_e('Feed:');
					?><select class="widefat feedidstandard" name="<?php echo $this->get_field_name('feedid');
						?>"><?php
						$standardFeeds = array(
								'top_rated' => 'Top Rated',
								'top_favorites' => 'Top Favorites',
								'most_viewed' => 'Most Viewed',
								'most_shared' => 'Most Shared',
								'most_popular' => 'Most Popular',
								'most_recent' => 'Most Recent',
								'most_discussed' => 'Most Discussed',
								'most_responded' => 'Most Responded',
								'recently_featured' => 'Recently Featured',
								'on_the_web' => 'Trending Videos'
							);
						foreach($standardFeeds as $k => $v)
						{
							?><option value="<?php echo $k;?>"<?php
								echo (strstr($settings['feedid'], $k) !== false ? ' selected' : ''); ?>><?php
								echo $v;
							?></option><?php
						}
					?></select>
				</label>
			</p>
			<p class="feedtypeid feedtypeid-standard">
				<label><?php
					_e('Region (optional):');
					?><select class="widefat standardfeedregion" name="<?php echo $this->get_field_name('standardfeedregion');
						?>"><option></option><?php
						$regions = array(
								'AR' => 'Argentina',
								'AU' => 'Australia',
								'BR' => 'Brazil',
								'CA' => 'Canada',
								'CZ' => 'Czech Republic',
								'FR' => 'France',
								'DE' => 'Germany',
								'GB' => 'Great Britain',
								'HK' => 'Hong Kong',
								'HU' => 'Hungary',
								'IN' => 'India',
								'IE' => 'Ireland',
								'IL' => 'Israel',
								'IT' => 'Italy',
								'JP' => 'Japan',
								'MX' => 'Mexico',
								'NL' => 'Netherlands',
								'NZ' => 'New Zealand',
								'PL' => 'Poland',
								'RU' => 'Russia',
								'ZA' => 'South Africa',
								'KR' => 'South Korea',
								'ES' => 'Spain',
								'SE' => 'Sweden',
								'TW' => 'Taiwan',
								'US' => 'United States'
							);
						foreach($regions as $k => $v)
						{
							?><option value="<?php echo $k;?>"<?php
								echo (strstr($settings['feedid'], $k) !== false ? ' selected' : ''); ?>><?php
								echo $v;
							?></option><?php
						}
					?></select>
				</label>
			</p>
			<p>
				<label><?php
					_e('Order:');
					?><select class="widefat" name="<?php echo $this->get_field_name('orderby'); ?>"><?php
						foreach(array(
								'relevance' => 'Relevance',
								'published' => 'Date Published',
								'viewCount' => 'View Count',
								'commentCount' => 'Comment Count',
								'position' => 'Position',
								'duration' => 'Duration',
								'title' => 'Title',
								'rating' => 'Rating'
							) as $k => $v)
						{
							?><option value="<?php echo $k; ?>"<?php
								$so = $yf->getSpecificOrders();
								if(array_key_exists($k, $so))
								{
									?> class="orderby-<?php echo $so[$k]; ?>"<?php
								}
								echo ($settings['orderby'] == $k ? ' selected' : ''); ?>><?php
								echo $v;
							?></option><?php
						}
					?></select>
				</label>
			</p>
			<hr/>
			<p style="font-weight:bold;">Display Options</p>
			<p><?php
				_e('Size:');
				?><br/><label><?php _e('Width:'); ?> <input type="text" name="<?php echo $this->get_field_name('width'); ?>" value="<?php echo $settings['width']; ?>" size="4"/></label>
				x <label><?php _e('Height:'); ?> <input type="text" name="<?php echo $this->get_field_name('height'); ?>" value="<?php echo $settings['height']; ?>" size="4"/></label>
			</p>
			<hr/>
			<p>
				<label><b><?php
					_e('Cache:');
					?></b><input type="text" name="<?php echo $this->get_field_name('cachenum'); ?>" value="<?php
						echo substr($settings['cache'], 0, stripos($settings['cache'], ' '));
						$unit = substr($settings['cache'], (stripos($settings['cache'], ' ') + 1));
						?>" size="5"/><select name="<?php echo $this->get_field_name('cacheunit'); ?>"><?php
							foreach(array('minutes', 'hours', 'days', 'weeks', 'months') as $u)
							{
								?><option<?php echo ($unit == $u ? ' selected' : ''); ?>><?php echo $u; ?></option><?php
							}
						?></select>
				</label>
			</p>
		</div><script>jQuery(setupYfAdmin);</script><?php
	}
} // class YoutubeFeederThumbnailWidget

/**
 * Retreives the global YoutubeFeeder Instance. Creates it if it doesn't exist already.
 *
 * @return YoutubeFeeder
 */
function getYfInstance()
{
	global $YoutubeFeeder;
	if(!isset($YoutubeFeeder))
	{
		// Create YoutubeFeeder object if it doesn't exist yet.
		$YoutubeFeeder = new YoutubeFeeder();
	}
	return $YoutubeFeeder;
}

/**
 * Registers the three widgets contained in this file with Wordpress.
 *
 * @return void
 */
function registerYoutubeFeederWidgets()
{
	register_widget('YoutubeFeederWidget');
	register_widget('YoutubeFeederPublishedDateWidget');
	register_widget('YoutubeFeederThumbnailWidget');
}

add_action('widgets_init', 'registerYoutubeFeederWidgets');

?>
