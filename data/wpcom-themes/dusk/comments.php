<?php // Do not delete these lines
if (!empty($_SERVER['SCRIPT_FILENAME']) && 'comments.php' == basename($_SERVER['SCRIPT_FILENAME']))
	die ('Please do not load this page directly. Thanks!');
if ( post_password_required() ) {
?>
<p class="nocomments"><?php _e("This post is password protected. Enter the password to view comments."); ?></p>
<?php
	return;
}

function dusk_comment($comment, $args, $depth) {
	$GLOBALS['comment'] = $comment;
	extract($args, EXTR_SKIP);
?>
	<li class="<?php echo $oddcomment; ?>" id="comment-<?php comment_ID() ?>">
		<?php if ($args['avatar_size'] != 0) { ?><div class="avatar"><?php echo get_avatar( $comment, $args['avatar_size'] ); ?></div><?php } ?>
		<h4 class="commentauthor vcard"><span class="fn"><?php comment_author_link() ?></span> <span class="says"><?php _e('said,'); ?></span></h4>
		<p class="comment-meta commentmetadata commentmeta"><?php comment_date() ?> <?php _e('at'); ?> <a href="#comment-<?php comment_ID() ?>" title="<?php _e('Permanent link to this comment'); ?>"><?php comment_time() ?></a> <?php edit_comment_link(__('Edit'),' &#183; ',''); ?></p>
		<div id="div-comment-<?php comment_ID() ?>">
			<?php comment_text() ?>
		<div class="reply">
			<?php comment_reply_link(array_merge( $args, array('add_below' => 'div-comment', 'depth' => $depth, 'max_depth' => $args['max_depth']))) ?>
		</div>
		</div>
<?php 
}

if (have_comments()) : ?>
	<h3 id="comments"><?php comments_number(__('Comments'), __('1 Comment'), __('% Comments'));?></h3>

	<ol id="commentlist">
	<?php wp_list_comments(array('callback'=>'dusk_comment')); ?>
	</ol>
	
	<div class="navigation">
		<div class="alignleft"><?php previous_comments_link() ?></div>
		<div class="alignright"><?php next_comments_link() ?></div>
	</div>

	<?php if (!comments_open()) : ?> 
		<p>Comments are closed.</p>		
	<?php endif; ?>
<?php endif; ?>


<?php if (comments_open()) : ?>
<div id="respond">
<h3 id="postcomment"><?php comment_form_title( __('Post a Comment'), __('Post a Comment to %s') ); ?></h3>
<div id="cancel-comment-reply"><small><?php cancel_comment_reply_link() ?></small></div>

<?php if ( get_option('comment_registration') && !$user_ID ) : ?>
<p><?php _e('You must be'); ?> <a href="<?php echo get_option('siteurl'); ?>/wp-login.php?redirect_to=<?php the_permalink(); ?>"><?php _e('logged in'); ?></a> <?php _e('to post a comment.'); ?></p>
<?php else : ?>

<form action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php" method="post" id="commentform">

<?php if ( $user_ID ) : ?>

<p><?php _e('Logged in as'); ?> <a href="<?php echo get_option('siteurl'); ?>/wp-admin/profile.php"><?php echo $user_identity; ?></a>. <a href="<?php echo get_option('siteurl'); ?>/wp-login.php?action=logout" title="<?php _e('Log out of this account'); ?>"><?php _e('Logout'); ?> &raquo;</a></p>

<?php else : ?>

<p>
<input type="text" name="author" id="author" value="<?php echo $comment_author; ?>" size="28" tabindex="1" />
<label for="author"><?php _e('Name'); ?> <?php if ($req) _e('(required)'); ?></label>
</p>

<p>
<input type="text" name="email" id="email" value="<?php echo $comment_author_email; ?>" size="28" tabindex="2" />
<label for="email"><?php _e('E-mail'); ?> <?php if ($req) _e('(required)'); ?></label>
</p>

<p>
<input type="text" name="url" id="url" value="<?php echo $comment_author_url; ?>" size="28" tabindex="3" />
<label for="url"><abbr title="<?php _e('Uniform Resource Identifier'); ?>"><?php _e('URI'); ?></abbr></label>
</p>

<?php endif; ?>

<p>
<textarea name="comment" id="comment" cols="80" rows="10" tabindex="4"></textarea>
</p>

<p>
<input name="submit" type="submit" id="submit" tabindex="5" value="<?php _e('Submit Comment'); ?>" />
<?php comment_id_fields(); ?>
</p>

<?php do_action('comment_form', $post->ID); ?>

</form>
<?php endif; // If registration required and not logged in ?>
</div>
<?php endif; // if you delete this the sky will fall on your head ?>
