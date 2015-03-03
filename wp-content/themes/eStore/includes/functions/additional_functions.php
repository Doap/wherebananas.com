<?php
if (class_exists('Walker_Nav_Menu')) {
	class description_walker extends Walker_Nav_Menu
	{
		function start_el(&$output, $item, $depth, $args) {
			global $wp_query;
			$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

			$class_names = $value = '';

			$classes = empty( $item->classes ) ? array() : (array) $item->classes;

			$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item ) );
			$class_names = ' class="'. esc_attr( $class_names ) . '"';

			$output .= $indent . '<li id="menu-item-'. $item->ID . '"' . $value . $class_names .'>';

			$attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
			$attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
			$attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
			$attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';

			$prepend = '<strong>';
			$append = '</strong>';

			$description  = ! empty( $item->description ) ? esc_attr( $item->description ) : '';
			if (strlen($description) > 22) $description = substr($description,0,21);

			if($depth != 0) { $description = $append = $prepend = ""; }

			$item_output = $args->before;
			$item_output .= '<a'. $attributes .'>';
			$item_output .= $args->link_before .$prepend.apply_filters( 'the_title', $item->title, $item->ID ).$append;
			$item_output .= '<span>' . $description. '</span>' . $args->link_after;
			$item_output .= '</a>';
			$item_output .= $args->after;

			$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
		}
	}
}

function estore_is_plugin_activated( $plugin_name ) {
	$currentPlugins = get_option( 'active_plugins' );

	$active = false;

	if ( 'eShop' === $plugin_name ) {
		$eshop_plugin = array( 'eshop/eshop.php', 'eshop.php' );
		foreach ( $eshop_plugin as $plugin ) {
			if ( in_array( $plugin, $currentPlugins ) )
				$active = true;
		}
	}

	if ( 'wpPayPal' === $plugin_name ) {
		$wp_paypal_plugin = array( 'wordpress-simple-paypal-shopping-cart/wp_shopping_cart.php', 'wp_shopping_cart.php' );
		foreach ( $wp_paypal_plugin as $plugin ) {
			if( in_array( $plugin, $currentPlugins ) )
				$active = true;
		}
	}

	return $active;
}

/* Meta boxes */

function estore_add_metabox_settings(){
	add_meta_box("et_post_meta", "ET Settings", "estore_display_options", "post", "normal", "high");
	add_meta_box("et_post_meta", "ET Settings", "estore_display_options", "page", "normal", "high");
	add_meta_box("et_post_meta", "ET Settings", "estore_display_options", "product", "normal", "high");
}
add_action("admin_init", "estore_add_metabox_settings");

function estore_display_options($callback_args) {
	global $post;

	$thumbs = array();
	$custom = get_post_custom($post->ID);

	$price = isset($custom["price"][0]) ? $custom["price"][0] : '';
	$shipping = isset($custom["shipping"][0]) ? $custom["shipping"][0] : '';
	$description =  isset($custom["description"][0]) ? $custom["description"][0] : '';
	$featured_bgcolor = isset($custom["featured_bgcolor"][0]) ? $custom["featured_bgcolor"][0] : '';
	$featured_image = isset($custom["featured_image"][0]) ? $custom["featured_image"][0] : '';

	$custom["thumbs"] = isset( $custom["thumbs"][0] ) ? unserialize($custom["thumbs"][0]) : '';
	$thumbnail1 =  isset($custom["thumbs"][0]) ? $custom["thumbs"][0] : '';
	$thumbnail2 =  isset($custom["thumbs"][1]) ? $custom["thumbs"][1] : '';
	$thumbnail3 =  isset($custom["thumbs"][2]) ? $custom["thumbs"][2] : '';
	$thumbnail4 =  isset($custom["thumbs"][3]) ? $custom["thumbs"][3] : '';

	$et_band =  isset($custom["et_band"][0]) ? $custom["et_band"][0] : '';
	if ($callback_args->post_type == 'page') $et_page_template =  isset($custom["et_page_template"][0]) ? $custom["et_page_template"][0] : '';

	wp_nonce_field( basename( __FILE__ ), 'et_settings_nonce' );
?>

	<?php if ($callback_args->post_type == 'page') { ?>
		<p style="margin-bottom: 22px;">
			<label for="et_page_template">Page Type</label>
			<select id="et_page_template" name="et_page_template">
				<option value="">Product Page</option>
				<option <?php if (htmlspecialchars($et_page_template) == 'usual') echo('selected="selected"')?> value="usual">Usual Page</option>
			</select>
		</p>
	<?php }; ?>

	<?php if ( 'product' != $callback_args->post_type ) { ?>
		<p style="margin-bottom: 22px;">
			<label for="et_price">Price</label>
			<input name="et_price" id="et_price" type="text" value="<?php echo esc_attr($price); ?>" size="20" />
			<small>(ex. 29.99)</small>
		</p>

		<?php if ( estore_is_plugin_activated( 'wpPayPal' ) ) { ?>
			<p style="margin-bottom: 22px;">
				<label for="et_shipping">Shipping</label>
				<input name="et_shipping" id="et_shipping" type="text" value="<?php echo esc_attr( $shipping ); ?>" size="20" />
				<small>(ex. 9.99. Use 0 or leave the field empty to disable shipping cost, use 0.001 to use base shipping cost only)</small>
			</p>
		<?php } ?>

	<?php } ?>

	<p style="margin-bottom: 22px;">
		<label for="et_description">Product Description: </label><br/>
		<textarea id="et_description" name="et_description" style="width: 90%"><?php echo esc_textarea($description); ?></textarea><br/>
		<small>(used on Single post pages)</small>
	</p>

	<p style="margin-bottom: 22px;">
		<label for="et_upload_image">Product Thumbnail #1: </label><br/>
		<input id="et_upload_image" type="text" size="90" name="et_upload_image" value="<?php echo esc_attr($thumbnail1); ?>" />
		<input class="upload_image_button" type="button" value="Upload Image" data-choose="Choose Product Thumbnail #1" data-update="Use For Product Thumbnail #1" /><br/>
		<small>(enter an URL or upload an image for the first Product Image)</small>
	</p>

	<p style="margin-bottom: 22px;">
		<label for="et_upload_image2">Product Thumbnail #2: </label><br/>
		<input id="et_upload_image2" type="text" size="90" name="et_upload_image2" value="<?php echo esc_attr($thumbnail2); ?>" />
		<input class="upload_image_button" type="button" value="Upload Image" data-choose="Choose Product Thumbnail #2" data-update="Use For Product Thumbnail #2" /><br/>
		<small>(enter an URL or upload an image for the second Product Image)</small>
	</p>

	<p style="margin-bottom: 22px;">
		<label for="et_upload_image3">Product Thumbnail #3: </label><br/>
		<input id="et_upload_image3" type="text" size="90" name="et_upload_image3" value="<?php echo esc_attr($thumbnail3); ?>" />
		<input class="upload_image_button" type="button" value="Upload Image" data-choose="Choose Product Thumbnail #3" data-update="Use For Product Thumbnail #3" /><br/>
		<small>(enter an URL or upload an image for the third Product Image)</small>
	</p>

	<p style="margin-bottom: 22px;">
		<label for="et_upload_image4">Product Thumbnail #4: </label><br/>
		<input id="et_upload_image4" type="text" size="90" name="et_upload_image4" value="<?php echo esc_attr($thumbnail4); ?>" />
		<input class="upload_image_button" type="button" value="Upload Image" data-choose="Choose Product Thumbnail #4" data-update="Use For Product Thumbnail #4" /><br/>
		<small>(enter an URL or upload an image for the fourth Product Image)</small>
	</p>

	<p style="margin-bottom: 22px;">
		<label for="et_featured_bgcolor">Featured Product Background Color</label>
		<input name="et_featured_bgcolor" id="et_featured_bgcolor" type="text" value="<?php echo esc_attr($featured_bgcolor); ?>" size="20" />
		<small>(ex. 969384)</small>
	</p>

	<p style="margin-bottom: 22px;">
		<label for="featured_image">Featured Product Alternative Image: </label><br/>
		<input id="featured_image" type="text" size="90" name="featured_image" value="<?php echo esc_attr($featured_image); ?>" />
		<input class="upload_image_button" type="button" value="Upload Image" data-choose="Choose Featured Product Alternative Image" data-update="Use For Featured Product Alternative Image" /><br/>
		<small>(enter an URL or upload an image to use as Featured Product Alternative Image)</small>
	</p>

	<p style="margin-bottom: 22px;">
		<?php $bands = array('onsale' => 'On Sale', 'buygetone' => 'Buy One Get One', 'outofstock' => 'Out Of Stock');
		$bands = apply_filters('et_bands',$bands); ?>

		<label for="et_band">Special Offer</label>
		<select id="et_band" name="et_band">
			<option value="">No</option>
			<?php foreach ($bands as $key => $value) { ?>
				<option <?php if (htmlspecialchars($et_band) == $key) echo('selected="selected"')?> value="<?php echo esc_attr($key); ?>"><?php echo esc_html($value); ?></option>
			<?php }; ?>
		</select>
	</p>
	<?php
}

function estore_save_details( $post_id, $post ){
	global $pagenow;

	if ( 'post.php' != $pagenow ) return $post_id;

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return $post_id;

	$post_type = get_post_type_object( $post->post_type );
	if ( ! current_user_can( $post_type->cap->edit_post, $post_id ) )
		return $post_id;

	if ( !isset( $_POST['et_settings_nonce'] ) || ! wp_verify_nonce( $_POST['et_settings_nonce'], basename( __FILE__ ) ) )
        return $post_id;

	if ( isset( $_POST["et_price"] ) && $_POST["et_price"] <> '' )
		update_post_meta( $post_id, "price", sanitize_text_field( $_POST["et_price"] ) );
	else
		delete_post_meta( $post_id, "price" );

	if ( isset( $_POST["et_shipping"] ) && $_POST["et_shipping"] <> '' )
		update_post_meta( $post_id, "shipping", sanitize_text_field( $_POST["et_shipping"] ) );
	else
		delete_post_meta( $post_id, "shipping" );

	if ( isset( $_POST["et_description"] ) && $_POST["et_description"] <> '' )
		update_post_meta( $post_id, "description", stripslashes( $_POST["et_description"] ) );
	else
		delete_post_meta( $post_id, "description" );

	if ( isset( $_POST["et_upload_image"] ) && $_POST["et_upload_image"] <> '' )
		$thumbs[] = esc_url_raw( $_POST["et_upload_image"] );
	if ( isset( $_POST["et_upload_image2"] ) && $_POST["et_upload_image2"] <> '' )
		$thumbs[] = esc_url_raw( $_POST["et_upload_image2"] );
	if ( isset( $_POST["et_upload_image3"] ) && $_POST["et_upload_image3"] <> '' )
		$thumbs[] = esc_url_raw( $_POST["et_upload_image3"] );
	if ( isset( $_POST["et_upload_image4"] ) && $_POST["et_upload_image4"] <> '' )
		$thumbs[] = esc_url_raw( $_POST["et_upload_image4"] );

	if ( ! empty( $thumbs ) )
		update_post_meta( $post_id, "thumbs", $thumbs );
	else
		delete_post_meta( $post_id, "thumbs" );

	if ( isset( $_POST["et_featured_bgcolor"] ) && $_POST["et_featured_bgcolor"] <> '' )
		update_post_meta( $post_id, "featured_bgcolor", sanitize_text_field( $_POST["et_featured_bgcolor"] ) );
	else
		delete_post_meta( $post_id, "featured_bgcolor" );

	if ( isset( $_POST["featured_image"] ) && $_POST["featured_image"] <> '' )
		update_post_meta( $post_id, "featured_image", esc_url_raw( $_POST["featured_image"] ) );
	else
		delete_post_meta( $post_id, "featured_image" );

	if ( isset( $_POST["et_band"] ) )
		update_post_meta( $post_id, "et_band", sanitize_text_field( $_POST["et_band"] ) );
	else
		delete_post_meta( $post_id, "et_band" );

	if ( isset( $_POST["et_page_template"] ) )
		update_post_meta( $post_id, "et_page_template", sanitize_text_field( $_POST["et_page_template"] ) );
	else
		delete_post_meta( $post_id, "et_page_template" );
}
add_action( 'save_post', 'estore_save_details', 10, 2 );

function estore_upload_scripts() {
	wp_enqueue_script('media-upload');
	wp_enqueue_script('thickbox');
	wp_register_script('my-upload', get_bloginfo('template_directory').'/js/custom_uploader.js', array('jquery','media-upload','thickbox'));
	wp_enqueue_script('my-upload');
}

function estore_upload_styles() {
	wp_enqueue_style('thickbox');
}

function et_estore_post_scripts_styles( $hook ) {
	global $typenow;

	if ( ! in_array( $hook, array( 'post-new.php', 'post.php' ) ) ) return;

	if ( isset( $typenow ) && in_array( $typenow, array( 'post', 'product' ) ) ) {
		estore_upload_scripts();
		estore_upload_styles();
	}
}
add_action( 'admin_enqueue_scripts', 'et_estore_post_scripts_styles', 10, 1 );

/* Plugin Integrations */

#WP Paypal Shopping Cart integration
function add_wppaypal($content) {
	#jQuery("input[name='addcart']").parents('object').css('display','none')
	global $post;
	$custom = get_post_custom($post->ID);
	$price = isset($custom["price"][0]) ? $custom["price"][0] : '';
	$shipping = isset($custom["shipping"][0]) ? $custom["shipping"][0] : '0';

	if ($price == '') $price = '19.99';
	$shipping = '0' !== $shipping ? ':shipping:' . $shipping : '';

	if ( is_single() || is_page() )
		$content = '[wp_cart:'. $post->post_title .':price:'. $price . $shipping .':end]' . $content;

	return $content;
};

function wppaypal_js(){
	echo('<script type="text/javascript">
			var form_object = jQuery("input[name=\'addcart\']").parent(\'form\');
			form_object.css(\'display\',\'none\');
			jQuery("a.addto-cart").click(function(){
				form_object.trigger("submit");
				return false;
			});
		</script>');
};

if ( estore_is_plugin_activated( 'wpPayPal' ) ) {
	add_filter('the_content','add_wppaypal');
	add_action('wp_footer','wppaypal_js');
}

#end WP Paypal Shopping Cart integration


#eStore integration
function add_eshop($content) {
	if (is_single() || is_page()) {
		if (do_shortcode('[eshop_addtocart]') <> '')
			$content = '<div class="dnone"><div id="examplePopup">[eshop_addtocart]</div></div>' . $content;
	}
	return $content;
};

function eshop_integrate_js(){
	echo('<script type="text/javascript">
			var form_object = jQuery("div.dnone");
			form_object.appendTo(".product-info .clearfix");

			jQuery("a.addto-cart").click(function(){
				form_object.slideToggle();
				return false;
			});
		</script>');
};

if ( estore_is_plugin_activated( 'eShop' ) ) {
	add_filter('the_content', 'add_eshop');
	remove_filter('the_content', 'eshop_boing');
	add_action('wp_footer','eshop_integrate_js');
}

#end eStore integration