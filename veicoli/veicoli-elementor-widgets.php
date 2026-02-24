<?php
/**
 * Veicoli Elementor Widgets
 * Widgets custom per filtro veicoli e prezzo dinamico
 *
 * @package lp-elementor-widgets
 */

if ( ! defined( 'ABSPATH' ) )
	exit;

// Percorsi basati sulla directory del file
define( 'VEICOLI_WIDGETS_PATH', __DIR__ . '/' );
define( 'VEICOLI_WIDGETS_URL', get_stylesheet_directory_uri() . '/widgets/veicoli/' );

// Include ACF field definitions and taxonomies
require_once VEICOLI_WIDGETS_PATH . 'acf-fields.php';

// Include marca-modello relationship logic
require_once VEICOLI_WIDGETS_PATH . 'marca-modello-relation.php';

function register_veicoli_widgets( $widgets_manager ) {

	require_once( VEICOLI_WIDGETS_PATH . 'widgets/veicoli-filter-widget.php' );
	require_once( VEICOLI_WIDGETS_PATH . 'widgets/veicolo-pricing-widget.php' );
	require_once( VEICOLI_WIDGETS_PATH . 'widgets/veicolo-loop-info-widget.php' );

	$widgets_manager->register( new \Veicoli_Filter_Widget() );
	$widgets_manager->register( new \Veicolo_Pricing_Widget() );
	$widgets_manager->register( new \Veicolo_Loop_Info_Widget() );
}
add_action( 'elementor/widgets/register', 'register_veicoli_widgets' );


// Crea categoria custom per i widget
function add_veicoli_elementor_widget_categories( $elements_manager ) {
	$elements_manager->add_category(
		'veicoli',
		[
			'title' => esc_html__( 'Veicoli', 'veicoli' ),
			'icon' => 'fa fa-car',
		]
	);
}
add_action( 'elementor/elements/categories_registered', 'add_veicoli_elementor_widget_categories' );

// Enqueue scripts e styles
function veicoli_elementor_scripts() {
	// CSS
	wp_enqueue_style(
		'veicoli-widgets',
		VEICOLI_WIDGETS_URL . 'assets/veicoli-widgets.css',
		[],
		filemtime( VEICOLI_WIDGETS_PATH . 'assets/veicoli-widgets.css' )
	);

	// JavaScript
	wp_enqueue_script(
	    'veicoli-widgets',
	    VEICOLI_WIDGETS_URL . 'assets/veicoli-widget.js',
	    [ 'jquery' ],
	    file_exists( VEICOLI_WIDGETS_PATH . 'assets/veicoli-widget.js' ) ? filemtime( VEICOLI_WIDGETS_PATH . 'assets/veicoli-widget.js' ) : false,
	    true
	);

	// Localizza script per AJAX
	$popup_id_lungo_termine = '';
	if ( function_exists( 'get_field' ) ) {
		$popup_lungo_termine_field = get_field( 'popup_richiesta_preventivo', 'option' );
		if ( is_object( $popup_lungo_termine_field ) && isset( $popup_lungo_termine_field->ID ) ) {
			$popup_id_lungo_termine = $popup_lungo_termine_field->ID;
		} elseif ( is_numeric( $popup_lungo_termine_field ) ) {
			$popup_id_lungo_termine = $popup_lungo_termine_field;
		}
	}
	$popup_id_breve_termine = '';
	if ( function_exists( 'get_field' ) ) {
		$popup_breve_termine_field = get_field( 'popup_preventivo_breve_termine', 'option' );
		if ( is_object( $popup_breve_termine_field ) && isset( $popup_breve_termine_field->ID ) ) {
			$popup_id_breve_termine = $popup_breve_termine_field->ID;
		} elseif ( is_numeric( $popup_breve_termine_field ) ) {
			$popup_id_breve_termine = $popup_breve_termine_field;
		}
	}

	wp_localize_script( 'veicoli-widgets', 'veicoliAjax', [
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
		'nonce' => wp_create_nonce( 'veicoli_filter_nonce' ),
		'popupIdLungoTermine' => $popup_id_lungo_termine,
		'popupIdBreveTermine' => $popup_id_breve_termine,
	] );
}
add_action( 'wp_enqueue_scripts', 'veicoli_elementor_scripts' );

// Enqueue scripts anche nell'editor di Elementor
function veicoli_elementor_editor_scripts() {
	wp_enqueue_style(
		'veicoli-widgets-editor',
		VEICOLI_WIDGETS_URL . 'assets/veicoli-widgets.css',
		[],
		filemtime( VEICOLI_WIDGETS_PATH . 'assets/veicoli-widgets.css' )
	);
}
add_action( 'elementor/editor/after_enqueue_styles', 'veicoli_elementor_editor_scripts' );

// AJAX Handler per il filtro veicoli
add_action( 'wp_ajax_filter_veicoli_elementor', 'filter_veicoli_elementor' );
add_action( 'wp_ajax_nopriv_filter_veicoli_elementor', 'filter_veicoli_elementor' );

function filter_veicoli_elementor() {
	// Verifica nonce
	check_ajax_referer( 'veicoli_filter_nonce', 'nonce' );

	$args = array(
		'post_type' => 'veicolo',
		'posts_per_page' => -1,
		'post_status' => 'publish'
	);

	// Filtri tassonomie
	$tax_query = array( 'relation' => 'AND' );

	$taxonomies = [ 'produttore', 'modello', 'alimentazione', 'segmento', 'cambio' ];
	foreach ( $taxonomies as $tax ) {
		if ( ! empty( $_POST[ $tax ] ) ) {
			$tax_query[] = array(
				'taxonomy' => $tax,
				'field' => 'slug',
				'terms' => sanitize_text_field( $_POST[ $tax ] )
			);
		}
	}

	if ( count( $tax_query ) > 1 ) {
		$args['tax_query'] = $tax_query;
	}

	// Filtro ricerca testuale
	if ( ! empty( $_POST['search'] ) ) {
		$args['s'] = sanitize_text_field( $_POST['search'] );
	}

	$query = new WP_Query( $args );
	$filtered_ids = array();

	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			$post_id = get_the_ID();

			// Se ACF non è disponibile salta la logica basata sui campi custom
			if ( ! function_exists( 'get_field' ) ) {
				$filtered_ids[] = $post_id;
				continue;
			}

			// Filtro per anticipo
			$anticipo = get_field( 'anticipo', $post_id );
			if ( ! empty( $_POST['anticipo'] ) ) {
				$anticipo_filter = sanitize_text_field( $_POST['anticipo'] );
				if ( $anticipo_filter === 'si' && ! $anticipo ) {
					continue;
				}
				if ( $anticipo_filter === 'no' && $anticipo ) {
					continue;
				}
			}

			// Ottieni piani e trova prezzo minimo
			$piani = get_field( 'piani_noleggio', $post_id );
			$prezzo_min = PHP_INT_MAX;

			if ( $piani && is_array( $piani ) ) {
				foreach ( $piani as $piano ) {
					$prezzo = isset( $piano['prezzo_senza_anticipo'] ) ? floatval( $piano['prezzo_senza_anticipo'] ) : 0;
					if ( $prezzo > 0 && $prezzo < $prezzo_min ) {
						$prezzo_min = $prezzo;
					}
				}
			}

			// Filtro per range prezzo (solo se abbiamo un prezzo valido)
			if ( $prezzo_min !== PHP_INT_MAX ) {
				if ( ! empty( $_POST['prezzo_min'] ) && $prezzo_min < intval( $_POST['prezzo_min'] ) ) {
					continue;
				}
				if ( ! empty( $_POST['prezzo_max'] ) && $prezzo_min > intval( $_POST['prezzo_max'] ) ) {
					continue;
				}
			}

			$filtered_ids[] = $post_id;
		}
	}

	wp_reset_postdata();

	wp_send_json_success( [ 'post_ids' => $filtered_ids, 'total' => count( $filtered_ids ) ] );
}

// Proteggi i Dynamic Tags ACF da valori NULL nei repeater
add_filter( 'acf/format_value', 'veicoli_sanitize_acf_for_elementor', 10, 3 );

function veicoli_sanitize_acf_for_elementor( $value, $post_id, $field ) {
	// Solo per il repeater piani_noleggio
	if ( isset( $field['name'] ) && $field['name'] === 'piani_noleggio' ) {
		if ( is_null( $value ) || ( is_array( $value ) && empty( $value ) ) ) {
			return [];
		}
	}

	// Per tutti gli altri campi ACF usati nei Dynamic Tags
	if ( is_null( $value ) && did_action( 'elementor/loaded' ) ) {
		return '';
	}

	return $value;
}
