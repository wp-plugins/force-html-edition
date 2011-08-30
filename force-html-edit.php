<?php
/*
Plugin Name: Force html edit
Plugin URI: http://magnetik.org/tech/wordpress/force-html-edition/
Description: Add checkbox to force HTML edition on specific posts. <a href="http://magnetik.org/tech/wordpress/force-html-edition/">Website for support/suggestion</a>
Version: 0.3
Author: magnetik
Author URI: http://magnetik.org
License: GPLv2
*/
/*  Copyright 2011  Baptiste Lafontaine  (email : baptiste33@gmail.com)
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/* Put all in one function to ensure that will be called
 * once plugin_loaded is called to ensure that every plugin
 * function are defined */
 
// WP 3.0+
// add_action('add_meta_boxes', 'force-html-edit_custom_box');
// backwards compatible
add_action('admin_init', 'force_html_edit_add_custom_box');
add_action('admin_head', 'force_html_edit_handler');
/* Do something with the data entered */
add_action('save_post', 'force_html_edit_save_postdata');

/* Function called */
function force_html_edit_handler () {
  global $post;
  if (is_object($post) && property_exists($post,"ID")) {
	  $force_html = force_html_is_forced($post->ID);
	  if ($force_html) {
		add_filter('user_can_richedit', create_function ('$a', 'return false;'), 50 );
	   }
	}
}
/* Adds a box to the main column on the Post and Page edit screens */
function force_html_edit_add_custom_box() {
    add_meta_box( 'force-html-edit', 
				__( 'Force html edit', 'force-html-edit' ), 
                'force_html_edit_custom_box', 
				'post',
				'side'
	);
    add_meta_box( 'force-html-edit',
				__( 'Force html edit', 'force-html-edit' ), 
                'force_html_edit_custom_box',
				'page' ,
				'side'
	);
}
/* Prints the box content */
function force_html_edit_custom_box($post) {
  // Use nonce for verification
  wp_nonce_field(plugin_basename(__FILE__), 'force_html_edit_noncename');
  // Get the status for this post
  $force_html = force_html_is_forced($post->ID);
  // The actual fields for data entry
  echo '<label for="force_html_edit">'. __("Force html edit ?", 'force-html-edit') . '</label> ';
  echo '<input type="checkbox" id="force_html_edit" name="force_html_edit" value="on" ';
  if ($force_html) echo 'checked="checked"';
  echo ' />';
}
function force_html_is_forced ($id) {
  $force_html = get_post_meta($id, "_force_html", true);
  if ($force_html == "on") { return true; }
  else { return false; }
}
/* Save data */
function force_html_edit_save_postdata( $post_id ) {
  // verify this came from the our screen and with proper authorization,
  // because save_post can be triggered at other times
  if (!wp_verify_nonce( $_POST['force_html_edit_noncename'], plugin_basename(__FILE__))) {
    return $post_id;
  }
  // verify if this is an auto save routine. If it is our form has not been submitted, so we dont want
  // to do anything
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) 
    return $post_id;
  
  // Check permissions
  if ('page' == $_POST['post_type']) {
    if ( !current_user_can( 'edit_page', $post_id ) )
      return $post_id;
  } else {
    if ( !current_user_can( 'edit_post', $post_id ) )
      return $post_id;
  }
  // OK, we're authenticated: we need to find and save the data
  if (isset($_POST['force_html_edit'])) {
    update_post_meta($post_id, '_force_html', 'on');
  }
  else {
    update_post_meta($post_id, '_force_html', 'off');
  }
  // Do something with $mydata 
  // probably using add_post_meta(), update_post_meta(), or 
  // a custom table (see Further Reading section below)
   return $mydata;
}
?>