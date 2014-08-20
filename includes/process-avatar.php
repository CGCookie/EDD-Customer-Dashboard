<?php
	
	add_action( 'init',  'cgc_save_profile' );
	function cgc_save_profile() {
		if ( isset( $_POST['action'] ) && $_POST['action'] == 'update_profile' ) {

			if( !is_user_logged_in() )
				return;

			if ( wp_verify_nonce( $_POST['profile_nonce'], 'profile-nonce' ) ) {
				$user_id 		= get_current_user_id();

				if( empty( $user_id ) )
					return;

				cgc_handle_avatar_image( $user_id );
				do_action( 'cgc_save_profile', $user_id );

				wp_redirect( network_home_url( '/blender/customer-dashboard/?task=profile#profile-information' ) ); exit;
			}
		}
	}


	function cgc_handle_avatar_image( $user_id ){

		if ( $_FILES ) {

			var_dump($_FILES);wp_die();

			foreach ( $_FILES as $file => $array ) {
				$avatar_image_id = cgc_save_profile_image( $file );

			}
		}


		if ( isset( $avatar_image_id ) && !is_wp_error( $avatar_image_id ) ) {
			$avatar_img_src = wp_get_attachment_image_src( $avatar_image_id, 'thumbnail' );
			update_user_meta( $user_id, 'profile_avatar_image', $avatar_img_src[0] );
		}
	}

	function cgc_save_profile_image( $file_handler, $post_id = null ) {
		if ( $_FILES[ $file_handler ]['error'] !== UPLOAD_ERR_OK )
			return false;

		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		$attach_id = media_handle_upload( $file_handler, $post_id );

		return $attach_id; 
	}