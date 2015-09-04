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

	/**
	 * The constructor
	 */
	public function __construct() {
		add_filter( 'media_row_actions', array( $this, 'add_media_row_action' ), 10, 2 );
		add_action( 'admin_head-upload.php', array( $this, 'add_script' ) );
		add_action( 'wp_ajax_regeneratethumbnail', array( $this, 'ajax_handler' ) );
		add_action( 'wp_ajax_regeneratethumbnail-next-step', array( $this, 'ajax_handler_next_step' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'plugin_page_css' ) );
	}

	public function plugin_page_css( $hook ) {
		if ( "media_page_regenerate-thumbnail" !== $hook ) {
			return;
		}
		wp_enqueue_style( 'regenerate_thumbnail_style', plugins_url( '/css/admin.css', __FILE__ ), array() );
		wp_enqueue_script( 'regenerate_thumbnail_script', plugins_url( '/js/regenerateAll.js', __FILE__ ), array() );
	}

	public function admin_menu() {
		add_submenu_page( 'upload.php', 'Regenerate Thumbnail', 'Regenerate Thumbnail', 'manage_options', 'regenerate-thumbnail', array(
			$this,
			'admin_page_content'
		) );
	}

public function admin_page_content() {
	?>
	<div class="wrap">
		<h1>Regenerate Thumbnail</h1>

		<form action="">
			<p>Khi bạn click vào link dưới đây, tất cả các file thumbnail và các file ảnh đá được resize theo các
				size mà theme ( đang được active ) đã đăng ký với WordPress đều sẽ bị regenerate. Bất cứ lúc nào bạn
				muốn dừng lại, đều có thể ấn vào button "Dừng lại"</p>

			<div id="regenerateStatus">
				<p><span id="statusString">Đã xử lý</span> : <span id="processed">0</span>/<span id="total">0</span> files</p>
			</div>
			<p class="submit">
				<button type="submit" name="submit" id="startStopRegenerate" class="button button-primary">Bắt đầu
				</button>
			</p>
		</form>
	</div>
<?php }

	/**
	 * Regenerate the thumbnail
	 *
	 * @param $attachmentId int the attachment id
	 *
	 * @return bool|mixed|WP_Error
	 */
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

	/**
	 * Add action link to media row
	 *
	 * @param $actions
	 * @param $post
	 *
	 * @return mixed
	 */
	public function add_media_row_action( $actions, $post ) {
		if ( ! preg_match( '!^image/!', get_post_mime_type( $post ) ) || ! current_user_can( 'manage_options' ) ) {
			return $actions;
		}
		$actions['regenerate_thumbnails'] = '<a data-id="' . $post->ID . '" class="regenerate_thumbnail" href="#" title="' . esc_attr( __( "Regenerate the thumbnails for this single image", 'regenerate-thumbnails' ) ) . '">' . __( 'Regenerate Thumbnails', 'regenerate-thumbnails' ) . '</a>';

		return $actions;
	}

	/**
	 * Add script
	 */
	public function add_script() {
		$inlineFile = plugins_url( 'js/inline.js', __FILE__ );
		echo "<script src='" . $inlineFile . "'></script>";
	}

	/**
	 * handle ajax request
	 */
	public function ajax_handler() {
		if ( ! isset( $_POST['attachmentId'] ) ) {
			wp_send_json( array( 'success' => false, 'message' => 'Your have to provice the attachment ID.' ) );
		}
		$attachmentId = abs( $_POST['attachmentId'] );
		$result       = $this->regenerate( $attachmentId );
		if ( is_wp_error( $result ) ) {
			wp_send_json( array( 'success' => false, 'message' => $result->get_error_message() ) );

		}
		wp_send_json( array( 'success' => true, 'message' => 'generate success' ) );
	}

public function ajax_handler_next_step() {
	if ( ! session_id() ) {
		session_start();
	}
	$filePerPage = 5;
	if ( isset( $_SESSION['processedPage'] ) ) {
		$processedPage = $_SESSION['processedPage'];
	} else {
		$processedPage = 1;
	}

	$attachment_query = new WP_Query( array(
			'post_type'      => 'attachment',
			'post_mime_type' => 'image',
			'post_status'    => 'inherit',
			'posts_per_page' => $filePerPage,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'paged'          => $processedPage
		)
	);
	$attachments = $attachment_query->get_posts();
	foreach($attachments as $attachment){
		$fullSizePath = get_attached_file( $attachment->ID );
		$metaData = wp_generate_attachment_metadata( $attachment->ID, $fullSizePath );
		if ( is_wp_error( $metaData ) ) {
			return $metaData;
		}
		if ( empty( $metaData ) ) {
			return new WP_Error( 'UNKNOW_ERRORR', 'Unknown error !' );
		}
		wp_update_attachment_metadata( $attachment->ID, $metaData );
	}
	$proccessedCount = $attachment_query->post_count * $processedPage;
	if ( $proccessedCount ==  $attachment_query->found_posts) {
		$_SESSION['processedPage'] = 1;
		wp_send_json( array(
			'success'    => true,
			'proccessed' => $proccessedCount,
			'total'        => $attachment_query->found_posts,
			'done'       => true
		) );
	} else {
		$_SESSION['processedPage'] = $processedPage + 1;
		wp_send_json( array(
			'success'    => true,
			'proccessed' => $proccessedCount,
			'total'        => $attachment_query->found_posts
		) );
	}
}
}

new RegenerateThumbnail();