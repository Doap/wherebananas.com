<?php
/**
 * The template for displaying product content in the single-product.php template
 *
 * Override this template by copying it to yourtheme/woocommerce/content-single-product.php
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<?php
	$custom = get_post_custom( get_the_ID() );
	$et_band =  isset($custom["et_band"][0]) ? 'et_' . $custom["et_band"][0] : '';
?>

<div class="post clearfix">
<?php
	/**
	 * woocommerce_before_single_product hook
	 *
	 * @hooked woocommerce_show_messages - 10
	 */
	 do_action( 'woocommerce_before_single_product' );
?>

<?php if ($et_band <> '') { ?>
	<span class="band<?php echo(' '.esc_attr($et_band)); ?>"></span>
<?php }; ?>

	<div itemscope itemtype="http://schema.org/Product" id="product-<?php the_ID(); ?>" <?php post_class(); ?>>
<?php
$wc_product = function_exists( 'get_product' ) ? get_product( get_the_ID() ) : new WC_Product( get_the_ID() );
$et_price = $wc_product->get_price_html();

$thumb = '';
$width = 510;
$height = 510;
$classtext = '';
$titletext = get_the_title();

$thumbnail = get_thumbnail( $width, $height, $classtext, $titletext, $titletext, true );
$thumb = $thumbnail["thumb"];

$custom["thumbs"] = isset( $custom["thumbs"][0] ) ? unserialize($custom["thumbs"][0]) : ''; ?>


<?php if (!empty($custom["thumbs"])) { ?>
	<div id="product-slider">
		<div id="product-slides">
			<?php for ($i = 0; $i <= count($custom["thumbs"])-1; $i++) { ?>
				<div class="item-slide">
				    <a href="<?php echo($custom["thumbs"][$i]); ?>" rel="gallery" class="fancybox">
						<?php echo et_new_thumb_resize( et_multisite_thumbnail($custom["thumbs"][$i]), 298, 226 ); ?>
						<span class="overlay"></span>
				   </a>
				</div> <!-- .item-slide -->
			<?php }; ?>

			<?php woocommerce_show_product_sale_flash( $post, $wc_product ); ?>
		</div> <!-- #product-slides -->

			<?php if (count($custom["thumbs"]) > 1) { ?>
				<div id="product-thumbs">
					<?php for ($i = 0; $i <= count($custom["thumbs"])-1; $i++) { ?>
						<a href="#" <?php if($i==0) echo('class="active"'); if ($i==count($custom["thumbs"])-1) echo('class="last"') ?> rel="<?php echo($i+1); ?>">
							<?php echo et_new_thumb_resize( et_multisite_thumbnail($custom["thumbs"][$i]), 69, 69 ); ?>
							<span class="overlay"></span>
						</a>
					<?php }; ?>
				</div> <!-- #product-thumbs -->
			<?php }; ?>
	</div> <!-- #product-slider -->
<?php } elseif ( '' != $thumb ) { ?>
	<div id="product-slider">
		<div id="product-slides">
			<div class="item-slide images">
			<?php
				printf( '<a class="woocommerce-main-image zoom" itemprop="image" href="%1$s" title="%2$s" data-o_href="%1$s">',
					esc_url( $thumbnail['fullpath'] ),
					esc_attr( $titletext )
				);
				print_thumbnail( $thumb, $thumbnail["use_timthumb"], $titletext, $width, $height, $classtext );

				echo '</a>';

				woocommerce_show_product_sale_flash( $post, $wc_product );
			?>
			</div> <!-- .item-class -->
		</div> <!-- #product-slides -->

		<?php do_action('woocommerce_product_thumbnails'); ?>
	</div> <!-- #product-slider -->
<?php } ?>


		<?php
			/**
			 * woocommerce_show_product_images hook
			 *
			 * @hooked woocommerce_show_product_sale_flash - 10
			 * @hooked woocommerce_show_product_images - 20
			 */
			//do_action( 'woocommerce_before_single_product_summary' );
		?>

		<div class="summary">

			<?php
				/**
				 * woocommerce_single_product_summary hook
				 *
				 * @hooked woocommerce_template_single_title - 5
				 * @hooked woocommerce_template_single_price - 10
				 * @hooked woocommerce_template_single_excerpt - 20
				 * @hooked woocommerce_template_single_add_to_cart - 30
				 * @hooked woocommerce_template_single_meta - 40
				 * @hooked woocommerce_template_single_sharing - 50
				 */
				do_action( 'woocommerce_single_product_summary' );
			?>

		</div><!-- .summary -->

		<?php
			/**
			 * woocommerce_after_single_product_summary hook
			 *
			 * @hooked woocommerce_output_product_data_tabs - 10
			 * @hooked woocommerce_output_related_products - 20
			 */
			do_action( 'woocommerce_after_single_product_summary' );
		?>

	</div><!-- #product-<?php the_ID(); ?> -->

	<?php do_action( 'woocommerce_after_single_product' ); ?>
</div><!-- .post -->