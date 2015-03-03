<?php
/*
Template Name: Sitemap Page
*/
?>
<?php
$et_ptemplate_settings = array();
$et_ptemplate_settings = maybe_unserialize( get_post_meta(get_the_ID(),'et_ptemplate_settings',true) );

$fullwidth = isset( $et_ptemplate_settings['et_fullwidthpage'] ) ? (bool) $et_ptemplate_settings['et_fullwidthpage'] : false;
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

		<div id="sitemap">
			<div class="sitemap-col">
				<h2><?php esc_html_e('Pages','Glow'); ?></h2>
				<ul id="sitemap-pages"><?php wp_list_pages('title_li='); ?></ul>
			</div> <!-- end .sitemap-col -->

			<div class="sitemap-col">
				<h2><?php esc_html_e('Categories','Glow'); ?></h2>
				<ul id="sitemap-categories"><?php wp_list_categories('title_li='); ?></ul>
			</div> <!-- end .sitemap-col -->

			<div class="sitemap-col">
				<h2><?php esc_html_e('Tags','Glow'); ?></h2>
				<ul id="sitemap-tags">
					<?php $tags = get_tags();
					if ($tags) {
						foreach ($tags as $tag) {
							echo '<li><a href="' . esc_url( get_tag_link( $tag->term_id ) ) . '">' . esc_html( $tag->name ) . '</a></li> ';
						}
					} ?>
				</ul>
			</div> <!-- end .sitemap-col -->

			<div class="sitemap-col<?php echo ' last'; ?>">
				<h2><?php esc_html_e('Authors','Glow'); ?></h2>
				<ul id="sitemap-authors" ><?php wp_list_authors('show_fullname=1&optioncount=1&exclude_admin=0'); ?></ul>
			</div> <!-- end .sitemap-col -->
		</div> <!-- end #sitemap -->

		<div class="clear"></div>

		<?php edit_post_link(esc_html__('Edit this page','Glow')); ?>
		<div class="clear"></div>
	</div> <!-- end post-content -->
<?php endwhile; endif; ?>
</div> <!-- end new-post -->

			</div> <!-- end post -->
		</div> <!-- end main -->
<?php if (!$fullwidth) get_sidebar(); ?>
<?php get_footer(); ?>