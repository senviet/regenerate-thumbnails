<?php

/*
Plugin Name: Regenerate Thumbnail
Plugin URI: http://laptrinh.senviet.org
Description: Allow you to regenerate the attachment thumbnail and sizes.
Version: 1.0
Author: nguyenvanduocit
Author URI: http://nvduoc@senviet.org
License: GPL2
*/

class RegenerateThumbnail{
	public function __construct(){

	}
	public function regenerate($attachmentId){

		if(!current_user_can('manage_options')){
			return new WP_Error('NOT_PERMISSION', 'Only admin can regenerate image.');
		}
		$post = get_post($attachmentId);
		if(!$post){
			return new WP_Error('POST_NOT_EXIST', 'This postId is not exist.');
		}
		if('attachment' != $post->post_type || !preg_match('!^image/!', get_post_mime_type( $post ) ) ){
			return new WP_Error('NOT_SUPPORT', 'This plugin is only support for image attachment');
		}

		$fullSizePath = get_attached_file( $post->ID );
		if(!file_exists($fullSizePath)){
			return new WP_Error('NOT_DISPLAYABLE', 'This attachment\'s file is not displayable');
		}
		if(!function_exists('wp_generate_attachment_metadata')){
			include( ABSPATH . 'wp-admin/includes/image.php' );
		}
		$metaData = wp_generate_attachment_metadata( $post->ID, $fullSizePath );
		if(is_wp_error($metaData)){
			return $metaData;
		}
		if ( empty( $metaData ) ){
			return new WP_Error('UNKNOW_ERRORR', 'Unknown error !');
		}
		wp_update_attachment_metadata( $post->ID, $metaData );
		return true;;
	}
}