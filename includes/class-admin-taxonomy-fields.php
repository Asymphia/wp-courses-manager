<?php

namespace WPCourses;

if ( ! defined( 'ABSPATH' ) ) exit;

class AdminTaxonomyFields {
	const MANAGED_TAXONOMIES = array( 'miejsce', 'organizator', 'wykladowca' );
	
	public function register_hooks() {
		add_action( 'add_meta_boxes', array( $this, 'replace_default_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_selected_terms' ) );
	}
	
	public function replace_default_meta_boxes() {
		foreach ( self::MANAGED_TAXONOMIES as $taxonomy ) {
			remove_meta_box( 'tagsdiv-' . $taxonomy, 'kurs', 'side' );
			
			add_meta_box(
				$taxonomy . '_select_box',
				get_taxonomy( $taxonomy )->labels->name,
				array( $this, 'render_select_box' ),
				'kurs',
				'side',
				'default',
				array( 'taxonomy' => $taxonomy )
			);
		}
	}
	
	public function render_select_box( $post, $box ) {
		$taxonomy = $box['args']['taxonomy'];
		$taxonomy_obj = get_taxonomy( $taxonomy );
		$terms = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false ) );
		$current = wp_get_object_terms( $post->ID, $taxonomy, array( 'fields' => 'ids' ) );
		$selected = !empty( $current ) ? $current[0] : '';
		
		wp_nonce_field( 'wp_save_' . $taxonomy, 'wp_' . $taxonomy . '_nonce' );
		
		echo '<select name="wp_tax_' . esc_attr( $taxonomy ) . '" style="width:100%;">';
		echo '<option value="">— Wybierz ' . esc_html( $taxonomy_obj->labels->singular_name ) . ' —</option>';
		
		foreach ( $terms as $term ) {
			printf(
				'<option value="%d" %s>%s</option>',
				$term->term_id,
				selected( $selected, $term->term_id, false ),
				esc_html( $term->name )
			);
		}
		
		echo '</select>';
		
		if ( empty( $terms ) ) {
			echo '<p style="color:#a00; margin-top:8px;">Brak terminów — dodaj je najpierw w Kursy → ' . esc_html( $taxonomy_obj->labels->name ) . '.</p>';
		}
	}
	
	public function save_selected_terms( $post_id ) {
		if ( get_post_type( $post_id ) !== 'kurs' ) {
			return;
		}
		
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		
		foreach ( self::MANAGED_TAXONOMIES as $taxonomy ) {
			$nonce_key = 'wp_' . $taxonomy . '_nonce';
			
			if ( ! isset( $_POST[ $nonce_key ] ) || ! wp_verify_nonce( $_POST[ $nonce_key ], 'wp_save_' . $taxonomy ) ) {
				continue;
			}
			
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				continue;
			}
			
			$value = isset( $_POST[ 'wp_tax_' . $taxonomy ] ) ? intval( $_POST[ 'wp_tax_' . $taxonomy ] ) : 0;
			
			wp_set_object_terms( $post_id, $value ? array( $value ) : array(), $taxonomy );
		}
	}
}