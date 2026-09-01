<?php

namespace WPCourses;

if ( ! defined( 'ABSPATH' ) ) exit;

class Shortcode {
	const CHECKBOX_FILTERS = array(
		'kategoria' => array( 'kategoria_kursu', 'Kategoria' ),
		'miejsce' => array( 'miejsce', 'Miasto' ),
		'organizator' => array( 'organizator', 'Organizator' ),
		'wykladowca' => array( 'wykladowca', 'Wykładowca' ),
	);
	
	public function register_hooks() {
		add_shortcode( 'kursy', array( $this, 'render' ) );
	}
	
	public function render( $atts ) {
		$atts = shortcode_atts( array(
			'take' => 9,
			'pagination' => 'true',
			'category' => '',
			'filters' => 'true',
			'sorting' => 'true',
			'search' => 'true',
		), $atts, 'kursy' );
		
		$show_filters = filter_var( $atts['filters'], FILTER_VALIDATE_BOOLEAN );
		$show_sorting = filter_var( $atts['sorting'], FILTER_VALIDATE_BOOLEAN );
		$show_search = filter_var( $atts['search'], FILTER_VALIDATE_BOOLEAN );
		$pagination = filter_var( $atts['pagination'], FILTER_VALIDATE_BOOLEAN );
		
		$locked_category = $this->resolve_locked_category( $atts['category'] );
		
		$this->enqueue_assets();
		
		ob_start();
		?>
		<div class="kursy-wrapper<?php echo ( ! $show_filters && ! $show_sorting && ! $show_search ) ? ' no-toolbar' : ''; ?>"
		     data-take="<?php echo esc_attr( intval( $atts['take'] ) ); ?>"
		     data-pagination="<?php echo esc_attr( $pagination ? '1' : '0' ); ?>"
		     data-locked-category="<?php echo esc_attr( $locked_category ); ?>"
		>
			
			<?php if ( $show_filters || $show_sorting || $show_search ) : ?>
				<?php $this->render_toolbar( $show_filters, $show_sorting, $show_search, $locked_category ); ?>
			<?php endif; ?>
			
			<div class="kurs-results">
				<?php if ( $show_filters || $show_sorting || $show_search ) : ?>
					<div class="kurs-result-count"></div>
				<?php endif; ?>
				<?php
				echo CourseQuery::render_results_html( array(
					'take' => $atts['take'],
					'pagination' => $pagination,
					'paged' => 1,
					'kategoria' => $locked_category ? array( $locked_category ) : array(),
				) );
				?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
	
	private function resolve_locked_category( $category_attr ) {
		if ( $category_attr === 'auto' ) {
			return $this->get_current_course_category_slug();
		}
		
		if ( ! empty( $category_attr ) ) {
			return sanitize_title( $category_attr );
		}
		
		if ( is_tax( 'kategoria_kursu' ) ) {
			$queried = get_queried_object();
			
			if ( $queried && ! is_wp_error( $queried ) ) {
				return $queried->slug;
			}
		}
		
		return '';
	}
	
	private function get_current_course_category_slug() {
		if ( ! is_singular( 'kurs' ) ) {
			return '';
		}
		
		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return '';
		}
		
		$terms = get_the_terms( $post_id, 'kategoria_kursu' );
		if ( ! $terms || is_wp_error( $terms ) ) {
			return '';
		}
		
		return $terms[0]->slug;
	}
	
	private function render_toolbar( $show_filters, $show_sorting, $show_search, $locked_category ) {
		?>
		<div class="kurs-toolbar">
			
			<?php if ( $show_search ) : ?>
				<div class="k-field k-search">
					<input type="text" class="k-input" data-filter="s" placeholder="Szukaj po nazwie kursu…">
				</div>
			<?php endif; ?>
			
			<?php if ( $show_filters ) :
				foreach ( self::CHECKBOX_FILTERS as $filter_key => $tax_info ) {
					list( $taxonomy, $label ) = $tax_info;
					if ( $filter_key === 'kategoria' && $locked_category ) continue;
					$this->render_checkbox_group( $filter_key, $label, $taxonomy );
				}
				?>
				<div class="k-field">
					<label>Data od</label>
					<input type="date" class="k-input" data-filter="data_od">
				</div>
				<div class="k-field">
					<label>Data do</label>
					<input type="date" class="k-input" data-filter="data_do">
				</div>
			<?php endif; ?>
			
			<?php if ( $show_sorting ) : ?>
				<div class="k-field">
					<label>Sortuj</label>
					<select class="k-select" data-filter="sort">
						<option value="data_kursu_asc">Data kursu: najbliższe</option>
						<option value="data_kursu_desc">Data kursu: najdalsze</option>
						<option value="data_dodania_desc">Data dodania: najnowsze</option>
						<option value="data_dodania_asc">Data dodania: najstarsze</option>
					</select>
				</div>
			<?php endif; ?>
			
			<?php if ( $show_filters || $show_search ) : ?>
				<button type="button" class="k-clear">Wyczyść filtry</button>
			<?php endif; ?>
		</div>
		<?php
	}
	
	private function render_checkbox_group( $filter_key, $label, $taxonomy ) {
		$terms = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false ) );
		
		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return;
		}
		?>
		<div class="k-field k-checkgroup" data-filter="<?php echo esc_attr( $filter_key ); ?>">
			<label><?php echo esc_html( $label ); ?></label>
			<div class="k-check-list">
				<?php foreach ( $terms as $term ) : ?>
					<label class="k-check-option">
						<input type="checkbox" value="<?php echo esc_attr( $term->slug ); ?>">
						<span><?php echo esc_html( $term->name ); ?></span>
					</label>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}
	
	private function enqueue_assets() {
		wp_enqueue_style(
			'wp-courses-styles',
			WP_COURSES_URL . 'assets/css/course-styles.css',
			array(),
			WP_COURSES_VERSION
		);
		
		wp_enqueue_script(
			'wp-courses-filters',
			WP_COURSES_URL . 'assets/js/course-filters.js',
			array(),
			WP_COURSES_VERSION,
			true
		);
		
		wp_localize_script( 'wp-courses-filters', 'wpKursy', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'wp_kursy_nonce' ),
		) );
	}
}
