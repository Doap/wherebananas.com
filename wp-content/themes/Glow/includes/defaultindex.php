<div id="mainposts" class="notop">
<?php $i = 0; ?>
<?php if (have_posts()) : while (have_posts()) : the_post();
$i++;?>

<?php $width = 65;
	  $height = 65;
	  $classtext = 'thumbnail';
	  $titletext = get_the_title();

	  $thumbnail = get_thumbnail($width,$height,$classtext,$titletext,$titletext);
	  $thumb = $thumbnail["thumb"]; ?>

			<div class="mainpost-wrap<?php if (($i%2)<>0) echo (" first") ?>">
				<div class="bottom">
					<div class="inner-content">
						<?php get_template_part('includes/postinfo-small'); ?>
						<h2><a href="<?php the_permalink() ?>" title="<?php printf(esc_attr__ ('Permanent Link to %s', 'Glow'), get_the_title()) ?>"><?php truncate_title(36); ?></a></h2>

						<?php if ($thumb != '') { ?>
							<a href="<?php the_permalink() ?>" title="<?php printf(esc_attr__ ('Permanent Link to %s', 'Glow'), get_the_title()) ?>">
								<?php print_thumbnail($thumb, $thumbnail["use_timthumb"], $titletext, $width, $height, $classtext); ?>
							</a>
                        <?php }; ?>

						<p><?php truncate_post(79); ?></p>
					</div> <!-- end inner-content-->
				</div> <!-- end bottom-->
			</div> <!-- end mainpost-wrap-->
<?php endwhile; ?>
<div class="clear"></div>
	<?php if(function_exists('wp_pagenavi')) { wp_pagenavi(); }
	else { ?>
	<div class="pagination">
		<div class="alignleft"><?php next_posts_link(esc_html__('&laquo; Older Entries','Glow')) ?></div>
		<div class="alignright"><?php previous_posts_link(esc_html__('Next Entries &raquo;', 'Glow')) ?></div>
	</div>
	<?php } ?>
<?php else : ?>
	<?php get_template_part('includes/no-results'); ?>
<?php endif; ?>
</div> <!-- end mainposts -->