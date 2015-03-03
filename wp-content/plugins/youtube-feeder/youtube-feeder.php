<?php
/*
Plugin Name: Youtube Feeder
Plugin URI: http://andrewensley.com/projects/youtube-feeder-wordpress-plugin/
Version: 2.0.1
Author: Andrew Ensley
Author URI: http://andrewensley.com/
Description: Embed a dynamic Youtube video feed anywhere on your Wordpress blog.
License: GPLv3
*/

if(!class_exists('YoutubeFeeder'))
{
	/**
	 * Main Youtube Feeder Code
	 * @author awensley
	 * @package YoutubeFeeder
	 */
	class YoutubeFeeder
	{
		// Properties
		private $adminOptionsName = 'YoutubeFeederAdminOptions',
			$cacheOptionName = 'YoutubeFeederCache',
			$options = array(),
			$settings = array('user', 'feedid', 'feedtype', 'limit', 'width', 'height', 'dateformat', 'datelocation', 'title', 'style',
				'cache', 'thumbnail', 'description', 'orderby', 'region', 'autohide', 'autoplay', 'cc_load_policy', 'color',
				'controls', 'disablekb', 'fs', 'iv_load_policy', 'loop', 'modestbranding', 'rel', 'showinfo', 'theme'),
			$ytCount = 0,
			$specificOrders = array('relevance' => 'np', 'rating' => 'np', 'position' => 'p',
				'commentCount' => 'p', 'duration' => 'p', 'title' => 'p'),
			$version = '2.0.1';
		/**
		 * Constructor
		 *
		 * @return YoutubeFeeder
		 */
		public function __construct()
		{
			// Get options from DB
			$this->options = get_option($this->adminOptionsName);
			// Add CSS and JS
			add_action('wp_enqueue_scripts', array(&$this, 'ytEnqueueStyles'));
			add_action('wp_enqueue_scripts', array(&$this, 'ytEnqueueScripts'));
			// Add Youtube Feed Shortcode
			add_shortcode('youtubefeeder', array(&$this, 'youtubeFeederShortcode'));
			// Add Youtube Published Date Shortcode
			add_shortcode('youtubefeederpublisheddate', array(&$this, 'getPublishedDate'));
			// Add Youtube Thumbnail Shortcode
			add_shortcode('youtubefeederthumbnail', array(&$this, 'getThumbnail'));
			if(is_admin())
			{
				global $pagenow;
				// Add the admin menu item
				add_action('admin_menu', array(&$this, 'youtubefeeder_ap'));
				// Initial activation and upgrade actions
				add_action('admin_init', array(&$this, 'setAdminOptions'));
				if($pagenow == 'widgets.php')
				{
					add_action('admin_enqueue_scripts', array(&$this, 'ytEnqueueAdminScripts'));
				}
				else
				{
					add_action('admin_print_scripts-settings_page_YoutubeFeeder', array(&$this, 'ytEnqueueAdminScripts'));
				}
			}
		}

		/**
		 * Retrieve the saved default settings for youtube feeds from the database.
		 *
		 * @return array The saved default settings as an associative array.
		 */
		public function getSavedDefaults()
		{
			$defaults = array();
			foreach($this->settings as $s)
			{
				$defaults[$s] = $this->options[$s];
			}
			return $defaults;
		}

		/**
		 * Retrieve the 'orderby' values that have feed-type restraints (playlist-only, non-playlist-only).
		 *
		 * @return array The 'orderby' values as keys in an associative array. A value of 'p' indicates playlist, 'np' non-playlist.
		 */
		public function getSpecificOrders()
		{
			return $this->specificOrders;
		}

		/**
		 * Enqueues Javascript and CSS for the admin side.
		 *
		 * @return void
		 */
		public function ytEnqueueAdminScripts()
		{
			$this->ytEnqueueScripts(true);
			$this->ytEnqueueStyles();
		}

		/**
		 * Enqueues Javascript.
		 *
		 * @param bool $admin Set to true if on admin side. Defaults to false.
		 * @return void
		 */
		public function ytEnqueueScripts($admin = false)
		{
			wp_register_script(
					'YoutubeFeeder_jquery_youtubeplaylist',
					plugins_url('/js/jquery.youtubeplaylist.js', __FILE__),
					array('jquery'),
					$this->version
				);
			wp_enqueue_script('YoutubeFeeder_jquery_youtubeplaylist');
			if($admin)
			{
				wp_register_script('YoutubeFeeder_admin', plugins_url('/js/admin.js', __FILE__), array('jquery'), $this->version);
				wp_enqueue_script('YoutubeFeeder_admin');
			}
		}

		/**
		 * Enqueues CSS.
		 *
		 * @return void
		 */
		public function ytEnqueueStyles()
		{
			wp_register_style('YoutubeFeeder_styles', plugins_url('/css/style.css', __FILE__), array(), $this->version, 'all');
			wp_enqueue_style('YoutubeFeeder_styles');
		}

		/**
		 * Performs the work of processing the youtubefeeder shortcode.
		 *
		 * @param array $atts Associative array of provided shortcode paramaters.
		 * @return string The generated content.
		 */
		public function youtubeFeederShortcode($atts)
		{
			$this->ytCount++;
			$defaults = $this->getSavedDefaults();
			$settings = shortcode_atts($defaults, $atts);
			extract($settings);
			// Compatibility with old settings
			if(empty($feedid) && !empty($user))
			{
				$feedid = $user;
			}
			if(empty($feedtype))
			{
				$feedtype = 'uploads';
			}
			$thumbnail = ($thumbnail === 'true' ? 1 : ($thumbnail === 'false' ? 0 : $thumbnail));
			$description = ($description === 'true' ? 1 : ($description === 'false' ? 0 : $description));
			$title = ($title === 'true' ? 1 : ($title === 'false' ? 0 : $title));
			// End compatibility code
			$data = $this->getYoutubeData($feedid, $cache, $feedtype, $orderby);
			if(!$data)
			{
				return '<div style="border:1px solid red;margin:5px 5%;font-weight:bold;">Unable to get Youtube feed.  '
					. 'Did you supply the correct feed information?</div>';
			}
			$content = '<div class="youtubeFeeder youtubeFeeder' . $style . '" style="min-height:' . $height . 'px;">';
			switch($style)
			{
				case 'playlist':
					$this->youtubePlaylistStyle($data, $content, $limit, $width, $height, $dateformat,
						$datelocation, $title, $description, $thumbnail, $settings, $this->ytCount);
					break;
				case 'list':
					$this->youtubeListStyle($data, $content, $limit, $width, $height, $dateformat,
						$datelocation, $title, $description, $settings);
					break;
				case 'minimal':
				default:
					$this->youtubeMinimalStyle($data, $content, $limit, $width, $height, $settings);
					break;
			}
			$content .= '</div>';
			return $content;
		}

		/**
		 * Retrieves Youtube Feed data. First checks the cache to see if there is data younger than the cache param.
		 * Failing that, it retrieves the data from the Youtube API and caches it.
		 *
		 * @param string $id The Feed ID.
		 * @param string $cache How long to cache the data.
		 * @param string $type The Feed Type.
		 * @param string $orderby The Feed Order.
		 * @return array The retrieved Youtube Feed data.
		 */
		private function getYoutubeData($id, $cache, $type = 'uploads', $orderby)
		{
			if(!$entries = $this->getCachedYoutubeData($id, $cache, $type, $orderby))
			{
				$dataURL = $this->getFeedUrl($id, $type, $orderby);
				if($data = @file_get_contents($dataURL)){
					$data = json_decode($data, true);
				}
				elseif(function_exists('curl_init'))
				{
					$ch = curl_init();
					$timeout = 5; // set to zero for no timeout
					curl_setopt($ch, CURLOPT_URL, $dataURL);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
					$data = curl_exec($ch);
					curl_close($ch);
					$data = json_decode($data, true);
				}
				$entries = array();
				if($data && is_array($data['feed']['entry']))
				{
					foreach($data['feed']['entry'] as $vid)
					{
						$vid = $vid['media$group'];
						$url = $vid['media$content'][0]['url'];
						$url = substr($url, 0, stripos($url, '?'));
						$entries[] = array(
								'id' => $vid['yt$videoid']['$t'],
								'url' => $url,
								'title' => $vid['media$title']['$t'],
								'date' => $vid['yt$uploaded']['$t'],
								'content' => $vid['media$description']['$t']
							);
					}
				}
				if(count($entries) > 0)
				{
					$this->cacheYoutubeData($id, $entries, $type, $orderby);
				}
				else
				{
					$entries = false;
				}
			}
			return $entries;
		}

		/**
		 * Builds the Youtube Feed API URL
		 *
		 * @param string $id The Feed ID.
		 * @param string $type The Feed Type.
		 * @param string $orderby The Feed Order.
		 * @return string The Youtube Feed API URL
		 */
		private function getFeedUrl($id, $type = 'uploads', $orderby = '')
		{
			$urlBase = 'http://gdata.youtube.com/feeds/api/';
			$params = array(
					'alt' => 'json',
					'v' => 2,
					'format' => 5,
					'max-results' => 50,
					'safeSearch' => 'strict'
				);
			switch($type)
			{
				case 'category':
					$beforeId = 'videos/-';
					$afterId = '';
					$id = urlencode($id);
					$params['orderby'] = 'published';
					break;
				case 'favorites':
					$beforeId = 'users';
					$afterId = '/favorites';
					$params['orderby'] = 'published';
					break;
				case 'playlist':
					$beforeId = 'playlists';
					$afterId = '';
					$params['orderby'] = 'position';
					$id = (substr($id, 0, 2) === 'PL' ? substr($id, 2) : $id);
					break;
				case 'search':
					$beforeId = 'videos';
					$afterId = '?q=' . urlencode($id);
					$id = '';
					$params['orderby'] = 'relevance';
					break;
				case 'standard':
					$beforeId = 'standardfeeds';
					$afterId = '';
					$params['orderby'] = 'published';
					break;
				case 'subscriptions':
					$beforeId = 'users';
					$afterId = '/newsubscriptionvideos';
					$params['orderby'] = 'published';
					break;
				case 'uploads':
				default:
					$beforeId = 'users';
					$afterId = '/uploads';
					$params['orderby'] = 'published';
					break;
			}
			$url = $urlBase . $beforeId . '/' . $id . $afterId;
			if(strpos($url, '?') === false)
			{
				$url .= '?';
			}
			else
			{
				$url .= '&';
			}
			if(!empty($orderby)
				&& (!isset($this->specificOrders[$orderby]) || ($this->specificOrders[$orderby] == 'np' xor $type == 'playlist')))
			{
				$params['orderby'] = $orderby;
			}
			foreach($params as $k => $v)
			{
				$url .= $k . '=' . urlencode($v) . '&';
			}
			//error_log(rtrim($url, '&'));
			return rtrim($url, '&');
		}

		/**
		 * Retrieves Youtube Feed data from the cache.
		 *
		 * @param string $id The Feed ID.
		 * @param string $cache How long to cache the data.
		 * @param string $type The Feed Type.
		 * @param string $orderby The Feed Order.
		 * @return array|bool The retrieved Youtube Feed data as an array on success; False on failure.
		 */
		private function getCachedYoutubeData($id, $cache, $type = 'uploads', $orderby)
		{
			$key = $type . '-' . $id . '-' . $orderby;
			$youtubeCache = get_option($this->cacheOptionName);
			if(empty($youtubeCache) || empty($youtubeCache[$key]) || empty($youtubeCache[$key.'-date'])
				|| strtotime($youtubeCache[$key.'-date']) < strtotime('-' . $cache))
			{
				return false;
			}
			else
			{
				return $youtubeCache[$key];
			}
		}

		/**
		 * Caches Youtube Feed Data.
		 *
		 * @param string $id The Feed ID.
		 * @param array $data The Youtube Feed Data.
		 * @param string $type The Feed Type.
		 * @param string $orderby The Feed Order.
		 * @return void
		 */
		private function cacheYoutubeData($id, $data, $type = 'uploads', $orderby)
		{
			$key = $type . '-' . $id . '-' . $orderby;
			// Load the cache stored in the db
			$youtubeCache = get_option($this->cacheOptionName);
			if(empty($youtubeCache))
			{
				$youtubeCache = array();
			}
			$youtubeCache[$key] = $data;
			$youtubeCache[$key.'-date'] = date('Y-m-d H:i:s');
			// Store updated options back in the database.
			update_option($this->cacheOptionName, $youtubeCache);
		}

		/**
		 * Creates a Youtube Playlist from Feed Data in the "Playlist" style.
		 *
		 * @param array $data Youtube Feed Data.
		 * @param string $content The destination for output.
		 * @param string $limit The max number of videos to display.
		 * @param string $width The video width.
		 * @param string $height The video height.
		 * @param string $dateformat The date format in PHP date() style.
		 * @param string $datelocation The date location.
		 * @param int $title 0 or 1 to display the video title.
		 * @param int $description 0 or 1 to display the video description.
		 * @param int $thumbnail 0 or 1 to display the video thumbnail.
		 * @param array $playback An associative array of Youtube Video player playback attributes.
		 * @param int $ytCount The count of Playlists on the page.
		 * @return void
		 */
		private function youtubePlaylistStyle($data, &$content, $limit, $width, $height, $dateformat,
			$datelocation, $title, $description, $thumbnail, $playback, $ytCount)
		{
			$content .= '<div class="youtubeVideo" id="youtubeFeederPlayer' . $ytCount . '"></div>'
				. '<ul class="youtubeFeederPlaylistList" id="youtubeFeederPlaylist' . $ytCount . '">';
			$counter = 0;
			$ep = $this->getEmbedParams($playback);
			foreach($data as $vid)
			{
				$date = '<span class="youtubeDate' . $datelocation . '">'
					. date($dateformat, strtotime($vid['date'])) . '</span>';
				$content .= '<li><a href="http://youtube.com/watch?v=' . $vid['id'] . '&' . $ep . '">'
					. ($thumbnail == 1 ? '<img alt="' . $vid['title'] . '" class="youtubeThumb" src="'
						. $this->getThumbnailUrl($vid['id']) . '" />' : '')
					. ($datelocation == 'beforetitle' ? $date : '')
					. ($title == 1 ? '<span class="youtubeTitle">' . $vid['title'] . '</span>' : '')
					. ($datelocation == 'aftertitle' ? $date : '')
					. ($description == 1 ?
						($vid['content'] != '.' ? '<br/><small class="youtubeContent">' . $vid['content'] . '</small>' : '')
						: '') . '</a></li>';
				$counter++;
				if($counter == $limit)
				{
					break;
				}
			}
			$content .= '</ul><script>jQuery("#youtubeFeederPlaylist' . $ytCount . '").ytplaylist({playerHeight:"' . $height
				. '",playerWidth:"' . $width . '",holderId:"youtubeFeederPlayer' . $ytCount . '",embedParams:"' . $ep . '"});</script>';
		}

		/**
		 * Creates a Youtube Playlist from Feed Data in the "Minimal" style.
		 *
		 * @param array $data Youtube Feed Data.
		 * @param string $content The destination for output.
		 * @param string $limit The max number of videos to display.
		 * @param string $width The video width.
		 * @param string $height The video height.
		 * @param array $playback An associative array of Youtube Video player playback attributes.
		 * @return void
		 */
		private function youtubeMinimalStyle($data, &$content, $limit, $width, $height, $playback)
		{
			$vid = array_shift($data);
			$url = '//www.youtube.com/embed/' . $vid['id'] . '?' . $this->getEmbedParams($playback);
			$counter = 1;
			$playlist = '';
			if($limit > 1)
			{
				foreach($data as $vid)
				{
					$playlist .= (empty($playlist) ? '&playlist=' : ',') . $vid['id'];
					$counter++;
					if($counter == $limit)
					{
						break;
					}
				}
			}
			$content .= '<div class="youtubeVideo"><iframe width="' . $width . '" height="' . $height
				. '" src="' . $url . $playlist . '" frameborder="0" allowfullscreen></iframe></div>';
		}

		/**
		 * Creates a Youtube Playlist from Feed Data in the "List" style.
		 *
		 * @param array $data Youtube Feed Data.
		 * @param string $content The destination for output.
		 * @param string $limit The max number of videos to display.
		 * @param string $width The video width.
		 * @param string $height The video height.
		 * @param string $dateformat The date format in PHP date() style.
		 * @param string $datelocation The date location.
		 * @param int $title 0 or 1 to display the video title.
		 * @param int $description 0 or 1 to display the video description.
		 * @param array $playback An associative array of Youtube Video player playback attributes.
		 * @return void
		 */
		private function youtubeListStyle($data, &$content, $limit, $width, $height, $dateformat, $datelocation, $title, $description, $playback)
		{
			$counter = 0;
			$ep = $this->getEmbedParams($playback);
			foreach($data as $vid)
			{
				$date = '<span class="youtubeDate' . $datelocation . '">'
					. date($dateformat, strtotime($vid['date'])) . '</span>';
				$content.= ($datelocation == 'beforevideo' ? $date : '')
					. '<div class="youtubeVideo"><iframe width="' . $width . '" height="' . $height
					. '" src="//www.youtube.com/embed/' . $vid['id'] . '?' . $ep
					. '" frameborder="0" allowfullscreen></iframe></div>'
//					Old embed style:
//					. '<div class="youtubeVideo"><object width="' . $width . '" height="' . $height . '">'
//					. '<param name="movie" value="' . $vid['url'] . '"></param>'
//					. '<param name="allowfullscreen" value="true"></param>'
//					. '<param name="allowscriptaccess" value="always"></param>'
//					. '<embed src="' . $vid['url'] . '" type="application/x-shockwave-flash" allowscriptaccess="always"'
//						. ' allowfullscreen="true" width="' . $width . '" height="' . $height . '"></embed></object></div>'
					. '<div class="youtubeTitle">' . ($datelocation == 'beforetitle' ? $date : '')
					. ($title == 1 ? $vid['title'] : '')
					. ($datelocation == 'aftertitle' ? $date : '') . '</div>'
					. ($description == 1 ?
						($vid['content'] != '.' ? '<div class="youtubeContent">' . $vid['content'] . '</div>' : '') : '') . "\n";
				$counter++;
				if($counter == $limit)
				{
					break;
				}
			}
		}

		/**
		 * Performs the work of processing the youtubefeederpublisheddate shortcode.
		 *
		 * @param array $atts Associative array of provided shortcode paramaters.
		 * @return string The generated content.
		 */
		public function getPublishedDate($atts)
		{
			$defaults = $this->getSavedDefaults();
			extract(shortcode_atts($defaults, $atts));
			if(empty($feedid) && !empty($user))
			{
				$feedid = $user;
			}
			if(empty($feedtype))
			{
				$feedtype = 'uploads';
			}
			$data = $this->getYoutubeData($feedid, $cache, $feedtype, $orderby);
			if(!$data)
			{
				return '';
			}
			else
			{
				return date($dateformat, strtotime($data[0]['date']));
			}
		}

		/**
		 * Performs the work of processing the youtubefeederthumbnail shortcode.
		 *
		 * @param array $atts Associative array of provided shortcode paramaters.
		 * @return string The generated content.
		 */
		public function getThumbnail($atts)
		{
			$defaults = $this->getSavedDefaults();
			extract(shortcode_atts($defaults, $atts));
			if(!empty($user))
			{
				$feedid = $user;
			}
			if(empty($feedtype))
			{
				$feedtype = 'uploads';
			}
			$data = $this->getYoutubeData($feedid, $cache, $feedtype, $orderby);
			$vid = $data[0];
			return '<img alt="' . $vid['title'] . '" width="' . $width . '" height="' . $height
				. '" class="youtubeThumb" src="' . $this->getThumbnailUrl($vid['id'], 'hqdefault') . '" />';
		}

		/**
		 * Gets the embed parameters for embedding Youtube Videos as a URL query string.
		 *
		 * @param array $settings associative array of embed parameters and their values.
		 * @return string The embed parameters as a URL query string.
		 */
		private function getEmbedParams($settings = array())
		{
			$params = '';
			foreach(array('autohide', 'autoplay', 'cc_load_policy', 'color', 'controls', 'disablekb',
				'fs', 'iv_load_policy', 'loop', 'modestbranding', 'rel', 'showinfo', 'theme') as $s)
			{
				$params .= (empty($params) ? '' : '&') . $s . '=' . urlencode($settings[$s]);
			}
			return $params;
		}

		/**
		 * Gets the thumbnail url for a given video id.
		 *
		 * @param string $id The video ID.
		 * @param string $thumb The thumbnail to display. Valid values: 'default', 'hqdefault', '0', '1', '2', '3'
		 */
		private function getThumbnailUrl($id, $thumb = 'default')
		{
			return '//i.ytimg.com/vi/' . $id . '/' . $thumb . '.jpg';
		}

		/**
		 * Adds the options page link for Youtube Feeder
		 *
		 * @return void
		 */
		public function YoutubeFeeder_ap()
		{
			if(isset($this))
			{
				if(function_exists('add_options_page'))
				{
					add_options_page('YoutubeFeeder', 'YoutubeFeeder', 9, 'YoutubeFeeder', array(&$this, 'printAdminPage'));
				}
			}
		}

		/**
		 * Sets Admin Option defaults while respecting previously-saved values, if present.
		 * This is to be executed on plugin activation and upgrade.
		 *
		 * @return void
		 */
		public function setAdminOptions()
		{
			if($this->options === false || !isset($this->options['version']) || $this->options['version'] < $this->version)
			{
				if(!is_array($this->options))
				{
					$this->options = array();
				}
				// Default values for configuration options
				$youtubeAdminOptions = array(
						'user' => '',
						'feedid' => '',
						'feedtype' => 'uploads',
						'limit' => 5,
						'height' => 385,
						'width' => 480,
						'style' => 'minimal',
						'dateformat' => 'n/j/y g:ia',
						'datelocation' => 'aftertitle',
						'cache' => '1 days',
						'thumbnail' => 1,
						'description' => 0,
						'orderby' => 'published',
						'title' => 1,
						'region' => '',
						'autohide' => '2',
						'autoplay' => '0',
						'cc_load_policy' => '0',
						'color' => 'red',
						'controls' => '1',
						'disablekb' => '0',
						'fs' => '1',
						'iv_load_policy' => '1',
						'loop' => '0',
						'modestbranding' => '0',
						'rel' => '1',
						'showinfo' => '1',
						'theme' => 'dark'
					);
				// Load options stored in the Database
				$youtubeOptions = get_option($this->adminOptionsName);
				if(!empty($youtubeOptions))
				{
					// Overwrite defaults with options retrieved from the database
					foreach($youtubeOptions as $key => $option)
					{
						$youtubeAdminOptions[$key] = $option;
					}
					// Move value from deprecated 'user' to new 'feedid' field.
					if(empty($youtubeAdminOptions['feedid']) && !empty($youtubeAdminOptions['user']))
					{
						$youtubeAdminOptions['feedid'] = $youtubeAdminOptions['user'];
					}
				}
				// Store updated options back in the database.
				update_option($this->adminOptionsName, $youtubeAdminOptions);
			}
		}
		/**
		 * Prints out the admin settings page and handles settings form submissions.
		 *
		 * @return void
		 */
		public function printAdminPage()
		{
			// Clear Cache button was pressed
			if(isset($_POST['YoutubeFeeder_clearcache']))
			{
				if(delete_option($this->cacheOptionName))
				{
					?><div class="updated"><p><strong><?php
						_e('Youtube Feeder Cache Cleared', 'YoutubeFeeder');
					?></strong></p></div><?php
				}
				else
				{
					?><div class="error"><p><strong><?php
						_e('Unable to clear Youtube Feeder Cache', 'YoutubeFeeder');
					?></strong></p></div><?php
				}
			}
			// The form was submitted
			if(isset($_POST['update_YoutubeFeederSettings']))
			{
				foreach($this->settings as $s)
				{
					switch($s)
					{
						case 'cache':
							if(isset($_POST['cachenum']))
							{
								switch($_POST['cacheunit'])
								{
									case 'minutes':
										$unit = 'minutes';
										break;
									case 'hours':
										$unit = 'hours';
										break;
									case 'weeks':
										$unit = 'weeks';
										break;
									case 'months':
										$unit = 'months';
										break;
									case 'days':
									default:
										$unit = 'days';
										break;
								}
								$tnum = $_POST['cachenum'];
								$timeperiod = ($tnum == 0 ? '0' : $tnum . ' ' . $unit);
								$this->options['cache'] = $timeperiod;
							}
							break;
						case 'feedid':
							if(isset($_POST[$s]))
							{
								if(!empty($_POST['standardfeedregion']))
								{
									$this->options[$s] = $_POST['standardfeedregion'] . '/' . $_POST[$s];
								}
								else
								{
									$this->options[$s] = $_POST[$s];
								}
							}
							break;
						default:
							if(isset($_POST[$s]))
							{
								$this->options[$s] = $_POST[$s];
							}
							break;
					}
				}
				// Store options in the database
				if(update_option($this->adminOptionsName, $this->options))
				{
					?><div class="updated"><p><strong><?php
						_e('Settings Updated.', 'YoutubeFeeder');
					?></strong></p></div><?php
				}
				else
				{
					?><div class="error"><p><strong><?php
						_e('Unable to update settings.', 'YoutubeFeeder');
					?></strong></p></div><?php
				}
			}
			?><div class="wrap yfSettingsForm">
				<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
					<h2>Youtube Feeder Plugin</h2>
					<br /><strong>The following settings are merely defaults.<br />
					All settings can be overriden by shortcode attributes or parameters passed to the function call.</strong><br/>
					<small>For FAQ and support, refer to the official <a href="http://andrewensley.com/projects/youtube-feeder-wordpress-plugin/"
						title="Youtube Feeder Wordpress Plugin | AndrewEnsley.com" target="_blank">Youtube Feeder Plugin page.</a></small>
					<hr/>
					<h3>Feed Options</h3>
					<h4>Feed Type</h4>
					<!-- Documented here: https://developers.google.com/youtube/2.0/reference#Video_Feeds -->
						<select name="feedtype" class="feedtype"><?php
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
								?><option value="<?php echo $k ?>"<?php echo ($this->options['feedtype'] == $k ? ' selected' : ''); ?>><?php
									echo $v;
								?></option><?php
							}
						?></select>
					<hr/>
					<div class="feedtypeid feedtypeid-id">
						<h4>Feed ID</h4>
						<label>
							<input type="text" name="feedid" class="feedid" value="<?php echo $this->options['feedid']; ?>" size="30" /><br/>
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
						<hr/>
					</div>
					<div class="feedtypeid feedtypeid-standard">
						<h4>Feed</h4>
						<label>
							Choose a Standard Feed:
							<select name="feedid" class="feedidstandard"><?php
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
										echo (strstr($this->options['feedid'], $k) !== false ? ' selected' : ''); ?>><?php
										echo $v;
									?></option><?php
								}
							?></select>
						</label><br/>
						<label>
							Choose a Region (optional - leave empty for global feeds):
							<select name="standardfeedregion" class="standardfeedregion"><option></option><?php
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
										echo (strstr($this->options['feedid'], $k . '/') !== false ? ' selected' : ''); ?>><?php
										echo $v;
									?></option><?php
								}
							?></select>
						</label>
						<hr/>
					</div>
					<h4>Order</h4>
						<select name="orderby"><?php
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
									if(array_key_exists($k, $this->specificOrders))
									{
										?> class="orderby-<?php echo $this->specificOrders[$k]; ?>"<?php
									}
									echo ($this->options['orderby'] == $k ? ' selected' : ''); ?>><?php
									echo $v;
								?></option><?php
							}
						?></select>
					<hr/>
					<h3>Style</h3>
						<label>
							<input type="radio" name="style" value="playlist"<?php
								echo ($this->options['style'] == 'playlist' ? ' checked' : ''); ?> />
							<strong>Play List</strong>
						</label><br/>
						<a href="#" class="previewButton playlistPreview">Preview:</a>
						<div style="height:600px;overflow:auto;border:1px solid black;display:none;" class="previewDiv playlistPreviewDiv"><?php
							if(!empty($this->options['user']))
							{
								echo do_shortcode('[youtubefeeder limit="3" style="playlist" cache="1 year"]');
							}
							else
							{
								echo do_shortcode('[youtubefeeder user="flamadiddle86" limit="3" style="playlist" cache="1 week"]');
							}
						?></div><br/>
						<label>
							<input type="radio" name="style" value="minimal"<?php
								echo ($this->options['style'] == 'minimal' ? ' checked' : ''); ?> />
							<strong>Minimal</strong>
							<small>Only works with flash player. Not mobile-compatible.</small>
						</label><br/>
						<a href="#" class="previewButton minimalPreview">Preview:</a>
						<div style="height:600px;overflow:auto;border:1px solid black;display:none;" class="previewDiv minimalPreviewDiv"><?php
							if(!empty($this->options['user']))
							{
								echo do_shortcode('[youtubefeeder limit="5" style="minimal" cache="1 year"]');
							}
							else
							{
								echo do_shortcode('[youtubefeeder user="flamadiddle86" limit="5" style="minimal" cache="1 week"]');
							}
						?></div><br/>
						<label>
							<input type="radio" name="style" value="list"<?php
								echo ($this->options['style'] == 'list' ? ' checked' : ''); ?> />
							<strong>Simple List</strong>
						</label><br/>
						<a href="#" class="previewButton listPreview">Preview:</a>
						<div style="height:600px;overflow:auto;border:1px solid black;display:none;" class="previewDiv listPreviewDiv"><?php
							if(!empty($this->options['user']))
							{
								echo do_shortcode('[youtubefeeder limit="2" style="list" cache="1 year"]');
							}
							else
							{
								echo do_shortcode('[youtubefeeder user="flamadiddle86" limit="2" style="list" cache="1 week"]');
							}
						?></div>
					<hr/>
					<h3>Display Options</h3>
					<table class="yfSettings">
						<tr>
							<td><label for="limit">Limit</label></td>
							<td><input type="text" name="limit" id="limit" value="<?php echo $this->options['limit']; ?>" size="5"/></td>
							<td>
								The maximum number of videos to display.<br/>
								<small style="color:blue;">
									The Youtube API limits all requests to 50 results.
									Because of this, there is no way to display more than 50 videos.
								</small>
							</td>
						</tr>
						<tr>
							<td>Size</td>
							<td>
								<label>
									Width:
									<input type="text" name="width" value="<?php echo $this->options['width']; ?>" size="4"/>
								</label>
								x
								<label>
									Height:
									<input type="text" name="height" value="<?php echo $this->options['height']; ?>" size="4"/>
								</label>
							</td>
							<td>The size of the video player.</td>
						</tr>
						<tr>
							<td><label for="thumbnail">Show Thumbnail</label></td>
							<td>
								<select name="thumbnail" id="thumbnail"><?php
									foreach(array('1' => 'Yes', '0' => 'No') as $k => $v)
									{
										?><option value="<?php echo $k; ?>"<?php
											echo ($this->options['thumbnail'] == $k ? ' selected' : ''); ?>><?php
											echo $v;
										?></option><?php
									}
								?></select>
							</td>
							<td>Only affects the "playlist" style.  This is whether or not to show video thumbnails in the play list.</td>
						</tr>
						<tr>
							<td><label for="title">Show Title</label></td>
							<td>
								<select name="title" id="title"><?php
									foreach(array('1' => 'Yes', '0' => 'No') as $k => $v)
									{
										?><option value="<?php echo $k; ?>"<?php
											echo ($this->options['title'] == $k ? ' selected' : ''); ?>><?php
											echo $v;
										?></option><?php
									}
								?></select>
							</td>
							<td></td>
						</tr>
						<tr>
							<td><label for="description">Show Description</label></td>
							<td>
								<select name="description" id="description"><?php
									foreach(array('1' => 'Yes', '0' => 'No') as $k => $v)
									{
										?><option value="<?php echo $k; ?>"<?php
											echo ($this->options['description'] == $k ? ' selected' : ''); ?>><?php
											echo $v;
										?></option><?php
									}
								?></select>
							</td>
							<td></td>
						</tr>
						<tr>
							<td><label for="datelocation">Date Location</label></td>
							<td>
								<select name="datelocation" id="datelocation"><?php
									foreach(array(
											'beforevideo' => 'Before Video',
											'beforetitle' => 'Before Title',
											'aftertitle' => 'After Title',
											'nowhere' => 'Don\'t display date'
										) as $k => $v)
									{
										?><option value="<?php echo $k; ?>"<?php
											echo ($this->options['datelocation'] == $k ? ' selected' : ''); ?>><?php
											echo $v;
										?></option><?php
									}
								?></select>
							</td>
							<td></td>
						</tr>
						<tr>
							<td><label for="dateformat">Date Format</label></td>
							<td>
								<input type="text" name="dateformat" id="dateformat" value="<?php
									echo $this->options['dateformat']; ?>" size="20"/><br/>
								<strong><?php echo date($this->options['dateformat']); ?></strong>
								&lt;--Current format ('<?php echo $this->options['dateformat']; ?>')
							</td>
							<td>
								<small>
									For an explanation of available date formats, see the
									<a href="http://php.net/date" title="PHP date() Function" target="_blank">
										doc page for the PHP date() function
									</a>
								</small>
							</td>
						</tr>
					</table>
					<br/>
					<h3>Playback Options</h3>
					<!-- Documented here: https://developers.google.com/youtube/player_parameters#Parameters -->
					<table class="yfSettings">
						<tr>
							<td><label for="autoplay">Autoplay</label></td>
							<td>
								<select name="autoplay" id="autoplay"><?php
									foreach(array(
											'0' => 'No',
											'1' => 'Yes'
										) as $k => $v)
									{
										?><option value="<?php echo $k; ?>"<?php
											echo ($this->options['autoplay'] == $k ? ' selected' : ''); ?>><?php
											echo $v;
										?></option><?php
									}
								?></select>
							</td>
							<td></td>
						</tr>
						<tr>
							<td><label for="loop">Loop</label></td>
							<td>
								<select name="loop" id="loop"><?php
									foreach(array(
											'0' => 'No',
											'1' => 'Yes'
										) as $k => $v)
									{
										?><option value="<?php echo $k; ?>"<?php
											echo ($this->options['loop'] == $k ? ' selected' : ''); ?>><?php
											echo $v;
										?></option><?php
									}
								?></select>
							</td>
							<td>
								Whether the video will startover at the beginning when the end is reached.
								In the case of the 'minimal' style, the player will play the entire playlist and then start again
								at the first video.
							</td>
						</tr>
						<tr>
							<td><label for="rel">Related Videos</label></td>
							<td>
								<select name="rel" id="rel"><?php
									foreach(array(
											'1' => 'Display',
											'0' => 'Don\'t Display'
										) as $k => $v)
									{
										?><option value="<?php echo $k; ?>"<?php
											echo ($this->options['rel'] == $k ? ' selected' : ''); ?>><?php
											echo $v;
										?></option><?php
									}
								?></select>
							</td>
							<td></td>
						</tr>
						<tr>
							<td><label for="showinfo">Video Information</label></td>
							<td>
								<select name="showinfo" id="showinfo"><?php
									foreach(array(
											'1' => 'Display',
											'0' => 'Don\'t Display'
										) as $k => $v)
									{
										?><option value="<?php echo $k; ?>"<?php
											echo ($this->options['showinfo'] == $k ? ' selected' : ''); ?>><?php
											echo $v;
										?></option><?php
									}
								?></select>
							</td>
							<td>
								When using 'minimal' style, the player will also display thumbnail images for the videos in the playlist.
							</td>
						</tr>
						<tr>
							<td><label for="controls">Controls</label></td>
							<td>
								<select name="controls" id="controls"><?php
									foreach(array(
											'1' => 'Display',
											'0' => 'Don\'t Display'
										) as $k => $v)
									{
										?><option value="<?php echo $k; ?>"<?php
											echo ($this->options['controls'] == $k ? ' selected' : ''); ?>><?php
											echo $v;
										?></option><?php
									}
								?></select>
							</td>
							<td></td>
						</tr>
						<tr>
							<td><label for="disablekb">Keyboard Controls</label></td>
							<td>
								<select name="disablekb" id="disablekb"><?php
									foreach(array(
											'0' => 'Enabled',
											'1' => 'Disabled'
										) as $k => $v)
									{
										?><option value="<?php echo $k; ?>"<?php
											echo ($this->options['disablekb'] == $k ? ' selected' : ''); ?>><?php
											echo $v;
										?></option><?php
									}
								?></select>
							</td>
							<td>Keyboard controls only work with flash player. Not mobile-compatible.</td>
						</tr>
						<tr>
							<td><label for="fs">FullScreen Button</label></td>
							<td>
								<select name="fs" id="fs"><?php
									foreach(array(
											'1' => 'Display',
											'0' => 'Don\'t Display'
										) as $k => $v)
									{
										?><option value="<?php echo $k; ?>"<?php
											echo ($this->options['fs'] == $k ? ' selected' : ''); ?>><?php
											echo $v;
										?></option><?php
									}
								?></select>
							</td>
							<td>The Fullscreen button only works with the flash player. Not mobile-compatible.</td>
						</tr>
						<tr>
							<td><label for="theme">Control Bar Theme</label></td>
							<td>
								<select name="theme" id="theme"><?php
									foreach(array(
											'dark' => 'Dark',
											'light' => 'Light'
										) as $k => $v)
									{
										?><option value="<?php echo $k; ?>"<?php
											echo ($this->options['theme'] == $k ? ' selected' : ''); ?>><?php
											echo $v;
										?></option><?php
									}
								?></select>
							</td>
							<td></td>
						</tr>
						<tr>
							<td><label for="autohide">Autohide</label></td>
							<td>
								<select name="autohide" id="autohide"><?php
									foreach(array(
											'2' => 'Progress Bar Only',
											'1' => 'Progress Bar and Controls',
											'0' => 'Disabled'
										) as $k => $v)
									{
										?><option value="<?php echo $k; ?>"<?php
											echo ($this->options['autohide'] == $k ? ' selected' : ''); ?>><?php
											echo $v;
										?></option><?php
									}
								?></select>
							</td>
							<td></td>
						</tr>
						<tr>
							<td><label for="color">Progress Bar Color</label></td>
							<td>
								<select name="color" id="color"><?php
									foreach(array(
											'red' => 'Red',
											'white' => 'White'
										) as $k => $v)
									{
										?><option value="<?php echo $k; ?>"<?php
											echo ($this->options['color'] == $k ? ' selected' : ''); ?>><?php
											echo $v;
										?></option><?php
									}
								?></select>
							</td>
							<td>Setting to 'White' will force the Youtube Logo to display.</td>
						</tr>
						<tr>
							<td><label for="cc_load_policy">Closed Captions</label></td>
							<td>
								<select name="cc_load_policy" id="cc_load_policy"><?php
									foreach(array(
											'0' => 'User Preference',
											'1' => 'Force On'
										) as $k => $v)
									{
										?><option value="<?php echo $k; ?>"<?php
											echo ($this->options['cc_load_policy'] == $k ? ' selected' : ''); ?>><?php
											echo $v;
										?></option><?php
									}
								?></select>
							</td>
							<td>Closed Captions only works with the flash player. Not mobile-compatible.</td>
						</tr>
						<tr>
							<td><label for="iv_load_policy">Video Annotations</label></td>
							<td>
								<select name="iv_load_policy" id="iv_load_policy"><?php
									foreach(array(
											'1' => 'Display',
											'3' => 'Don\'t Display'
										) as $k => $v)
									{
										?><option value="<?php echo $k; ?>"<?php
											echo ($this->options['iv_load_policy'] == $k ? ' selected' : ''); ?>><?php
											echo $v;
										?></option><?php
									}
								?></select>
							</td>
							<td>Video Annotations only work with the flash player. Not mobile-compatible.</td>
						</tr>
						<tr>
							<td><label for="modestbranding">Youtube Logo</label></td>
							<td>
								<select name="modestbranding" id="modestbranding"><?php
									foreach(array(
											'0' => 'Display',
											'1' => 'Don\'t Display'
										) as $k => $v)
									{
										?><option value="<?php echo $k; ?>"<?php
											echo ($this->options['modestbranding'] == $k ? ' selected' : ''); ?>><?php
											echo $v;
										?></option><?php
									}
								?></select>
							</td>
							<td></td>
						</tr>
					</table>
					<br/>
					<h3>Cache</h3>
					<h4>Cache Time Period</h4>
						<input type="text" name="cachenum" value="<?php
							echo substr($this->options['cache'], 0, stripos($this->options['cache'], ' '));
							$unit = substr($this->options['cache'], (stripos($this->options['cache'], ' ') + 1));
							?>" size="5"/><select name="cacheunit"><?php
								foreach(array('minutes', 'hours', 'days', 'weeks', 'months') as $u)
								{
									?><option<?php echo ($unit == $u ? ' selected' : ''); ?>><?php echo $u; ?></option><?php
								}
							?></select><br/>
						This plugin can cache the data in the Youtube feed to reduce server load (recommended).<br/>
						Enter "0" (without quotes) to disable caching and always serve the most up-to-date
						feed<span style="color:red;font-weight:bold;">*</span>.<br/>
						<small style="color:red;font-weight:bold;">*Warning: this will slow down your site for all visitors.</small>
						<div class="submit">
							<input type="submit" name="YoutubeFeeder_clearcache" value="<?php _e('Clear Cache Now', 'YoutubeFeeder'); ?>" />
						</div>
					<hr/>
					<div class="submit">
						<input type="submit" name="update_YoutubeFeederSettings" value="<?php _e('Update Settings', 'YoutubeFeeder'); ?>" />
					</div>
				</form>
			</div><script>jQuery(setupYfAdmin);</script><?php
		}//End function printAdminPage()
	}
}//End Class YoutubeFeeder

if(class_exists('YoutubeFeeder'))
{
	$YoutubeFeeder = new YoutubeFeeder();
}

if(!function_exists('embed_youtube_thumb'))
{
	/**
	 * Runs the youtubefeederthumbnail shortcode from a function call.
	 *
	 * @param array $options Shortcode attributes as an associative array.
	 */
	function embed_youtube_thumb($options = array())
	{
		$shortcode = '[youtubefeederthumbnail';
		foreach($options as $key=>$val)
		{
			$shortcode .= ' ' . $key . '="' . $val . '"';
		}
		$shortcode .= ']';
		echo do_shortcode($shortcode);
	}
}

if(!function_exists('embed_youtube_feed'))
{
	/**
	 * Runs the youtubefeeder shortcode from a function call.
	 *
	 * @param array $options Shortcode attributes as an associative array.
	 */
	function embed_youtube_feed($options = array())
	{
		$shortcode = '[youtubefeeder';
		foreach($options as $key=>$val)
		{
			$shortcode .= ' ' . $key . '="' . $val . '"';
		}
		$shortcode .= ']';
		echo do_shortcode($shortcode);
	}
}

if(!function_exists('get_youtube_published_date'))
{
	/**
	 * Runs the youtubefeedertpublisheddate shortcode from a function call.
	 *
	 * @param array $options Shortcode attributes as an associative array.
	 */
	function get_youtube_published_date($options = array())
	{
		$shortcode = '[youtubefeederpublisheddate';
		foreach($options as $key=>$val)
		{
			$shortcode .= ' ' . $key . '="' . $val . '"';
		}
		$shortcode .= ']';
		echo do_shortcode($shortcode);
	}
}

include_once(dirname(__FILE__) . '/youtube-feeder-widget.php');

?>
