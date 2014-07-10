<?php wp_nonce_field( basename( __FILE__ ), 'mdpicker_nonce' ); ?>

<style>
	td.mdp-highlight a{
		border: 1px solid #2F4F4F !important;
		background: #8DB6CD  !important;
	}
</style>

<input type="hidden" name="mdpicker_dates" id="mdpicker_dates" value="<?php echo $this->get_post_dates($post->ID ) ?>">
<div id="mdp-datepicker"></div>