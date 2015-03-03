<?php
	add_theme_support( 'post-thumbnails' );

	global $et_theme_image_sizes;

	$et_thumb_size_format = '%dx%d';
	$et_post_thumb = sprintf( $et_thumb_size_format,
		intval( get_option( 'glow_thumbnail_width_posts', 172 ) ),
		intval( get_option( 'glow_thumbnail_height_posts', 172 ) )
	);
	$et_page_thumb = sprintf( $et_thumb_size_format,
		intval( get_option( 'glow_thumbnail_width_pages', 172 ) ),
		intval( get_option( 'glow_thumbnail_height_pages', 172 ) )
	);

	$et_theme_image_sizes = array(
		$et_post_thumb	=> 'et-posts-thumb',
		$et_page_thumb	=> 'et-pages-thumb',
		'630x298' 	=> 'et-featured-big-thumb',
		'67x67' 	=> 'et-featured-small-thumb',
		'156x156' 	=> 'et-blogstyle-thumb',
		'65x65' 	=> 'et-default-thumb',
		'38x38' 	=> 'et-widget-tabbed-thumb',
	);

	$et_page_templates_image_sizes = array(
		'184x184' 	=> 'et-blog-page-thumb',
		'207x136' 	=> 'et-gallery-page-thumb',
		'260x170' 	=> 'et-portfolio-medium-page-thumb',
		'260x315' 	=> 'et-portfolio-medium-portrait-page-thumb',
		'140x94' 	=> 'et-portfolio-small-page-thumb',
		'140x170' 	=> 'et-portfolio-small-portrait-page-thumb',
		'430x283' 	=> 'et-portfolio-large-page-thumb',
		'430x860' 	=> 'et-portfolio-large-portrait-page-thumb',
	);

	$et_theme_image_sizes = array_merge( $et_theme_image_sizes, $et_page_templates_image_sizes );

	$et_theme_image_sizes = apply_filters( 'et_theme_image_sizes', $et_theme_image_sizes );
	$crop = apply_filters( 'et_post_thumbnails_crop', true );

	if ( is_array( $et_theme_image_sizes ) ){
		foreach ( $et_theme_image_sizes as $image_size_dimensions => $image_size_name ){
			$dimensions = explode( 'x', $image_size_dimensions );
			add_image_size( $image_size_name, $dimensions[0], $dimensions[1], $crop );
		}
	}
?>