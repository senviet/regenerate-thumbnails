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

class RegenerateThumbnail {
	public function __construct() {
		add_filter( 'media_row_actions', array( $this, 'add_media_row_action' ), 10, 2 );
		add_action( 'admin_head-upload.php', array( $this, 'add_inline_script' ) );
		add_action( 'wp_ajax_regeneratethumbnail', array( $this, 'ajax_handler' ) );

	}

	public function regenerate( $attachmentId ) {

		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'NOT_PERMISSION', 'Only admin can regenerate image.' );
		}
		$post = get_post( $attachmentId );
		if ( ! $post ) {
			return new WP_Error( 'POST_NOT_EXIST', 'This postId is not exist.' );
		}
		if ( 'attachment' != $post->post_type || ! preg_match( '!^image/!', get_post_mime_type( $post ) ) ) {
			return new WP_Error( 'NOT_SUPPORT', 'This plugin is only support for image attachment' );
		}

		$fullSizePath = get_attached_file( $post->ID );
		if ( ! file_exists( $fullSizePath ) ) {
			return new WP_Error( 'NOT_DISPLAYABLE', 'This attachment\'s file is not displayable' );
		}
		if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
			include( ABSPATH . 'wp-admin/includes/image.php' );
		}
		$metaData = wp_generate_attachment_metadata( $post->ID, $fullSizePath );
		if ( is_wp_error( $metaData ) ) {
			return $metaData;
		}
		if ( empty( $metaData ) ) {
			return new WP_Error( 'UNKNOW_ERRORR', 'Unknown error !' );
		}
		wp_update_attachment_metadata( $post->ID, $metaData );

		return true;
	}

	public function add_media_row_action( $actions, $post ) {
		if ( !preg_match( '!^image/!', get_post_mime_type( $post ) ) || ! current_user_can( 'manage_options' ) ) {
			return $actions;
		}
		$actions['regenerate_thumbnails'] = '<a data-id="'.$post->ID.'" class="regenerate_thumbnail" href="#" title="' . esc_attr( __( "Regenerate the thumbnails for this single image", 'regenerate-thumbnails' ) ) . '">' . __( 'Regenerate Thumbnails', 'regenerate-thumbnails' ) . '</a>';

		return $actions;
	}
	public function add_inline_script(){
		$inlineFile = plugins_url('js/inline.js', __FILE__ );
		echo "<script src='".$inlineFile."'></script>";
	}
	public function ajax_handler(){
		wp_send_json(array('success'=>true,'message'=>'success'));
	}
}

new RegenerateThumbnail();