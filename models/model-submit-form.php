	<?php
	/**
	* Model for submite form.
	*
	* @package    Cherry_Framework
	* @subpackage Model
	* @author     Cherry Team <cherryframework@gmail.com>
	* @copyright  Copyright (c) 2012 - 2016, Cherry Team
	* @link       http://www.cherryframework.com/
	* @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
	*/

	/**
	* Model properties
	*/
	class Model_Submit_Form {

	public function register_ajax() {

		add_action( 'wp_ajax_nopriv_submit_form', array( 'Model_Submit_Form', 'submit_form_callback' ) );
		add_action( 'wp_ajax_submit_form', array( 'Model_Submit_Form', 'submit_form_callback' ) );
	
		add_action( 'wp_enqueue_scripts', array( 'Model_Submit_Form', 'load_scripts' ) );

	}
	/**
	 * Shortcode properties
	 *
	 * @return html code.
	 */
	public function shortcode_submit_form() {

		wp_localize_script( 'cherry-js-core', 'form_url', 
			array(
				'url' => admin_url('admin-ajax.php'),
			)
		);

		$terms = Model_Submit_Form::custom_taxonomy_walker( 'property-type' );

		return Cherry_Core::render_view(
			TM_REAL_ESTATE_DIR . '/views/submit-form.php',
			$terms
		);
	}
	public function submit_form_callback() {
		$property['title'] = $_POST['property']['title'];
		$property['description'] = $_POST['property']['description'];
		$term_id = $_POST['property']['type'];
		$property_meta = $_POST['property']['meta'];
	
		$post_id = Model_Properties::add_property( $property );

		wp_set_post_terms( $post_id, $term_id, 'property-type' );

		$file = $_FILES['thumb'];
		$attachment_id = Model_Submit_Form::insert_attacment($file,$post_id);

		 
		$files = Model_Submit_Form::reArrayFiles($_FILES['gallery']);
		foreach ( $files as $key => $file ) {
			$property_meta['gallery']['image'][ $key ] = Model_Submit_Form::insert_attacment($file,$post_id);
		}
		echo "<pre>"; print_r($property_meta['gallery']); echo "</pre>";
		foreach ( $property_meta as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

		set_post_thumbnail( $post_id, $attachment_id );

		wp_die();
	}
	public function insert_attacment( $file, $post_id ) {
	
		require_once( ABSPATH . 'wp-admin/includes/admin.php' );
		$file_return = wp_handle_upload( $file, array( 'test_form' => false ) );
		if( isset( $file_return['error'] ) || isset( $file_return['upload_error_handler'] ) ) {
			return false;
		} else {
			$filename = $file_return['file'];
			$attachment = array(
				'post_mime_type' => $file_return['type'],
				'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
				'post_content' => '',
				'post_status' => 'inherit',
				'guid' => $file_return['url']
			);
			$attachment_id = wp_insert_attachment( $attachment, $file_return['url'], $post_id );
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
			$attachment_data = wp_generate_attachment_metadata( $attachment_id, $filename );

			wp_update_attachment_metadata( $attachment_id, $attachment_data );
			if( 0 < intval( $attachment_id ) ) {
				return $attachment_id;
			}
		}
		return false;
	}

	public function custom_taxonomy_walker( $taxonomy, $parent = 0 ) {
		$terms = get_terms( $taxonomy, array('parent' => $parent, 'hide_empty' => false ) );
		if ( count( $terms ) > 0) {
			foreach ( $terms as $term ) {
				$child = Model_Submit_Form::custom_taxonomy_walker( $taxonomy, $term->term_id ); 
				$types[] = array(
					'term_id' => $term->term_id,
					'name' => $term->name,
					'child' => $child,
					);
			}
			return $types;
		}
		return false;
	}
	public function load_scripts() {
		wp_enqueue_script(
			'jquery_ui_widget',
			plugins_url( 'tm-real-estate' ) . '/assets/js/uploader/vendor/jquery.ui.widget.js',
			array( 'jquery' ),
			'1.0.0',
			false
		);
		
		wp_enqueue_script(
			'load_image',
			plugins_url( 'tm-real-estate' ) . '/assets/js/uploader/load-image.all.min.js',
			array( 'jquery' ),
			'1.0.0',
			false
		);
		wp_enqueue_script(
			'canvas_to_blob',
			plugins_url( 'tm-real-estate' ) . '/assets/js/uploader/canvas-to-blob.js',
			array( 'jquery' ),
			'1.0.0',
			false
		);
		wp_enqueue_script(
			'iframe_transport',
			plugins_url( 'tm-real-estate' ) . '/assets/js/uploader/jquery.iframe-transport.js',
			array( 'jquery' ),
			'1.0.0',
			false
		);
		wp_enqueue_script(
			'fileupload',
			plugins_url( 'tm-real-estate' ) . '/assets/js/uploader/jquery.fileupload.js',
			array( 'jquery' ),
			'1.0.0',
			false
		);
		wp_enqueue_script(
			'fileupload_process',
			plugins_url( 'tm-real-estate' ) . '/assets/js/uploader/jquery.fileupload-process.js',
			array( 'jquery' ),
			'1.0.0',
			false
		);
		wp_enqueue_script(
			'fileupload_image',
			plugins_url( 'tm-real-estate' ) . '/assets/js/uploader/jquery.fileupload-image.js',
			array( 'jquery' ),
			'1.0.0',
			false
		);
		//wp_enqueue_style(
		//	'tm-real-state-settings-page',
		//	plugins_url( 'tm-real-estate' ) . '/assets/css/page-settings.min.css',
		//	array(),
		//	'1.0.0',
		//	'all'
		//);
	}
	public function reArrayFiles(&$file_post) {

		$file_ary = array();
		$file_count = count ($file_post['name']);
		$file_keys = array_keys ($file_post);

		for ( $i = 0; $i < $file_count; $i++ ) {
			foreach ( $file_keys as $key ) {
				$file_ary[$i][$key] = $file_post[$key][$i];
			}
		}

		return $file_ary;
	}

}
