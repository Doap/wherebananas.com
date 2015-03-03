<h3 id="deals-title"><span><?php esc_html_e('Deals Of The Day','eStore'); ?></span></h3>

<div id="scroller" class="clearfix">
	<a href="#" id="left-arrow"><?php esc_html_e('Previous','eStore'); ?></a>

	<div id="items">

	<?php
		$i = 0;
		$using_woocommerce = false;
		$dealsNum = (int) get_option( 'estore_deals_numposts' );

		if ( class_exists( 'woocommerce' ) ) {
			$using_woocommerce = true;

			// show WooCommerce products set as featured
			$deals_query = new WP_Query(
				apply_filters( 'et_woocommerce_deals_args',
					array(
						'post_type' => 'product',
						'posts_per_page' => (int) $dealsNum,
						'meta_query' => array(
							array( 'key' => '_visibility', 'value' => array( 'catalog', 'visible' ),'compare' => 'IN' ),
							array( 'key' => '_featured', 'value' => 'yes' )
						)
					)
				)
			);
		} else {
			$args = array(
				'posts_per_page' => $dealsNum,
				'cat' 		=> (int) get_catId( get_option( 'estore_deals_category' ) )
			);
			$deals_query = new WP_Query( $args );
		}

		if ( $deals_query->have_posts() ) : while ( $deals_query->have_posts() ) : $deals_query->the_post(); ?>
		<?php
			if ( $using_woocommerce )
				$wc_product = function_exists( 'get_product' ) ? get_product( get_the_ID() ) : new WC_Product( get_the_ID() );
		?>

		<?php
			if ( ($i % 4 == 0) || ($i == 0) ) {
				$div_closed = false;
				echo '<div class="block">';
			}
		?>

				<div class="item<?php if (($i+1) % 4 == 0) echo(' last'); ?>">
					<div class="item-top"></div>

					<div class="item-content">
					<?php
						global $post;

						$custom = '';
						$custom = get_post_custom($post->ID);

						if ( $using_woocommerce ) {
							$arr[$i]["price"] = $wc_product->get_price_html();
						} else {
							$arr[$i]["price"] = isset($custom["price"][0]) ? $custom["price"][0] : '';
							if ($arr[$i]["price"] <> '') $arr[$i]["price"] = get_option('estore_cur_sign') . $arr[$i]["price"];
						}

						$thumb = '';
						$width = 162;
						$height = 112;
						$classtext = '';
						$titletext = get_the_title();

						$thumbnail = get_thumbnail($width,$height,$classtext,$titletext,$titletext);
						$thumb = $thumbnail["thumb"];
					?>

						<?php if ($arr[$i]["price"] <> '') { ?>
							<span class="tag"><span><?php echo $arr[$i]["price"]; ?></span></span>
						<?php }; ?>

						<?php if ($thumb <> '') { ?>
							<?php print_thumbnail($thumb, $thumbnail["use_timthumb"], $titletext, $width, $height, $classtext); ?>
						<?php }; ?>
						<h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
					</div> <!-- .item-content -->

					<?php if ( $using_woocommerce ) woocommerce_show_product_sale_flash( $post, $wc_product ); ?>

					<a href="<?php the_permalink(); ?>" class="more"><span><?php esc_html_e('more info','eStore'); ?></span></a>
				</div> <!-- .item -->

		<?php
			if ( ($i+1) % 4 == 0 ) {
				$div_closed = true;
				echo '</div> <!-- end .block -->';
			}
		?>

			<?php $i++; ?>

		<?php endwhile; ?>
		<?php endif; wp_reset_postdata(); ?>

		<?php if ( $div_closed==false ) echo( '</div><!-- end .block-->' ); ?>

	</div> <!-- #items -->

	<a href="#" id="right-arrow"><?php esc_html_e('Next','eStore'); ?></a>
</div> <!-- #scroller -->

<div class="clear"></div>