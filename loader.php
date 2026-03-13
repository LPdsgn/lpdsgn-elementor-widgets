<?php

defined( 'ABSPATH' ) || exit;

/**
 * LP Elementor Widgets — Entry Point
 *
 * Includi nel functions.php del child theme:
 *   require_once get_stylesheet_directory() . '/lp-elementor-widgets/loader.php';
 *
 * @package lp-elementor-widgets
 */

// Percorsi basati sulla posizione reale di questo file, indipendenti dal nome della cartella
define( 'LP_ELEMENTOR_PATH', __DIR__ . '/' );
define( 'LP_ELEMENTOR_URL', get_stylesheet_directory_uri() . '/' . ltrim(
	str_replace(
		wp_normalize_path( get_stylesheet_directory() ),
		'',
		wp_normalize_path( __DIR__ )
	),
	'/'
) . '/' );

// ── Widget generici (dipendono solo da Elementor core) ──────────────────────

/**
 * Register BREADCRUMBS, SCROLL DOWN SPINNER, SCROLL DOWN INDICATOR
 * @package lp-elementor-widgets
 */
add_action( 'elementor/widgets/register', function ( $widgets_manager ) {
	require_once LP_ELEMENTOR_PATH . 'widgets/breadcrumbs/breadcrumbs.php';
	require_once LP_ELEMENTOR_PATH . 'widgets/scrollDownSpinner/scrollDownSpinner.php';
	require_once LP_ELEMENTOR_PATH . 'widgets/scrollDownIndicator/scrollDownIndicator.php';

	$widgets_manager->register( new \Breadcrumbs() );
	$widgets_manager->register( new \Scroll_Down_Spinner() );
	$widgets_manager->register( new \Scroll_Down_Indicator() );
} );

// Stile condiviso scroll-down (registrato, non enqueued — i widget lo dichiarano in get_style_depends)
add_action( 'wp_enqueue_scripts', function () {
	wp_register_style( 'scrollDown', LP_ELEMENTOR_URL . 'assets/scrollDown.min.css' );
} );

/**
 * Register Custom Shape Dividers
 * @package lp-elementor-widgets
 * @version 1.0.1
 */
require_once LP_ELEMENTOR_PATH . 'widgets/shapeDividers/customShapeDividers.php';

// ── Modulo Veicoli (richiede ACF Pro + Elementor Pro) ───────────────────────

/**
 * Veicoli Elementor Widgets
 * Requires: ACF Pro, Elementor, Elementor Pro
 * @package lp-elementor-widgets
 * @version 1.0.0
 */
add_action( 'init', function () {
	if (
		class_exists( 'ACF' ) && defined( 'ACF_PRO' ) &&
		class_exists( '\Elementor\Plugin' ) &&
		class_exists( '\ElementorPro\Plugin' )
	) {
		require_once LP_ELEMENTOR_PATH . 'modules/veicoli/loader.php';
	}
}, 5 );
