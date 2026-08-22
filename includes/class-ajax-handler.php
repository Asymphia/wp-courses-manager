<?php

namespace WPCourses;

if ( ! defined( 'ABSPATH' ) ) exit;

class AjaxHandler {
	public function register_hooks() {
		add_action( 'wp_ajax_wp_filter_kursy', array( $this, 'handle_request' ) );
		add_action( 'wp_ajax_nopriv_wp_filter_kursy', array( $this, 'handle_request' ) );
	}
	
	public function handle_request() {
		check_ajax_referer( 'wp_kursy_nonce', 'nonce' );
		
		$html = CourseQuery::render_results_html(array(
			's' => $this->get_string_param( 's' ),
			'kategoria' => $this->get_array_param( 'kategoria' ),
			'miejsce' => $this->get_array_param( 'miejsce' ),
			'organizator' => $this->get_array_param( 'organizator' ),
			'wykladowca' => $this->get_array_param( 'wykladowca' ),
			'data_od' => $this->get_string_param( 'data_od' ),
			'data_do' => $this->get_string_param( 'data_do' ),
			'sort' => $this->get_string_param( 'sort', 'data_kursu_asc' ),
			'take' => isset( $_POST['take'] ) ? intval( $_POST['take'] ) : 9,
			'pagination' => isset( $_POST['pagination'] ) && $_POST['pagination'] === '1',
			'paged' => isset( $_POST['paged'] ) ? intval( $_POST['paged'] ) : 1,
		));
		
		wp_send_json_success( array( 'html' => $html ) );
	}
	
	private function get_string_param( $key, $default = '' ) {
		return isset( $_POST[ $key] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : $default;
	}
	
	private function get_array_param( $key ) {
		return isset( $_POST[ $key ] ) && is_array( $_POST[ $key ] )
			? array_map( 'sanitize_text_field', wp_unslash( $_POST[ $key ] ) )
			: array();
	}
}