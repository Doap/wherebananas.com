<?php
add_action( 'after_setup_theme', 'et_setup_theme' );
if ( ! function_exists( 'et_setup_theme' ) ){
	function et_setup_theme(){
		global $themename, $shortname, $default_colorscheme;
		$themename = "eStore";
		$shortname = "estore";
		$default_colorscheme = 'Default';

		$template_dir = get_template_directory();

		require_once($template_dir . '/epanel/custom_functions.php');

		require_once($template_dir . '/includes/functions/sidebars.php');

		load_theme_textdomain('eStore',$template_dir.'/lang');

		require_once($template_dir . '/epanel/core_functions.php');

		require_once($template_dir . '/epanel/post_thumbnails_estore.php');

		include($template_dir . '/includes/widgets.php');

		require_once($template_dir . '/includes/functions/additional_functions.php');

		add_action( 'pre_get_posts', 'et_home_posts_query' );

		add_action( 'et_epanel_changing_options', 'et_delete_featured_ids_cache' );
		add_action( 'delete_post', 'et_delete_featured_ids_cache' );
		add_action( 'save_post', 'et_delete_featured_ids_cache' );

		add_action( 'wp_enqueue_scripts', 'et_load_scripts_styles' );

		add_theme_support( 'woocommerce' );

		add_action( 'body_class', 'et_add_woocommerce_class_to_homepage' );

		// take breadcrumbs out of .container
		remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0 );
		// woocommerce_breadcrumb function is overwritten in functions.php
		add_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 9, 0 );

		remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
	}
}

function et_load_scripts_styles() {
	global $shortname;

	// load Raleway from Google Fonts
	$protocol = is_ssl() ? 'https' : 'http';
	$query_args = array(
		'family' => 'Raleway:400,300,200'
	);
	wp_enqueue_style( 'estore-fonts', add_query_arg( $query_args, "$protocol://fonts.googleapis.com/css" ), array(), null );

	$et_prefix = ! et_options_stored_in_one_row() ? "{$shortname}_" : '';
	$heading_font_option_name = "{$et_prefix}heading_font";
	$body_font_option_name = "{$et_prefix}body_font";

	$et_gf_enqueue_fonts = array();
	$et_gf_heading_font = sanitize_text_field( et_get_option( $heading_font_option_name, 'none' ) );
	$et_gf_body_font = sanitize_text_field( et_get_option( $body_font_option_name, 'none' ) );

	if ( 'none' != $et_gf_heading_font ) $et_gf_enqueue_fonts[] = $et_gf_heading_font;
	if ( 'none' != $et_gf_body_font ) $et_gf_enqueue_fonts[] = $et_gf_body_font;

	if ( ! empty( $et_gf_enqueue_fonts ) ) et_gf_enqueue_fonts( $et_gf_enqueue_fonts );
}

// overwrite woocommerce_breadcrumb function to change wrap_before and wrap_after arguments
function woocommerce_breadcrumb( $args = array() ) {
	$defaults = array(
		'delimiter'  => ' &rsaquo; ',
		'wrap_before'  => '<div id="breadcrumbs" itemprop="breadcrumb">',
		'wrap_after' => '</div>',
		'before'   => '',
		'after'   => '',
		'home'    => null
	);

	$args = wp_parse_args( $args, $defaults  );

	if ( function_exists( 'WC' ) ) {
		wc_get_template( 'global/breadcrumb.php', $args );
	} else {
		woocommerce_get_template( 'shop/breadcrumb.php', $args );
	}
}

add_action('wp_head','et_portfoliopt_additional_styles',100);
function et_portfoliopt_additional_styles(){ ?>
	<style type="text/css">
		#et_pt_portfolio_gallery { margin-left: -41px; }
		.et_pt_portfolio_item { margin-left: 31px; }
		.et_portfolio_small { margin-left: -40px !important; }
		.et_portfolio_small .et_pt_portfolio_item { margin-left: 29px !important; }
		.et_portfolio_large { margin-left: -24px !important; }
		.et_portfolio_large .et_pt_portfolio_item { margin-left: 4px !important; }
	</style>
<?php }

function register_main_menus() {
	register_nav_menus(
		array(
			'primary-menu' => __( 'Primary Menu' ),
			'secondary-menu' => __( 'Secondary Menu' )
		)
	);
};
if (function_exists('register_nav_menus')) add_action( 'init', 'register_main_menus' );

/**
 * Gets featured posts IDs from transient, if the transient doesn't exist - runs the query and stores IDs
 */
function et_get_featured_posts_ids(){
	if ( false === ( $et_featured_post_ids = get_transient( 'et_featured_post_ids' ) ) ) {
		if ( class_exists( 'woocommerce' ) ) {
			// show WooCommerce products from taxonomy term set in ePanel
			$featured_cat = get_option('estore_feat_cat');
			$featured_num = get_option('estore_featured_num');
			$et_featured_term = get_term_by( 'name', $featured_cat, 'product_cat' );

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
			$featured_query = new WP_Query( apply_filters( 'et_featured_post_args', array(
				'posts_per_page'	=> (int) et_get_option( 'estore_featured_num' ),
				'cat'				=> (int) get_catId( et_get_option( 'estore_feat_cat' ) )
			) ) );
		}

		if ( $featured_query->have_posts() ) {
			while ( $featured_query->have_posts() ) {
				$featured_query->the_post();

				$et_featured_post_ids[] = get_the_ID();
			}

			set_transient( 'et_featured_post_ids', $et_featured_post_ids );
		}

		wp_reset_postdata();
	}

	return $et_featured_post_ids;
}

/**
 * Filters the main query on homepage
 */
function et_home_posts_query( $query = false ) {
	/* Don't proceed if it's not homepage or the main query */
	if ( ! is_home() || ! is_a( $query, 'WP_Query' ) || ! $query->is_main_query() ) return;

	/* Set the amount of posts per page on homepage */
	$query->set( 'posts_per_page', et_get_option( 'estore_homepage_posts', '6' ) );

	/* Exclude slider posts, if the slider is activated, pages are not featured and posts duplication is disabled in ePanel  */
	if ( 'on' == et_get_option( 'estore_featured', 'on' ) && 'false' == et_get_option( 'estore_use_pages', 'false' ) && 'false' == et_get_option( 'estore_duplicate', 'on' ) )
		$query->set( 'post__not_in', et_get_featured_posts_ids() );

	/* Exclude categories set in ePanel */
	$exclude_categories = et_get_option( 'estore_exlcats_recent', false );

	if ( ! class_exists( 'woocommerce' ) ) {
		if ( $exclude_categories ) $query->set( 'category__not_in', array_map( 'intval', et_generate_wpml_ids( $exclude_categories, 'category' ) ) );
	} else {
		/* Display WooCommerce products on homepage */
		$query->set( 'post_type', 'product' );
		$query->set( 'meta_query', array(
				array( 'key' => '_visibility', 'value' => array( 'catalog', 'visible' ),'compare' => 'IN' )
			)
		);

		if ( $exclude_categories ) {
			$query->set( 'tax_query', array(
					array(
						'taxonomy' 	=> 'product_cat',
						'field' 	=> 'id',
						'operator'	=> 'NOT IN',
						'terms'		=> (array) $exclude_categories
					)
				)
			);
		}
	}
}

/**
 * Deletes featured posts IDs transient, when the user saves, resets ePanel settings, creates or moves posts to trash in WP-Admin
 */
function et_delete_featured_ids_cache(){
	if ( false !== get_transient( 'et_featured_post_ids' ) ) delete_transient( 'et_featured_post_ids' );
}

if ( ! function_exists( 'et_list_pings' ) ){
	function et_list_pings($comment, $args, $depth) {
		$GLOBALS['comment'] = $comment; ?>
		<li id="comment-<?php comment_ID(); ?>"><?php comment_author_link(); ?> - <?php comment_excerpt(); ?>
	<?php }
}

function et_epanel_custom_colors_css(){
	global $shortname; ?>

	<style type="text/css">
		body { color: #<?php echo esc_html(get_option($shortname.'_color_mainfont')); ?>; }
		body { background-color: #<?php echo esc_html(get_option($shortname.'_color_bgcolor')); ?>; }
		.post a:link, .post a:visited { color: #<?php echo esc_html(get_option($shortname.'_color_mainlink')); ?>; }
		ul.nav li a { color: #<?php echo esc_html(get_option($shortname.'_color_pagelink')); ?>; }
		#sidebar h3 { color:#<?php echo esc_html(get_option($shortname.'_color_sidebar_titles')); ?>; }
		#footer, p#copyright { color:#<?php echo esc_html(get_option($shortname.'_color_footer')); ?> !important; }
		#footer a { color:#<?php echo esc_html(get_option($shortname.'_color_footer_links')); ?> !important; }
	</style>
<?php
}

function et_add_woocommerce_class_to_homepage( $classes ) {
	if ( is_home() ) $classes[] = 'woocommerce';

	return $classes;
}

if ( function_exists( 'get_custom_header' ) ) {
	// compatibility with versions of WordPress prior to 3.4

	add_action( 'customize_register', 'et_estore_customize_register' );
	function et_estore_customize_register( $wp_customize ) {
		$google_fonts = et_get_google_fonts();

		$font_choices = array();
		$font_choices['none'] = 'Default Theme Font';
		foreach ( $google_fonts as $google_font_name => $google_font_properties ) {
			$font_choices[ $google_font_name ] = $google_font_name;
		}

		$wp_customize->remove_section( 'title_tagline' );
		$wp_customize->remove_section( 'background_image' );
		$wp_customize->remove_section( 'colors' );

		$wp_customize->add_section( 'et_google_fonts' , array(
			'title'		=> __( 'Fonts', 'eStore' ),
			'priority'	=> 50,
		) );

		$wp_customize->add_setting( 'estore_heading_font', array(
			'default'		=> 'none',
			'type'			=> 'option',
			'capability'	=> 'edit_theme_options'
		) );

		$wp_customize->add_control( 'estore_heading_font', array(
			'label'		=> __( 'Header Font', 'eStore' ),
			'section'	=> 'et_google_fonts',
			'settings'	=> 'estore_heading_font',
			'type'		=> 'select',
			'choices'	=> $font_choices
		) );

		$wp_customize->add_setting( 'estore_body_font', array(
			'default'		=> 'none',
			'type'			=> 'option',
			'capability'	=> 'edit_theme_options'
		) );

		$wp_customize->add_control( 'estore_body_font', array(
			'label'		=> __( 'Body Font', 'eStore' ),
			'section'	=> 'et_google_fonts',
			'settings'	=> 'estore_body_font',
			'type'		=> 'select',
			'choices'	=> $font_choices
		) );
	}

	add_action( 'wp_head', 'et_estore_add_customizer_css' );
	add_action( 'customize_controls_print_styles', 'et_estore_add_customizer_css' );
	function et_estore_add_customizer_css(){ ?>
		<style type="text/css">
		<?php
			global $shortname;

			$et_prefix = ! et_options_stored_in_one_row() ? "{$shortname}_" : '';
			$heading_font_option_name = "{$et_prefix}heading_font";
			$body_font_option_name = "{$et_prefix}body_font";

			$et_gf_heading_font = sanitize_text_field( et_get_option( $heading_font_option_name, 'none' ) );
			$et_gf_body_font = sanitize_text_field( et_get_option( $body_font_option_name, 'none' ) );

			if ( 'none' != $et_gf_heading_font || 'none' != $et_gf_body_font ) :

				if ( 'none' != $et_gf_heading_font )
					et_gf_attach_font( $et_gf_heading_font, 'h1, h2, h3, h4, h5, h6, .description h2.title, .item-content h4, .product h3, .post h1, .post h2, .post h3, .post h4, .post h5, .post h6, .related-items span, .page-title, .product_title' );

				if ( 'none' != $et_gf_body_font )
					et_gf_attach_font( $et_gf_body_font, 'body' );

			endif;
		?>
		</style>
	<?php }

	add_action( 'customize_controls_print_footer_scripts', 'et_load_google_fonts_scripts' );
	function et_load_google_fonts_scripts() {
		wp_enqueue_script( 'et_google_fonts', get_template_directory_uri() . '/epanel/google-fonts/et_google_fonts.js', array( 'jquery' ), '1.0', true );
		wp_localize_script( 'et_google_fonts', 'et_google_fonts', array(
			'options_in_one_row' => ( et_options_stored_in_one_row() ? 1 : 0 )
		) );
	}

	add_action( 'customize_controls_print_styles', 'et_load_google_fonts_styles' );
	function et_load_google_fonts_styles() {
		wp_enqueue_style( 'et_google_fonts_style', get_template_directory_uri() . '/epanel/google-fonts/et_google_fonts.css', array(), null );
	}
}