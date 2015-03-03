<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
<head profile="http://gmpg.org/xfn/11">
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
<title><?php elegant_titles(); ?></title>
<?php elegant_description(); ?>
<?php elegant_keywords(); ?>
<?php elegant_canonical(); ?>

<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen" />
<!--[if lt IE 7]>
	<link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/ie6style.css" />
	<script type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/js/DD_belatedPNG_0.0.8a-min.js"></script>
	<script type="text/javascript">DD_belatedPNG.fix('#page-menu ul, ul#page-menu li li,#page-menu li li a:hover, ul#page-menu li.current_page_item li a:hover, #cats-menu ul, div#container-top, #featured-area .pics span.feat-overlay, #featured-area .pics div.excerpt, .new-post div.date, .mainpost-wrap div.date, div#footer, ul#cats-menu li li a, .bubble,.reply-container');</script>
	<script type="text/javascript">
		var clear="<?php echo get_template_directory_uri(); ?>/images/clear.gif"
	</script>
	<script type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/js/unitpngfix.js"></script>
<![endif]-->
<!--[if IE 7]>
	<link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/ie7style.css" />
<![endif]-->

<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />

<script type="text/javascript">
	document.documentElement.className = 'js';
</script>

<?php if ( is_singular() ) wp_enqueue_script( 'comment-reply' ); ?>
<?php wp_head(); ?>

</head>
<body <?php if (is_home()) echo('id="home"');?> <?php body_class(); ?>>
	<div id="header">
		<div class="container">
			<!-- Page Menu -->

			<?php $menuClass = 'nav superfish';
			$menuID = 'page-menu';
			$primaryNav = '';
			if (function_exists('wp_nav_menu')) {
				$primaryNav = wp_nav_menu( array( 'theme_location' => 'primary-menu', 'container' => '', 'fallback_cb' => '', 'menu_class' => $menuClass, 'menu_id' => $menuID, 'echo' => false ) );
			};
			if ($primaryNav == '') { ?>
				<ul id="<?php echo esc_attr( $menuID ); ?>" class="<?php echo esc_attr( $menuClass ); ?>">
					<?php if (get_option('glow_swap_navbar') == 'false') { ?>
						<?php if (get_option('glow_home_link') == 'on') { ?>
							<li <?php if (is_home()) echo('class="current_page_item"') ?>><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e('Home','Glow'); ?></a></li>
						<?php }; ?>

						<?php show_page_menu($menuClass,false,false); ?>
					<?php } else { ?>
						<?php show_categories_menu($menuClass,false); ?>
					<?php } ?>
				</ul> <!-- end ul#nav -->
			<?php }
			else echo($primaryNav); ?>

			<div class="clear"></div>

			<!-- Logo -->
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php $logo = (get_option('glow_logo') <> '') ? get_option('glow_logo') : get_template_directory_uri().'/images/logo.png'; ?>
				<img src="<?php echo esc_attr( $logo ); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>" id="logo" class="unitPng"/></a>

			<div class="clear"></div>

			<!-- Categories Menu -->
			<?php $menuClass = 'nav superfish';
			$menuID = 'cats-menu';
			$secondaryNav = '';
			if (function_exists('wp_nav_menu')) {
				$secondaryNav = wp_nav_menu( array( 'theme_location' => 'secondary-menu', 'container' => '', 'fallback_cb' => '', 'menu_class' => $menuClass, 'menu_id' => $menuID, 'echo' => false ) );
			};
			if ($secondaryNav == '') { ?>
				<ul id="<?php echo esc_attr( $menuID ); ?>" class="<?php echo esc_attr( $menuClass ); ?>">
					<?php if (get_option('glow_swap_navbar') == 'false') { ?>
						<?php show_categories_menu($menuClass,false); ?>
					<?php } else { ?>
						<?php if (get_option('glow_home_link') == 'on') { ?>
							<li <?php if (is_home()) echo('class="current_page_item"') ?>><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e('Home','Glow') ?></a></li>
						<?php }; ?>

						<?php show_page_menu($menuClass,false,false); ?>
					<?php } ?>
				</ul> <!-- end ul#nav -->
			<?php }
			else echo($secondaryNav); ?>

		</div> <!-- end container -->
	</div> <!-- end header -->

	<div id="content">
		<div id="container-top"></div>
		<div class="container<?php global $fullwidth; if ( is_page_template('page-full.php') || $fullwidth ) echo ' no_sidebar'; ?>">