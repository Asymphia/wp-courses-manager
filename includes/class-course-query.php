<?php

namespace WPCourses;

if ( ! defined( 'ABSPATH' ) ) exit;

class CourseQuery {
	const TAXONOMY_FILTERS = array(
		'kategoria' => 'kategoria_kursu',
		'miejsce' => 'miejsce',
		'organizator' => 'organizator',
		'wykladowca' => 'wykladowca',
	);
	
	/**
	 * @param array $args {
	 *   @type string   $s           Search term (matches course title).
	 *   @type string[] $kategoria   Category slugs.
	 *   @type string[] $miejsce     Place slugs.
	 *   @type string[] $organizator Organizer slugs.
	 *   @type string[] $wykladowca  Lecturer slugs.
	 *   @type string   $data_od     Course start date filter (Y-m-d), lower bound.
	 *   @type string   $data_do     Course start date filter (Y-m-d), upper bound.
	 *   @type string   $sort        One of: data_kursu_asc, data_kursu_desc, data_dodania_asc, data_dodania_desc.
	 *   @type int      $take        Posts per page.
	 *   @type bool     $pagination  Whether pagination is enabled.
	 *   @type int      $paged       Current page number.
	 * }
	 */
	public static function render_results_html( array $args ) {
		$args = wp_parse_args( $args, array(
			's' => '',
			'kategoria' => array(),
			'miejsce' => array(),
			'organizator' => array(),
			'wykladowca' => array(),
			'data_od' => '',
			'data_do' => '',
			'sort' => 'data_kursu_asc',
			'take' => 9,
			'pagination' => true,
			'paged' => 1,
		));
		
		$query = new \WP_Query( self::build_query_args( $args ) );
		
		ob_start();
		
		echo '<div class="kurs-count-holder" data-count="' . intval( $query->found_posts ) . '"></div>';
		
		if ( $query->have_posts() ) {
			echo '<div class="kurs-grid">';
			
			while ( $query->have_posts() ) {
				$query->the_post();
				CourseCard::render( get_the_ID() );
			}
			
			echo '</div>';
			
			if ( $args['pagination'] && $query->max_num_pages > 1 ) {
				self::render_pagination( $query->max_num_pages, (int) $args['paged'] );
			}
		} else {
			echo '<p class="kurs-empty">Brak kursów spełniających wybrane kryteria.</p>';
		}
		
		wp_reset_postdata();
		
		return ob_get_clean();
	}
	
	private static function build_query_args( array $args ) {
		$query_args = array(
			'post_type' => 'kurs',
			'post_status' => 'publish',
			'posts_per_page' => intval( $args['take'] ) > 0 ? intval( $args['take'] ) : 9,
			'paged' => max( 1, intval( $args['paged'] ) ),
		);
		
		if ( !empty( $args['s'] ) ) {
			$query_args['s'] = sanitize_text_field( $args['s'] );
		}
		
		$tax_query = self::build_tax_query( $args );
		
		if ( count( $tax_query ) > 1 ) {
			$query_args['tax_query'] = $tax_query;
		}
		
		$meta_query = self::build_meta_query( $args );
		
		if ( count( $meta_query ) > 1 ) {
			$query_args['meta_query'] = $meta_query;
		}
		
		$query_args = array_merge( $query_args, self::build_orderby( $args['sort'] ) );
		
		return $query_args;
	}
	
	private static function build_tax_query( array $args ) {
		$tax_query = array( 'relation' => 'AND' );
		
		foreach ( self::TAXONOMY_FILTERS as $filter_key => $taxonomy ) {
			$slugs = array_filter( array_map( 'sanitize_title', (array) $args[ $filter_key ] ) );
			if ( ! empty( $slugs ) ) {
				$tax_query[] = array(
					'taxonomy' => $taxonomy,
					'field' => 'slug',
					'terms' => array_values( $slugs ),
				);
			}
		}
		
		return $tax_query;
	}
	
	private static function build_meta_query( array $args ) {
		$meta_query = array( 'relation' => 'AND' );
		
		if ( !empty( $args['data_od'] ) ) {
			$meta_query[] = array(
				'key' => 'data_od',
				'value' => self::date_input_to_acf_format( $args['data_od'] ),
				'compare' => '>=',
				'type' => 'DATE',
			);
		}
		if ( !empty( $args['data_do'] ) ) {
			$meta_query[] = array(
				'key' => 'data_od',
				'value' => self::date_input_to_acf_format( $args['data_do'] ),
				'compare' => '<=',
				'type' => 'DATE',
			);
		}
		
		return $meta_query;
	}
	
	private static function date_input_to_acf_format( $date_string ) {
		return str_replace( '-', '', sanitize_text_field( $date_string ) );
	}
	
	private static function build_orderby( $sort ) {
		switch ( $sort ) {
			case 'data_kursu_desc':
				return array( 'meta_key' => 'data_od', 'orderby' => 'meta_value', 'order' => 'DESC' );
			
			case 'data_dodania_desc':
				return array( 'orderby' => 'date', 'order' => 'DESC' );
			
			case 'data_dodania_asc':
				return array( 'orderby' => 'date', 'order' => 'ASC' );
			
			case 'data_kursu_asc':
			default:
				return array( 'meta_key' => 'data_od', 'orderby' => 'meta_value', 'order' => 'ASC' );
		}
	}
	
	private static function render_pagination( $max_pages, $current_page ) {
		echo '<div class="kurs-pagination" data-max="' . intval( $max_pages ) . '" data-current="' . intval( $current_page ) . '">';
		
		for ( $i = 1; $i <= $max_pages; $i++ ) {
			printf(
				'<button type="button" class="k-page %s" data-page="%d">%d</button>',
				$i === $current_page ? 'active' : '',
				$i,
				$i
			);
		}
		
		echo '</div>';
	}
}
