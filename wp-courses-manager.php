<?php

/**
 * Plugin Name:       WP Courses Manager
 * Plugin URI:        https://github.com/Asymphia/wp-courses-manager
 * Description:       Registers CPT "Kurs", related taxonomies and shortcode [kursy] with filtering, sorting and AJAX searching
 * Version:           1.0.4
 * Requires PHP:      7.4
 * Author:            Asymphia
 * Text Domain:       wp-courses-manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WP_COURSES_VERSION', '1.0.4' );
define( 'WP_COURSES_FILE', __FILE__ );
define( 'WP_COURSES_PATH', plugin_dir_path( __FILE__ ) );
define( 'WP_COURSES_URL', plugin_dir_url( __FILE__ ) );

require_once WP_COURSES_PATH . 'includes/class-helpers.php';
require_once WP_COURSES_PATH . 'includes/class-post-types.php';
require_once WP_COURSES_PATH . 'includes/class-taxonomies.php';
require_once WP_COURSES_PATH . 'includes/class-admin-taxonomy-fields.php';
require_once WP_COURSES_PATH . 'includes/class-course-card.php';
require_once WP_COURSES_PATH . 'includes/class-course-query.php';
require_once WP_COURSES_PATH . 'includes/class-shortcode.php';
require_once WP_COURSES_PATH . 'includes/class-ajax-handler.php';
require_once WP_COURSES_PATH . 'includes/class-single-course-tags.php';
require_once WP_COURSES_PATH . 'includes/class-single-course-blocks.php';

final class WP_Courses_Manager {
	private static $instance = null;
	
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	private function __construct() {
		$this->init_modules();
		
		register_activation_hook( WP_COURSES_FILE, array( $this, 'on_activation' ) );
		register_deactivation_hook( WP_COURSES_FILE, array( $this, 'on_deactivation' ) );
	}
	
	private function init_modules() {
		( new WPCourses\PostTypes() )->register_hooks();
		( new WPCourses\Taxonomies() )->register_hooks();
		( new WPCourses\AdminTaxonomyFields() )->register_hooks();
		( new WPCourses\Shortcode() )->register_hooks();
		( new WPCourses\AjaxHandler() )->register_hooks();
		( new WPCourses\SingleCourseTags() )->register_hooks();
		( new WPCourses\SingleCourseBlocks() )->register_hooks();
	}
	
	public function on_activation() {
		( new WPCourses\PostTypes() )->register_post_type();
		( new WPCourses\Taxonomies() )->register_all_taxonomies();
		
		flush_rewrite_rules();
	}
	
	public function on_deactivation() {
		flush_rewrite_rules();
	}
}

WP_Courses_Manager::instance();