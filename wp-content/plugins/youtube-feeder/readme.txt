=== Youtube Feeder ===
Author: awensley
Contributors: awensley
Author URI: http://andrewensley.com/
Plugin URI: http://andrewensley.com/projects/youtube-feeder-wordpress-plugin/
Donate link: http://andrewensley.com/donate/
Tags: youtube, video, videos, playlist, list, live, feed, livefeed, youtube-feeder, feeder, youtubefeeder
Requires at least: 2.6
Tested up to: 3.4.1
Stable tag: 2.0.1
License: GPLv3

Embed a dynamic Youtube video feed anywhere on your Wordpress blog.

== Description ==

Youtube Feeder allows you to embed a live Youtube feed anywhere in your Wordpress blog.  The feed is always up to date because it pulls directly from Youtube's data api, but it can also cache the Youtube feed based on configurable settings.

The feed can be:

* User Uploads
* User Favorites
* User Subscriptions
* User Playlist
* Standard Feed
* Category / Tag
* Search

The plugin is very flexible because every aspect is configurable.  Each video feed can be configured separately, or you can use defaults that take effect site-wide.  Each component is given accessible classes for complete customization of the display through CSS.

The "Playlist" style of display was made possible by [a jQuery plugin by Dan Drayne](http://www.geckonewmedia.com/blog/2009/8/14/jquery-youtube-playlist-plugin---youtubeplaylist/).  The code is used with permission.

== Installation ==

Follow these instructions to install the plugin.

1. Unzip the files from the download file
2. Upload the entire `youtube-feeder` folder to your `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to the Youtube Feeder options page and choose your settings
5. Put the shortcode [youtubefeeder] in any post or page or embed_youtube_feed() anywhere in your template files.

== Frequently Asked Questions ==

= Q: Why don't all videos show up? =
A: Youtube restricts some videos from being embedded on public websites. This can be for copyright reasons or settings configured by the uploader. As a result, this plugin will not be able to embed those videos.

If you are the uploader, and you didn't restrict public embedding on the video yourself, it's most likely because music in the video is copyrighted or it contains clips of a copyrighted video. You can review [Youtube's fair use policies](http://www.youtube.com/t/copyright_permissions) and [submit a counter-notification](http://www.google.com/support/youtube/bin/answer.py?answer=155562) if you feel your video falls within [fair use](http://www.copyright.gov/fls/fl102.html).

= Q: Why can't I display more than 50 videos? =
A: The Youtube API enforces a limit of 50 results for users' upload feeds. Unless and until Youtube changes the limit, this plugin will be unable to display more than 50 videos at a time.

= Q: Why is it taking so long for my new videos to show up? =
A: First, try clicking the **Clear Cache Now** button on Youtube Feeder's settings page to make sure you are getting the most up-to-date information available. If that doesn't work, it is most likely a delay in the Youtube API.

Youtube's API is updated periodically throughout the day. As a result, uploaded videos won't be visible in the feed immediately. The [Youtube API reference](http://code.google.com/apis/youtube/2.0/reference.html#Overview) explains the delay. Here are some key points:

* Uploaded videos will be included in a user's public uploaded videos feed a few minutes after the upload completes and YouTube finishes processing the video.
* Uploaded videos will usually be available in search feeds within 30 minutes to two hours after the upload completes and YouTube finishes processing the video. However, this delay may be longer under heavy API server loads.

***To make sure your video shows up as soon as possible**, upload it as public initially*. Until the video is included in the Youtube API, this plugin will not be able to display it. 

= Q: When will Feature "X" or Release "Y" be ready? =
A: I write and maintain this plugin in my free time. I've given it to the WordPress community for free because I want to give back to such a great open source project and hopefully help others along the way. I don't have any deadlines or release dates for this project because it takes a backseat to my full-time job.

If you're waiting for a specific feature, you can hope I get some free time soon or consider making a donation (using the link under [Support](http://andrewensley.com/projects/youtube-feeder-wordpress-plugin/#support)). If I receive a donation for a specific feature, I will prioritize that first and get it out as soon as possible.

== Screenshots ==

1. Demo of the "minimal" style
2. Demo of the "playlist" style
3. Demo of the simple "list" style
4. The Settings page
5. Demo of all three widgets
6. Widget Settings
7. Thumbnail Widget Settings
8. Published Date Widget Settings

== Upgrade Notice ==

= 2.0.1 =
* From 2.0.0: fixed embed urls for secure sites. From 1.0.7: MAJOR UPDATE. New Widgets, playlist embeds, 'minimal' style, playback options, and more. See: <http://andrewensley.com/projects/youtube-feeder-wordpress-plugin/#changelog2.0.0>

= 2.0.0 =
* MAJOR UPDATE. New Widgets, playlist embeds, 'minimal' style, playback options, and more. See: <http://andrewensley.com/projects/youtube-feeder-wordpress-plugin/#changelog2.0.0>

= 1.0.7 =
* Fixed display of video descriptions.

= 1.0.6 =
* Fixed manual function call embeds always using default settings.

= 1.0.5 =
* Fixed video order bug.

= 1.0.4 =
* Fixed a couple bugs, added cURL support, and expanded feed limit to 50 videos.

= 1.0.3 =
* Added ability to embed the most recent thumbnail from a feed.

= 1.0.2 =
* Added ability to view videos fullscreen and fixed a bug with updating cache settings.
* BE SURE TO BACKUP ANY CHANGES TO style.css BEFORE UPGRADING

= 1.0.1 =
* Added ability to add thumbnails to the playlist style and fixed some bugs.

== Changelog ==

= 2.0.1 =
* Changed urls for embeded videos and thumbnails to be protocol agnostic, ensuring compatibility with secure sites.

= 2.0.0 =
* Added ability to embed user playlists.
* Added ability to embed global playlists.
* Added 'minimal' style (playlist embedded in a single player).
* Switched embed code to iframe embeds.
* Added all available video playback settings, such as showing related videos, autoplay, style options, etc.
* Added ability to hide video title in simple list format.
* Added Widgets.

= 1.0.7 =
* Updated to grab video description from new location in Youtube's data feed.

= 1.0.6 =
* Fixed bug causing manual function calls to always use the default admin settings. 

= 1.0.5 =
* Fixed order of video feed so that it is now in reverse-chronological order.

= 1.0.4 =
* Fixed the "Invalid argument supplied for foreach() on line 103" bug.
* Fixed the API call to only grab videos that are embeddable on public websites.
* Expanded number of possible results up to 50 instead of the default 25.  The Youtube API does not honor requests for more than 50.
* Added cURL as alternative to file_get_contents() for grabbing the feed.

= 1.0.3 =
* Added the ability to embed the most recent thumbnail from a feed.
* Added the ability to display the video description in "playlist" style and remove it in simple "list" style.
* Changed the default embed style to "playlist".
* Changed preview to show default youtube user (if available) instead of a static user.
* Fixed bug in get_youtube_published_date() function.
* Changed some of the default CSS classes.
* Changed URL for thumbnails.

= 1.0.2 =
* Added ability to view videos fullscreen.
* Fixed a bug with updating cache settings.

= 1.0.1 =
* Fixed bug with embed_youtube_feed() and get_youtube_published_date() raising warnings when no argument was supplied.
* Added ability to add thumbnails to "playlist" style.
* Added ability to clear the Youtube Feeder Cache.
* Added message pointing to this page for FAQ and Support to the top of the settings page.
* Changed name of main plugin file from 'youtube_feed.php' to 'youtube-feeder.php' for consistency.

= 1.0.0 =
* Initial release

== Features ==

* Retrieves and embeds the newest videos from Youtube video feeds automatically.  No more updating pages or fiddling with embed code.
* Displays player, title, date, and description for every video.
* Configurable video size.  Can be set site-wide or per video feed.
* Display styles completely customizable with simple tweaks to included CSS file.
* Completely configurable date format.
* Configurable caching of feeds to reduce server load and load time for visitors.  Can be handled site-wide or per video feed.
* Can simply list each video or create a player, allowing visitors to click on each video title, loading it dynamically into the single player.
* Configurable number of videos displayed in each feed.
* Dedicated function to get the published date of the most recent video in a feed.
* Dedicated function to get the thumbnail of the most recent video in a feed.

== Requirements ==

* Your server's PHP environment must have [cURL](http://php.net/manual/en/book.curl.php) support or PHP.ini must have [allow_url_fopen](http://www.php.net/manual/en/filesystem.configuration.php#ini.allow-url-fopen) set to "On"
* The [PHP JSON extension](http://www.php.net/manual/en/intro.json.php)

== License ==

This plugin is released under the GPLv3 license and comes with ABSOLUTELY NO WARRANTY, to the extent permitted by applicable law.  I make no guarantee this plugin will work for you.

== Support ==

For support, please visit the [plugin page](http://andrewensley.com/projects/youtube-feeder-wordpress-plugin/)
