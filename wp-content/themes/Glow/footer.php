		</div> <!-- end wrapper -->
	</div> <!-- end main area wrap -->

		<div id="footer">
			<p><?php esc_html_e('Powered by ','Glow'); ?> <a href="http://www.wordpress.com">WordPress</a> | <?php esc_html_e('Designed by ','Glow'); ?> <a href="http://www.elegantthemes.com">Elegant Themes</a></p>
		</div> <!-- end footer -->
	</div> <!-- end container -->
</div> <!-- end content -->

<?php if ((is_home()) && (get_option('glow_featured') == 'on') ) { //scripts for featured area are loaded only on homepage / when Display Featured Articles is set to Display ?>
	<script type="text/javascript">
		jQuery(".js ul#page-menu, .js ul#cats-menu, .js img#logo, .js div.custom-sidebar-block").show(); //prevents a flash of unstyled content
	</script>
<?php } else { ?>
	<script type="text/javascript">
		jQuery(".js ul#page-menu, .js ul#cats-menu, .js img#logo, .js div.custom-sidebar-block").show(); //prevents a flash of unstyled content
	</script>
<?php };

get_template_part('includes/scripts');
wp_footer(); ?>
</body>
</html>