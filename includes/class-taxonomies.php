<?php

namespace WPCourses;

if ( ! defined( 'ABSPATH' ) ) exit;

class Taxonomies {
	const TAXONOMIES = array(
		'kategoria_kursu' => 'kategoria-kursu',
		'miejsce' => 'miejsce',
		'organizator' => 'organizator',
		'wykladowca' => 'wykladowca',
	);
	
	public function register_hooks() {
		add_action( 'init', array( $this, 'register_all_taxonomies' ) );
	}
	
	public function register_all_taxonomies() {
		$this->register_category_taxonomy();
		$this->register_simple_taxonomy( 'miejsce', 'Miejsca', 'Miejsce' );
		$this->register_simple_taxonomy( 'organizator', 'Organizatorzy', 'Organizator' );
		$this->register_simple_taxonomy( 'wykladowca', 'Wykładowcy', 'Wykładowca' );
	}
	
	private function register_category_taxonomy() {
		$labels = array(
			'name' => 'Kategorie kursów',
			'singular_name' => 'Kategoria kursu',
			'search_items' => 'Szukaj kategorii',
			'all_items' => 'Wszystkie kategorie',
			'parent_item' => 'Kategoria nadrzędna',
			'parent_item_colon' => 'Kategoria nadrzędna:',
			'edit_item' => 'Edytuj kategorię',
			'update_item' => 'Aktualizuj kategorię',
			'add_new_item' => 'Dodaj nową kategorię',
			'new_item_name' => 'Nazwa nowej kategorii',
			'menu_name' => 'Kategorie',
		);
		
		register_taxonomy( 'kategoria_kursu', array( 'kurs' ), array(
			'labels' => $labels,
			'hierarchical' => true,
			'public' => true,
			'show_ui' => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => true,
			'show_in_rest' => true,
			'query_var' => true,
			'rewrite' => array( 'slug' => self::TAXONOMIES['kategoria_kursu'] ),
		) );
	}
	
	private function register_simple_taxonomy( $key, $plural_label, $singular_label ) {
		$labels = array(
			'name' => $plural_label,
			'singular_name' => $singular_label,
			'search_items' => 'Szukaj: ' . mb_strtolower( $plural_label ),
			'all_items' => 'Wszyscy/wszystkie: ' . mb_strtolower( $plural_label ),
			'edit_item' => 'Edytuj: ' . $singular_label,
			'update_item' => 'Aktualizuj: ' . $singular_label,
			'add_new_item' => 'Dodaj nowy wpis: ' . $singular_label,
			'new_item_name' => 'Nazwa nowego wpisu',
			'menu_name' => $plural_label,
		);
		
		register_taxonomy( $key, array( 'kurs' ), array(
			'labels' => $labels,
			'hierarchical' => false,
			'public' => true,
			'show_ui' => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => true,
			'show_in_rest' => true,
			'query_var' => true,
			'rewrite' => array( 'slug' => self::TAXONOMIES[ $key ] ),
		) );
	}
}
