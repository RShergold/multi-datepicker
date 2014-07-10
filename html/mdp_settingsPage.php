
<div class="wrap">
	<h2><?php _e('Multi datepicker', 'mdpick_textdomain' ) ?></h2>

	<?php if (isset($flash_message)): ?>
	<div id="message" class="updated below-h2"><p><?php echo $flash_message ?></p></div>
	<?php endif; ?>
	
	<form name="mdpick_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
		<input type="hidden" name="mdpick_hidden" value="Y">
		
		<p><?php _e('Add the multi datepicker field to specific post types', 'mdpick_textdomain' ) ?><p>
		<p>
			<?php foreach ($this->all_post_types as $post_type): ?>
			<label>
				<input type="checkbox" name="mdpick_post_type[<?php echo $post_type->name ?>]" value="Y" <?php if( get_option('mdpick_pt_'.$post_type->name) ) echo 'checked'?>>
				<?php echo $post_type->labels->menu_name ?>
			</label>
			<br>
			<?php endforeach; ?>
		</p>
		
		<h3 class="title"><?php _e('Custom sorting', 'mdpick_textdomain' ) ?><p>
		<p>
			<label>
				<input type="checkbox" name="mdpick_custom_sort" value="Y" <?php if( get_option('mdpick_custom_sort') ) echo 'checked'?>>
				<?php _e('Sort default query', 'mdpick_textdomain' ) ?>
			</label>	
		</p>
		<p>
			<label>
				<input type="checkbox" name="mdpick_custom_sort_tags" value="Y" <?php if( get_option('mdpick_custom_sort_tags') ) echo 'checked'?>>
				<?php _e('Also sort tags & categories', 'mdpick_textdomain' ) ?>
			</label>	
		</p>
			
		<p class="submit">
			<input class="button button-primary" type="submit" name="Submit" value="<?php _e('Update Options', 'mdpick_textdomain' ) ?>" />
		</p>
		
	</form>
	
</div>