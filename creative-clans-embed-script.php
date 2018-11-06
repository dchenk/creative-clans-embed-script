<?php
/*
Plugin Name: Embed Any Script
Plugin URI: https://github.com/dchenk/embed-any-script
Description: This plugin lets you insert code at the beginning and end of the content of any page or post.
Version: 1.0
Author: Wider Webs
*/

// Output the scripts.
function embed_any_script_display($content = '') {
	global $post;
	return get_post_meta($post->ID, '_embed_any_script_top', true) . $content . get_post_meta($post->ID, '_embed_any_script', true);
}
add_filter('the_content', 'embed_any_script_display');
add_filter('the_excerpt', 'embed_any_script_display');

// Displays a box that allows users to insert the scripts for the post or page.
function embed_any_script_meta($post) {
	// Use nonce for verification.
	wp_nonce_field(plugin_basename( __FILE__ ), 'embed_any_script_nonce');
	?>
	<style> #embed_any_script_top, #embed_any_script { width: 100%; height: 80px; } </style>
	<label for="embed_any_script_top">Code inserted at the top</label><br>
	<textarea id="embed_any_script_top" name="embed_any_script_top"><?php echo get_post_meta($post->ID, '_embed_any_script_top', true); ?></textarea><br>
	<label for="embed_any_script">Code inserted at the bottom<label><br>
	<textarea id="embed_any_script" name="embed_any_script"><?php echo get_post_meta($post->ID, '_embed_any_script', true); ?></textarea><?php
}

// Add the box defined above to post and page edit screens.
function embed_any_script_meta_box() {
	add_meta_box('embed_any_script', 'Embed Code', 'embed_any_script_meta', 'post', 'side');
	add_meta_box('embed_any_script', 'Embed Code', 'embed_any_script_meta', 'page', 'side');
}
add_action('admin_menu', 'embed_any_script_meta_box');

// Update the meta for a post upon save.
function embed_any_script_save($pID) {
	// If the function is called by the WP auto-save feature, nothing must be saved.
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}

	// Verify this came from our screen and with proper authorization, since 'save_post' can be triggered
	// at other times.
	if (!wp_verify_nonce($_POST['embed_any_script_nonce'], plugin_basename( __FILE__ ))) {
		return;
	}

	// Check permissions.
	if ('page' == $_POST['post_type']) {
		if (!current_user_can('edit_page', $pID)) {
			return;
		}
	} else if (!current_user_can('edit_post', $pID)) {
		return;
	}

	// We're authenticated. Find and save the data.
	$text = isset($_POST['embed_any_script_top']) ? $_POST['embed_any_script_top'] : '';
	update_post_meta($pID, '_embed_any_script_top', $text);

	$text = isset($_POST['embed_any_script']) ? $_POST['embed_any_script'] : '';
	update_post_meta($pID, '_embed_any_script', $text);
}
add_action('save_post', 'embed_any_script_save');
