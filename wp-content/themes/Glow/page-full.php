<?php
/*
Template Name: Full Width Page
*/
?>

<?php get_header(); ?>
<div id="main-area-wrap">
	<div id="wrapper">
		<div id="main">
			<div class="post">
<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
<div class="new-post">
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
		<?php edit_post_link(esc_html__('Edit this page','Glow')); ?>
		<div class="clear"></div>
	</div> <!-- end post-content -->
</div> <!-- end new-post -->
	<?php if (get_option('glow_show_pagescomments') == 'on') comments_template('', true); ?>
<?php endwhile; ?>

<?php else : ?>
	<!--If no results are found-->
	<div id="post-content">
		<h1><?php esc_html_e('No Results Found','Glow'); ?></h1>
		<p><?php esc_html_e('The page you requested could not be found. Try refining your search, or use the navigation above to locate the post.','Glow'); ?></p>
	</div>
	<!--End if no results are found-->
<?php endif; ?>
			</div> <!-- end post -->
		</div> <!-- end main -->

<?php get_footer(); ?>