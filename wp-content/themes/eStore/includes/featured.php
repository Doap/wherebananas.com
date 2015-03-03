<?php
	$arr = array();
	$i=1;

	$width = 1400;
	$height = 501;
	$width_small = 109;
	$height_small = 109;

	$using_woocommerce = false;

	$featured_cat = get_option('estore_feat_cat');
	$featured_num = (int) get_option('estore_featured_num');

	if ( 'false' == get_option( 'estore_use_pages' ) ) {
		if ( class_exists( 'woocommerce' ) ) {
			$et_featured_term = get_term_by( 'name', $featured_cat, 'product_cat' );
			$using_woocommerce = true;

			// show WooCommerce products from tabxonomy term set in ePanel
			$featured_query = new WP_Query(
				apply_filters( 'et_woocommerce_featured_args',
					array(
						'post_type' => 'product',
						'posts_per_page' => (int) $featured_num,
						'meta_query' => array(
							array( 'key' => '_visibility', 'value' => array( 'catalog', 'visible' ),'compare' => 'IN' )
						),
						'tax_query' => array(
							array(
								'taxonomy' 	=> 'product_cat',
								'field' 	=> 'id',
								'terms' 	=> (int) $et_featured_term->term_id
							)
						)
					)
				)
			);
		} else {
			// grab normal posts from selected category
			$featured_query = new WP_Query( "posts_per_page=$featured_num&cat=" . get_catId( $featured_cat ) );
		}
	} else {
		global $pages_number;

		if (get_option('estore_feat_pages') <> '') $featured_num = count(get_option('estore_feat_pages'));
		else $featured_num = $pages_number;

		$et_featured_pages_args = array(
			'post_type' => 'page',
			'orderby' => 'menu_order',
			'order' => 'DESC',
			'posts_per_page' => (int) $featured_num,
		);

		if ( is_array( et_get_option( 'estore_feat_pages', '', 'page' ) ) )
			$et_featured_pages_args['post__in'] = (array) array_map( 'intval', et_get_option( 'estore_feat_pages', '', 'page' ) );

		$featured_query = new WP_Query( $et_featured_pages_args );
	};

	while ( $featured_query->have_posts() ) : $featured_query->the_post();
		if ( $using_woocommerce )
			$wc_product = function_exists( 'get_product' ) ? get_product( get_the_ID() ) : new WC_Product( get_the_ID() );

		$arr[$i]["title"] = truncate_title(25,false);
		$arr[$i]["fulltitle"] = truncate_title(250,false);

		$arr[$i]["excerpt"] = truncate_post(250,false);

		$arr[$i]["permalink"] = get_permalink();

		$arr[$i]["thumbnail"] = get_thumbnail($width,$height,'',$arr[$i]["fulltitle"],$arr[$i]["fulltitle"],true,'featured_image');
		$arr[$i]["thumb"] = $arr[$i]["thumbnail"]["thumb"];

		$arr[$i]["thumbnail_small"] = get_thumbnail($width_small,$height_small,'',$arr[$i]["fulltitle"],$arr[$i]["fulltitle"]);
		$arr[$i]["thumb_small"] = $arr[$i]["thumbnail_small"]["thumb"];

		$arr[$i]["use_timthumb"] = $arr[$i]["thumbnail"]["use_timthumb"];

		$arr[$i]['post_id'] = (int) get_the_ID();

		$custom = '';
		$custom = get_post_custom( get_the_ID() );

		if ( $using_woocommerce ) {
			$arr[$i]["price"] = $wc_product->get_price_html();
		} else {
			$arr[$i]["price"] = isset($custom["price"][0]) ? $custom["price"][0] : '';

			if ($arr[$i]["price"] <> '') $arr[$i]["price"] = get_option('estore_cur_sign') . $arr[$i]["price"];
		}

		$arr[$i]["color"] = isset($custom["featured_bgcolor"][0]) ? $custom["featured_bgcolor"][0] : '969384';
		$arr[$i]["featured_alt_image"] = isset($custom["featured_image"][0]) ? $custom["featured_image"][0] : '';

		$i++;
	endwhile;
	wp_reset_postdata();
?>

<div id="featured">
	<div id="slides">
		<?php for ($i = 1; $i <= count( $arr ); $i++) { ?>
			<div class="slide<?php if ($i==1) echo(' active'); ?>" style="background: #<?php echo esc_attr($arr[$i]["color"]); ?> url('<?php
					if ( '' !== $arr[$i]["featured_alt_image"] ) {
						echo et_new_thumb_resize( et_multisite_thumbnail( $arr[$i]["featured_alt_image"] ), $width, $height, '', true );
					} else {
						print_thumbnail( array(
							'thumbnail' 	=> $arr[$i]["thumbnail"]["thumb"],
							'use_timthumb' 	=> $arr[$i]["thumbnail"]["use_timthumb"],
							'alttext'		=> $arr[$i]["fulltitle"],
							'width'			=> (int) $width,
							'height'		=> (int) $height,
							'echoout'		=> true,
							'forstyle'		=> true,
							'et_post_id'	=> $arr[$i]['post_id'],
						) );
					} ?>') no-repeat top center;">
				<div class="container">
					<div class="description">
						<div class="product">
							<?php if ($arr[$i]["price"] <> '') { ?>
								<span class="tag"><span><?php echo $arr[$i]["price"]; ?></span></span>
							<?php }; ?>
							<h2 class="title"><a href="<?php echo esc_url($arr[$i]["permalink"]); ?>"><?php echo esc_html($arr[$i]["fulltitle"]); ?></a></h2>
							<p><?php echo($arr[$i]["excerpt"]); ?></p>
							<a class="more" href="<?php echo esc_attr($arr[$i]["permalink"]); ?>"><span><?php esc_html_e('more info','eStore'); ?></span></a>
						</div> <!-- .product -->
					</div> <!-- .description -->
				</div> <!-- .container -->
			</div> <!-- .slide -->
		<?php }; ?>
	</div> <!-- #slides-->


	<div id="controllers">
		<div class="container">
			<div id="switcher">

				<?php for ($i = 1; $i <= count( $arr ); $i++) { ?>
					<div class="item<?php if ($i==1) echo(' active'); if ($i == $featured_num) echo(' last'); ?>">
						<a href="#" class="product">
						<?php
							print_thumbnail( array(
								'thumbnail' 	=> $arr[$i]["thumb_small"],
								'use_timthumb' 	=> $arr[$i]["use_timthumb"],
								'alttext'		=> $arr[$i]["fulltitle"],
								'width'			=> (int) $width_small,
								'height'		=> (int) $height_small,
								'et_post_id'	=> $arr[$i]['post_id'],
							) );
						?>
							<?php if ($arr[$i]["price"] <> '') { ?>
								<span class="tag"><span><?php echo $arr[$i]["price"]; ?></span></span>
							<?php }; ?>
						</a>
					</div> <!-- .item -->
				<?php }; ?>

			</div> <!-- #switcher -->
		</div> <!-- .container -->
	</div> <!-- #controllers -->

	<div id="top-shadow"></div>
	<div id="bottom-shadow"></div>

</div> <!-- end #featured -->