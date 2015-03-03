<?php get_header(); ?>

<?php get_template_part('includes/breadcrumbs'); ?>

<div id="main-area">
	<div id="main-content" class="clearfix">
		<div id="left-column">
			<?php if (get_option('estore_integration_single_top') <> '' && get_option('estore_integrate_singletop_enable') == 'on') echo(get_option('estore_integration_single_top')); ?>
		<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
			<div class="post clearfix">

			<?php
				if ( ! class_exists( 'woocommerce' ) ) {
					get_template_part( 'includes/single-product' );
				} else {
					echo '<h1 class="title">' . get_the_title() . '</h1>';
					the_content();
					echo '<div class="clear"></div>';
					wp_link_pages(array('before' => '<p><strong>'.esc_html__('Pages','eStore').':</strong> ', 'after' => '</p>', 'next_or_number' => 'number'));
					edit_post_link(esc_html__('Edit this page','eStore'));
				}
			?>

			</div> <!-- end .post -->
		<?php endwhile; endif; ?>
			<?php if (get_option('estore_integration_single_bottom') <> '' && get_option('estore_integrate_singlebottom_enable') == 'on') echo(get_option('estore_integration_single_bottom')); ?>

			<?php if (get_option('estore_468_enable') == 'on') { ?>
				<?php if(get_option('estore_468_adsense') <> '') echo(get_option('estore_468_adsense'));
				else { ?>
					<a href="<?php echo esc_url(get_option('estore_468_url')); ?>"><img src="<?php echo esc_attr(get_option('estore_468_image')); ?>" alt="468 ad" class="foursixeight" /></a>
				<?php } ?>
			<?php } ?>

		</div> <!-- #left-column -->

		<?php get_sidebar(); ?>

<?php get_footer(); ?>