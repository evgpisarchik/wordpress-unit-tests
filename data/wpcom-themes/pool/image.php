<?php get_header(); ?>
			
	<div id="bloque">
		
		<div id="noticias">
		
<?php is_tag(); ?>
		<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
		
			<div <?php post_class( 'entrada' ); ?>>
				<h2 id="post-<?php the_ID(); ?>"><a href="<?php echo get_permalink($post->post_parent); ?>" rev="attachment"><?php echo get_the_title($post->post_parent); ?></a> &raquo; <?php the_title(); ?></h2>
				<small><?php comments_popup_link('Leave a Comment', '1 Comment', '% Comments'); ?> <?php edit_post_link('Edit this post', ' | ', ''); ?><br /></small>

				<p class="attachment"><a href="<?php echo wp_get_attachment_url($post->ID); ?>"><?php echo wp_get_attachment_image( $post->ID, 'auto' ); ?></a></p>
				<div class="caption"><?php if ( !empty($post->post_excerpt) ) the_excerpt(); ?></div>
				<div class="image-description"><?php if ( !empty($post->post_content) ) the_content(); ?></div>

				<div class="navigation">
					<div class="alignleft"><?php previous_image_link() ?></div>
					<div class="alignright"><?php next_image_link() ?></div>
				</div>
			</div>
				
		<?php comments_template(); // Get wp-comments.php template ?>
			
		<?php endwhile; else: ?>
		<h2 class="center">Not Found</h2>
		<p><?php _e('Sorry, no posts matched your criteria.'); ?></p>
		<?php endif; ?>

		</div>

<?php get_footer(); ?>
