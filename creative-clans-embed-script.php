<?php
/*
Plugin Name: Creative Clans Embed Script
Plugin URI: http://www.creativeclans.nl
Description: Gives the possibility to add scripts to the beginning and/or the end of the 'content' of any page or post. 
Version: 1.2
Author: Guido Tonnaer
Author URI: http://www.creativeclans.nl

Copyright 2010-2012 Guido Tonnaer

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/**
 * Determine the location.
 */
$ccembedscriptpluginpath = WP_CONTENT_URL.'/plugins/'.plugin_basename(dirname(__FILE__)).'/';

/**
 * Hook the_content to output the scripts added to the page or post.
 */
add_filter('the_content', 'ccembedscript_display_hook');
add_filter('the_excerpt', 'ccembedscript_display_hook');
	
/**
 * output the scripts.
 */
function ccembedscript_display_hook($content='') {
	global $post;
  return get_post_meta($post->ID, '_ccembedscripttexttop', true) . $content . get_post_meta($post->ID, '_ccembedscripttext', true);
}

/**
 * Displays a box that allows users to insert the scripts for the post or page
 */
function ccembedscript_meta($post) {
  // Use nonce for verification
  wp_nonce_field( plugin_basename( __FILE__ ), 'ccembedscript_noncename' );

	?>
	<label for="ccembedscripttexttop"><?php _e('Scripts to be inserted at the top','ccembedscript') ?></label><br />
  <textarea id="ccembedscripttexttop" name="ccembedscripttexttop" /><?php echo get_post_meta($post->ID,'_ccembedscripttexttop',true); ?></textarea><br />
	<label for="ccembedscripttext"><?php _e('Scripts to be inserted at the bottom','ccembedscript') ?></label><br />
  <textarea id="ccembedscripttext" name="ccembedscripttext" /><?php echo get_post_meta($post->ID,'_ccembedscripttext',true); ?></textarea>
	<?php
}

/**
 * Add the box defined above to post and page edit screens.
 */
function ccembedscript_meta_box() {
	add_meta_box('ccembedscript','Creative Clans Embed Script','ccembedscript_meta','post','side');
	add_meta_box('ccembedscript','Creative Clans Embed Script','ccembedscript_meta','page','side');
}
add_action('admin_menu', 'ccembedscript_meta_box');

/**
 * If the post is inserted, save the script.
 */
function ccembedscript_insert_post($pID) {

  // if the function is called by the WP autosave feature, nothing must be saved
  if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
    return;
    
  // verify this came from the our screen and with proper authorization,
  // because save_post can be triggered at other times

  if ( !wp_verify_nonce( $_POST['ccembedscript_noncename'], plugin_basename( __FILE__ ) ) )
      return;

  
  // Check permissions
  if ( 'page' == $_POST['post_type'] ) 
  {
    if ( !current_user_can( 'edit_page', $pID ) )
        return;
  }
  else
  {
    if ( !current_user_can( 'edit_post', $pID ) )
        return;
  }

  // OK, we're authenticated: we need to find and save the data
  $text = (isset($_POST['ccembedscripttexttop'])) ? $_POST['ccembedscripttexttop'] : '';
  update_post_meta($pID, '_ccembedscripttexttop', $text);
  $text = (isset($_POST['ccembedscripttext'])) ? $_POST['ccembedscripttext'] : '';
  update_post_meta($pID, '_ccembedscripttext', $text);
}
add_action('save_post', 'ccembedscript_insert_post');

?>