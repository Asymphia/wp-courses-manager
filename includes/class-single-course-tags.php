<?php

namespace WPCourses;

if ( ! defined( 'ABSPATH' ) ) exit;

class SingleCourseTags {
	public function register_hooks() {
		add_shortcode( 'kurs_kategoria', array( $this, 'render_category_name' ) );
		add_shortcode( 'kurs_kategoria_opis', array( $this, 'render_category_description' ) );
	}
	
	public function render_category_name() {
		$term = $this->get_current_course_category();
		return $term ? esc_html( $term->name ) : '';
	}
	
	public function render_category_description() {
		$term = $this->get_current_course_category();
		
		if ( ! $term ) {
			return '';
		}
		
		return wpautop( term_description( $term->term_id, 'kategoria_kursu' ) );
	}
	
	private function get_current_course_category() {
		$post_id = get_the_ID();
		
		if ( ! $post_id || get_post_type( $post_id ) !== 'kurs' ) {
			return null;
		}
		
		$terms = get_the_terms( $post_id, 'kategoria_kursu' );
		
		if ( ! $terms || is_wp_error( $terms ) ) {
			return null;
		}
		
		return $terms[0];
	}
}