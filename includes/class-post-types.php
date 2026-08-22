<?php
namespace WPCourses;

if ( ! defined( 'ABSPATH' ) ) exit;

class PostTypes {
	public function register_hooks() {
		add_action( 'init', array( $this, 'register_post_type' ) );
	}
	
	public function register_post_type() {
		$labels = array(
			'name' => 'Kursy',
			'singular_name' => 'Kurs',
			'menu_name' => 'Kursy',
			'name_admin_bar' => 'Kurs',
			'add_new' => 'Dodaj nowy',
			'add_new_item' => 'Dodaj nowy kurs',
			'new_item' => 'Nowy kurs',
			'edit_item' => 'Edytuj kurs',
			'view_item' => 'Zobacz kurs',
			'all_items' => 'Wszystkie kursy',
			'search_items' => 'Szukaj kursów',
			'not_found' => 'Nie znaleziono kursów',
			'not_found_in_trash' => 'Brak kursów w koszu',
		);
		
		$args = array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'show_in_nav_menus' => true,
			'show_in_admin_bar' => true,
			'show_in_rest' => true,
			'menu_position' => 5,
			'menu_icon' => 'dashicons-welcome-learn-more',
			'query_var' => true,
			'rewrite' => array( 'slug' => 'terminy-kursow' ),
			'capability_type' => 'post',
			'has_archive' => true,
			'hierarchical' => false,
			'exclude_from_search' => false,
			'supports' => array( 'title' ),
		);
		
		register_post_type( 'kurs', $args );
	}
}
