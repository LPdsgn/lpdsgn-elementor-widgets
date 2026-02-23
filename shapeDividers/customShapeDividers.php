<?php

defined( 'ABSPATH' ) || die;

/**
 * Add additional shape dividers to Elementor.
 *
 * @since 1.0.0
 * @param array $additional_shapes Additional Elementor shape dividers.
 */
function custom_elementor_shape_dividers( $additional_shapes ) {

	$additional_shapes['curve-opacity'] = [
		'title'        => esc_html__( 'Curve opacity', 'textdomain' ),
		'url'          => get_stylesheet_directory_uri() . '/widgets/shapeDividers/curveOpacity.svg',
		'path'         => get_stylesheet_directory() . '/widgets/shapeDividers/curveOpacity.svg',
		'has_flip'     => true,
		'has_negative' => true,
		'height_only'  => true,
	];

	return $additional_shapes;

}
add_filter( 'elementor/shapes/additional_shapes', 'custom_elementor_shape_dividers' );
