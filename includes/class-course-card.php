<?php

namespace WPCourses;

if ( ! defined( 'ABSPATH' ) ) exit;

class CourseCard {
	public static function render( $post_id ) {
		$category_color = self::get_category_color( $post_id );
		$category = self::get_first_term_name( $post_id, 'kategoria_kursu' );
		$place = self::get_first_term_name( $post_id, 'miejsce' );
		$lecturer = self::get_first_term_name( $post_id, 'wykladowca' );
		
		$date_from = Helpers::format_course_date( get_field( 'data_od', $post_id ) );
		$date_to = Helpers::format_course_date( get_field( 'data_do', $post_id ) );
		$date_range = ( $date_to && $date_to !== $date_from ) ? $date_from . ' - ' . $date_to : $date_from;
		
		$price_group = get_field( 'cena_za_kurs', $post_id );
		$price_info = Helpers::get_lowest_price( $price_group );
		
		$icons_url = WP_COURSES_URL . 'assets/img/';
		
		?>
		<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" class="kurs-card">
			<div class="thumb" style="background-color: <?php echo esc_attr( $category_color ); ?>;">
				<?php if ( $category ) : ?>
					<div class="cat-pill"><?php echo esc_html( $category ); ?></div>
				<?php endif; ?>
			</div>
			<div class="body">
				<h3><?php echo esc_html( get_the_title( $post_id ) ); ?></h3>
				
				<?php if ( $date_range ) : ?>
					<div class="meta-line">
						<img src="<?php echo esc_url( $icons_url . 'calendar-outline.png' ); ?>" alt="">
						<?php echo esc_html( $date_range ); ?>
					</div>
				<?php endif; ?>
				
				<?php if ( $place || $lecturer ) : ?>
					<div class="meta-line">
						<?php if ( $place ) : ?>
							<img src="<?php echo esc_url( $icons_url . 'location-marker-outline.png' ); ?>" alt="">
							<?php echo esc_html( $place ); ?>
						<?php endif; ?>
						<?php if ( $place && $lecturer ) echo '&middot;'; ?>
						<?php if ( $lecturer ) : ?>
							<img src="<?php echo esc_url( $icons_url . 'user-group-outline.png' ); ?>" alt="">
							<?php echo esc_html( $lecturer ); ?>
						<?php endif; ?>
					</div>
				<?php endif; ?>
				
				<hr class="body-hr">
				
				<div class="card-foot">
					<?php if ( $price_info ) : ?>
						<div class="price-from">
							<span class="price">od <?php echo esc_html( $price_info['cena'] ); ?> zł</span><br>
							<span class="price-tag"><?php echo esc_html( $price_info['etykieta'] ); ?></span>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</a>
		<?php
	}
	
	private static function get_category_image_url( $post_id ) {
		$terms = get_the_terms( $post_id, 'kategoria_kursu' );
		$fallback = WP_COURSES_URL . 'assets/img/placeholder-course.jpg';
		
		if ( ! $terms || is_wp_error( $terms ) ) {
			return $fallback;
		}
		
		$image = get_field( 'zdjecie', $terms[0] );
		
		if ( $image && is_array( $image ) ) {
			return isset( $image['sizes']['medium_large'] ) ? $image['sizes']['medium_large'] : $image['url'];
		}
		
		if ( is_string( $image ) && $image !== '' ) {
			return $image;
		}
		
		return $fallback;
	}
	
	private static function get_category_color( $post_id ) {
		$terms = get_the_terms( $post_id, 'kategoria_kursu' );
		$fallback = '#ECEEDF';
		
		if ( ! $terms || is_wp_error( $terms ) ) {
			return $fallback;
		}
		
		$color = get_field( 'kolor', $terms[0] );
		
		return ( $color && is_string( $color ) ) ? $color : $fallback;
	}
	
	private static function get_first_term_name( $post_id, $taxonomy ) {
		$terms = get_the_terms( $post_id, $taxonomy );
		
		return ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : '';
	}
}
