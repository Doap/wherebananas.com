<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive.
 *
 * Override this template by copying it to yourtheme/woocommerce/archive-product.php
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

get_header('shop'); ?>
	<?php
		/**
		 * woocommerce_before_main_content hook
		 *
		 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
		 * @hooked woocommerce_breadcrumb - 20
		 */
		do_action('woocommerce_before_main_content');
	?>

		<h1 class="page-title"><?php woocommerce_page_title(); ?></h1>

		<?php do_action( 'woocommerce_archive_description' ); ?>

<div id="main-area">
	<div id="main-content" class="clearfix">
		<div id="left-column">
			<ul class="products">

			<?php if ( have_posts() ) : ?>

				<div class="et_page_meta_info clearfix">
				<?php
					/**
					 * woocommerce_before_shop_loop hook
					 *
					 * @hooked woocommerce_result_count - 20
					 * @hooked woocommerce_catalog_ordering - 30
					 */
					do_action( 'woocommerce_before_shop_loop' );
				?>
				</div> <!-- .et_page_meta_info -->

				<?php woocommerce_product_loop_start(); ?>

					<?php woocommerce_product_subcategories(); ?>

					<?php $i = 1; ?>

					<?php while ( have_posts() ) : the_post(); ?>

					<?php
						global $post;
						$using_woocommerce = false;

						$thumb = '';
						$width = 193;
						$height = 130;
						$classtext = '';
						$titletext = get_the_title();

						$thumbnail = get_thumbnail($width,$height,$classtext,$titletext,$titletext,true);
						$thumb = $thumbnail["thumb"];

						if ( class_exists( 'woocommerce' ) ) {
							$using_woocommerce = true;

							$wc_product = function_exists( 'get_product' ) ? get_product( get_the_ID() ) : new WC_Product( get_the_ID() );
						}

						$custom = get_post_custom($post->ID);

						if ( $using_woocommerce ) {
							$price = $wc_product->get_price_html();
						} else {
							$price = isset($custom["price"][0]) ? $custom["price"][0] : '';
							if ($price <> '') $price = get_option('estore_cur_sign') . $price;
						}

						$et_band = isset($custom["et_band"][0]) ? 'et_' . $custom["et_band"][0] : '';
					?>

						<li class="product<?php if ($i % 3 == 0) echo(' last'); ?>">
							<div class="product-content clearfix">
								<a href="<?php the_permalink(); ?>" class="image">
									<span class="rounded" style="background: url('<?php print_thumbnail($thumb, $thumbnail["use_timthumb"], $titletext, $width, $height, $classtext, true, true); ?>') no-repeat;"></span>
									<?php if ($price <> '') { ?>
										<span class="tag"><span><?php echo $price; ?></span></span>
									<?php }; ?>
								</a>

								<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
								<p><?php truncate_post(115); ?></p>
								<?php do_action( 'woocommerce_after_shop_loop_item' ); ?>
								<a href="<?php the_permalink(); ?>" class="more"><span><?php esc_html_e('more info','eStore'); ?></span></a>

								<?php if ($et_band <> '') { ?>
									<span class="band<?php echo(' '. esc_attr($et_band)); ?>"></span>
								<?php }; ?>

								<?php if ( $using_woocommerce ) woocommerce_show_product_sale_flash( $post, $wc_product ); ?>
							</div> <!-- .product-content -->
						</li> <!-- .product -->

						<?php if ($i % 3 == 0) echo('<div class="clear"></div>'); ?>
						<?php $i++; ?>

					<?php endwhile; // end of the loop. ?>
			</ul>
			<?php woocommerce_product_loop_end(); ?>

			<?php
				/**
				 * woocommerce_after_shop_loop hook
				 *
				 * @hooked woocommerce_pagination - 10
				 */
				do_action( 'woocommerce_after_shop_loop' );
			?>

		<?php elseif ( ! woocommerce_product_subcategories( array( 'before' => woocommerce_product_loop_start( false ), 'after' => woocommerce_product_loop_end( false ) ) ) ) : ?>

			<?php woocommerce_get_template( 'loop/no-products-found.php' ); ?>

		<?php endif; ?>

		<div class="clear"></div>

		<div id="et_archive_pagination" class="clearfix">

		</div> <!-- #et_archive_pagination -->

	<?php
		/**
		 * woocommerce_after_main_content hook
		 *
		 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
		 */
		//do_action('woocommerce_after_main_content');
	?>

		</div> <!-- #left-column -->

	<?php
		/**
		 * woocommerce_sidebar hook
		 *
		 * @hooked woocommerce_get_sidebar - 10
		 */
		do_action('woocommerce_sidebar');
	?>

<?php get_footer('shop'); ?>