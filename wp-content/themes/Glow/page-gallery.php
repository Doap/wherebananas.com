<?php
/*
Template Name: Gallery Page
*/
?>
<?php
$et_ptemplate_settings = array();
$et_ptemplate_settings = maybe_unserialize( get_post_meta(get_the_ID(),'et_ptemplate_settings',true) );

$fullwidth = isset( $et_ptemplate_settings['et_fullwidthpage'] ) ? (bool) $et_ptemplate_settings['et_fullwidthpage'] : (bool) $et_ptemplate_settings['et_fullwidthpage'];

$gallery_cats = isset( $et_ptemplate_settings['et_ptemplate_gallerycats'] ) ? $et_ptemplate_settings['et_ptemplate_gallerycats'] : array();
$et_ptemplate_gallery_perpage = isset( $et_ptemplate_settings['et_ptemplate_gallery_perpage'] ) ? (int) $et_ptemplate_settings['et_ptemplate_gallery_perpage'] : 12;
?>

<?php get_header(); ?>
<div id="main-area-wrap">
	<div id="wrapper">
		<div id="main">
			<div class="post">
<div class="new-post">
<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
	<h1><?php the_title() ?></h1>
	<div id="post-content">

		<?php $width = (int) get_option('glow_thumbnail_width_pages');
			  $height = (int) get_option('glow_thumbnail_height_pages');
			  $classtext = 'thumbnail alignleft';
			  $titletext = get_the_title();

			  $thumbnail = get_thumbnail($width,$height,$classtext,$titletext,$titletext);
			  $thumb = $thumbnail["thumb"];  ?>

		<?php if($thumb <> '' && get_option('glow_page_thumbnails') == 'on') { ?>
			<?php print_thumbnail($thumb, $thumbnail["use_timthumb"], $titletext, $width, $height, $classtext); ?>
		<?php }; ?>

		<?php the_content(); ?>

		<div id="et_pt_gallery" class="clearfix">
			<?php $gallery_query = '';
			if ( !empty($gallery_cats) ) $gallery_query = '&cat=' . implode(",", $gallery_cats);
			else echo '<!-- gallery category is not selected -->'; ?>
			<?php
				$et_paged = is_front_page() ? get_query_var( 'page' ) : get_query_var( 'paged' );
			?>
			<?php query_posts("posts_per_page=$et_ptemplate_gallery_perpage&paged=" . $et_paged . $gallery_query); ?>
			<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

				<?php $width = 207;
				$height = 136;
				$titletext = get_the_title();

				$thumbnail = get_thumbnail($width,$height,'portfolio',$titletext,$titletext,true,'Portfolio');
				$thumb = $thumbnail["thumb"]; ?>

				<div class="et_pt_gallery_entry">
					<div class="et_pt_item_image">
						<?php print_thumbnail($thumb, $thumbnail["use_timthumb"], $titletext, $width, $height, 'portfolio'); ?>
						<span class="overlay"></span>

						<a class="zoom-icon fancybox" title="<?php the_title_attribute(); ?>" rel="gallery" href="<?php echo($thumbnail['fullpath']); ?>"><?php esc_html_e('Zoom in','Glow'); ?></a>
						<a class="more-icon" href="<?php the_permalink(); ?>"><?php esc_html_e('Read more','Glow'); ?></a>
					</div> <!-- end .et_pt_item_image -->
				</div> <!-- end .et_pt_gallery_entry -->

			<?php endwhile; ?>
				<div class="page-nav clearfix">
					<?php if(function_exists('wp_pagenavi')) { wp_pagenavi(); }
					else { ?>
					<div class="pagination">
					   <div class="alignleft"><?php next_posts_link(esc_html__('&laquo; Older Entries','Glow')) ?></div>
					   <div class="alignright"><?php previous_posts_link(esc_html__('Next Entries &raquo;', 'Glow')) ?></div>
					</div>
					<?php } ?>
				</div> <!-- end .entry -->
			<?php else : ?>
				<?php get_template_part('includes/no-results'); ?>
			<?php endif; wp_reset_query(); ?>

		</div> <!-- end #et_pt_gallery -->

		<?php edit_post_link(esc_html__('Edit this page','Glow')); ?>
		<div class="clear"></div>
	</div> <!-- end post-content -->
<?php endwhile; endif; ?>
</div> <!-- end new-post -->

			</div> <!-- end post -->
		</div> <!-- end main -->
<?php if (!$fullwidth) get_sidebar(); ?>
<?php get_footer(); ?>