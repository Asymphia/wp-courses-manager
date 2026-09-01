<?php

namespace WPCourses;

if ( ! defined( 'ABSPATH' ) ) exit;

class SingleCourseBlocks {
	public function register_hooks() {
		add_shortcode( 'kurs_info', array( $this, 'render_info_list' ) );
		add_shortcode( 'kurs_cennik', array( $this, 'render_price_table' ) );
		add_shortcode( 'kurs_wykladowca', array( $this, 'render_lecturer' ) );
		add_shortcode( 'kurs_miejsce', array( $this, 'render_place' ) );
		add_shortcode( 'kurs_zapisy', array( $this, 'render_signup' ) );
		add_shortcode( 'kurs_data', array( $this, 'render_date_block' ) );
		add_shortcode( 'kurs_kategoria_zdjecie', array( $this, 'render_category_image' ) );
	}
	
	public function render_info_list() {
		$post_id = $this->current_course_id();
		if ( ! $post_id ) {
			return '';
		}
		
		$rows = array(
			'Miejsce' => $this->first_term_name( $post_id, 'miejsce' ),
			'Organizator' => $this->first_term_name( $post_id, 'organizator' ),
			'Wykładowca' => $this->first_term_name( $post_id, 'wykladowca' ),
		);
		
		$lowest = Helpers::get_lowest_price( get_field( 'cena_za_kurs', $post_id ) );
		if ( $lowest ) {
			$rows['Cena od'] = $lowest['cena'] . ' zł';
		}
		
		$rows = array_filter( $rows, function ( $v ) {
			return $v !== '' && $v !== null;
		} );
		
		if ( empty( $rows ) ) {
			return '';
		}
		
		ob_start();
		?>
		<div class="wpc-info-list">
			<?php foreach ( $rows as $label => $value ) : ?>
				<div class="wpc-info-row">
					<span class="wpc-info-label"><?php echo esc_html( $label ); ?>:</span>
					<span class="wpc-info-value"><?php echo esc_html( $value ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
		return ob_get_clean();
	}
	
	public function render_price_table() {
		$post_id = $this->current_course_id();
		if ( ! $post_id ) {
			return '';
		}
		
		$price_group = get_field( 'cena_za_kurs', $post_id );
		$rows = Helpers::get_all_prices( $price_group );
		
		if ( empty( $rows ) ) {
			return '';
		}
		
		ob_start();
		?>
		<div class="kurs-body">
			<div>
				<div class="price-table">
					<?php foreach ( $rows as $i => $row ) : ?>
						<div class="price-row<?php echo $i === 0 ? ' highlight' : ''; ?>">
							<div class="name"><?php echo esc_html( $row['label'] ); ?></div>
							<div class="amount"><?php echo esc_html( $row['amount'] ); ?> zł</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
	
	public function render_lecturer() {
		$post_id = $this->current_course_id();
		if ( ! $post_id ) {
			return '';
		}
		
		$terms = get_the_terms( $post_id, 'wykladowca' );
		if ( ! $terms || is_wp_error( $terms ) ) {
			return '';
		}
		
		$term = $terms[0];
		
		$name = $term->name;
		$photo = $this->term_image_url( $term, 'zdjecie' );
		$phone = get_field( 'telefon', $term );
		$email = get_field( 'e-mail', $term );
		$website = get_field( 'strona', $term );
		
		$icons_url = WP_COURSES_URL . 'assets/img/';
		$placeholder_url = WP_COURSES_URL . 'assets/img/placeholder-avatar.png';
		
		ob_start();
		?>
		<div class="wpc-lecturer">
			<img class="wpc-lecturer-photo" src="<?php echo esc_url( $photo ?: $placeholder_url ); ?>" alt="<?php echo esc_attr( $name ); ?>">
			<div class="wpc-lecturer-info">
				<h3 class="wpc-lecturer-name"><?php echo esc_html( $name ); ?></h3>
				<p class="wpc-lecturer-role">Wykładowca</p>
				
				<div class="wpc-lecturer-contact-row">
					<img src="<?php echo esc_url( $icons_url . 'phone-outline.png' ); ?>" alt="">
					<span><?php echo $phone ? esc_html( $phone ) : 'nie podano'; ?></span>
				</div>
				
				<div class="wpc-lecturer-contact-row">
					<img src="<?php echo esc_url( $icons_url . 'mail-outline.png' ); ?>" alt="">
					<span><?php echo $email ? esc_html( $email ) : 'nie podano'; ?></span>
				</div>
				
				<div class="wpc-lecturer-contact-row">
					<img src="<?php echo esc_url( $icons_url . 'link-outline.png' ); ?>" alt="">
					<span><?php echo $website ? '<a href="' . esc_url( $website ) . '" target="_blank" rel="noopener">' . esc_html( $website ) . '</a>' : 'nie podano'; ?></span>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
	
	public function render_place() {
		$post_id = $this->current_course_id();
		if ( ! $post_id ) {
			return '';
		}
		
		$place   = $this->first_term_name( $post_id, 'miejsce' );
		$address = get_field( 'adres_kursu', $post_id );
		
		$text = trim( $place . ( $place && $address ? ', ' : '' ) . $address );
		if ( ! $text ) {
			return '';
		}
		
		$icons_url = WP_COURSES_URL . 'assets/img/';
		
		ob_start();
		?>
		<div class="wpc-place">
			<h3>Miejsce kursu</h3>
			<div class="wpc-place-row">
				<img src="<?php echo esc_url( $icons_url . 'location-marker-outline.png' ); ?>" alt="">
				<span><?php echo esc_html( $text ); ?></span>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
	
	public function render_signup() {
		$post_id = $this->current_course_id();
		if ( ! $post_id ) {
			return '';
		}
		
		$signup  = get_field( 'informacje_do_zapisow', $post_id );
		if ( ! is_array( $signup ) ) {
			return '';
		}
		
		$phone = $signup['telefon'] ?? '';
		$email = $signup['e-mail'] ?? '';
		$website = $signup['strona_do_zapisow'] ?? '';
		
		if ( ! $phone && ! $email && ! $website ) {
			return '';
		}
		
		$icons_url = WP_COURSES_URL . 'assets/img/';
		$rows      = array();
		
		if ( $phone ) {
			$rows[] = array( 'icon' => 'phone-outline-white.png', 'html' => '<a href="tel:' . esc_attr( preg_replace( '/\s+/', '', $phone ) ) . '">' . esc_html( $phone ) . '</a>' );
		}
		if ( $email ) {
			$rows[] = array( 'icon' => 'mail-outline-white.png', 'html' => '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>' );
		}
		if ( $website ) {
			$rows[] = array( 'icon' => 'link-outline-white.png', 'html' => '<a href="' . esc_url( $website ) . '" target="_blank" rel="noopener">' . esc_html( $website ) . '</a>' );
		}
		
		ob_start();
		?>
		<div class="wpc-signup">
			<h3>Zapisy na kurs</h3>
			<div class="wpc-signup-list">
				<?php foreach ( $rows as $i => $row ) : ?>
					<?php if ( $i > 0 ) : ?><hr class="wpc-signup-divider"><?php endif; ?>
					<div class="wpc-signup-row">
						<img src="<?php echo esc_url( $icons_url . $row['icon'] ); ?>" alt="">
						<span><?php echo $row['html']; ?></span>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
	
	public function render_date_block() {
		$post_id = $this->current_course_id();
		if ( ! $post_id ) {
			return '';
		}
		
		$raw_from = get_field( 'data_od', $post_id );
		$raw_to   = get_field( 'data_do', $post_id );
		
		$from = Helpers::format_course_date( $raw_from );
		$to   = Helpers::format_course_date( $raw_to );
		
		if ( ! $from ) {
			return '';
		}
		
		$title = ( $to && $to !== $from ) ? $from . ' – ' . $to : $from;
		
		$days_count = Helpers::count_days( $raw_from, $raw_to );
		$place      = $this->first_term_name( $post_id, 'miejsce' );
		
		$subtitle_parts = array();
		if ( $days_count ) {
			$subtitle_parts[] = $days_count . ( $days_count === 1 ? ' dzień' : ' dni' );
		}
		if ( $place ) {
			$subtitle_parts[] = $place;
		}
		$subtitle = implode( ' &middot; ', array_map( 'esc_html', $subtitle_parts ) );
		
		$icons_url = WP_COURSES_URL . 'assets/img/';
		
		ob_start();
		?>
		<div class="wpc-date-block">
			<img src="<?php echo esc_url( $icons_url . 'calendar-outline.png' ); ?>" alt="">
			<div class="wpc-date-block-info">
				<h3><?php echo esc_html( $title ); ?></h3>
				<?php if ( $subtitle ) : ?>
					<span><?php echo $subtitle; ?></span>
				<?php endif; ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
	
	public function render_category_image() {
		$post_id = $this->current_course_id();
		if ( ! $post_id ) {
			return '';
		}
		
		$terms = get_the_terms( $post_id, 'kategoria_kursu' );
		if ( ! $terms || is_wp_error( $terms ) ) {
			return '';
		}
		
		$term = $terms[0];
		$image_url = $this->term_image_url( $term, 'zdjecie' );
		if ( ! $image_url ) {
			return '';
		}
		
		ob_start();
		?>
		<img class="wpc-category-image" src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $term->name ); ?>">
		<?php
		return ob_get_clean();
	}
	
	private function current_course_id() {
		$post_id = get_the_ID();
		if ( ! $post_id || get_post_type( $post_id ) !== 'kurs' ) {
			return 0;
		}
		
		return $post_id;
	}
	
	private function first_term_name( $post_id, $taxonomy ) {
		$terms = get_the_terms( $post_id, $taxonomy );
		return ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : '';
	}
	
	private function term_image_url( $term, $field_name ) {
		$image = get_field( $field_name, $term );
		if ( $image && is_array( $image ) ) {
			return $image['sizes']['medium'] ?? $image['url'] ?? '';
		}
		if ( is_string( $image ) && $image !== '' ) {
			return $image;
		}
		return '';
	}
}