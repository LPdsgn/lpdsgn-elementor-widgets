<?php

defined('ABSPATH') || exit; // Exit if accessed directly

/**
 * Register BREADCRUMBS widget
 * @package lp-elementor-widgets
 * @version 2.0.0
 */
function register_breadcrumbs($widgets_manager) {
    require_once(get_stylesheet_directory() . '/widgets/breadcrumbs/breadcrumbs.php');
    $widgets_manager->register(new \Breadcrumbs());
}
add_action('elementor/widgets/register', 'register_breadcrumbs');

/**
 * Register SCROLL DOWN widgets
 * @package lp-elementor-widgets
 * @version 1.0.0
 */

# Register SCROLL DOWN SPINNER widget
function register_scrollDownSpinner($widgets_manager) {
    require_once(get_stylesheet_directory() . '/widgets/scrollDownSpinner/scrollDownSpinner.php');
    $widgets_manager->register(new \Scroll_Down_Spinner());
}
add_action('elementor/widgets/register', 'register_scrollDownSpinner');

# Register SCROLL DOWN INDICATOR widget
function register_scrollDownIndicator($widgets_manager) {
    require_once(get_stylesheet_directory() . '/widgets/scrollDownIndicator/scrollDownIndicator.php');
    $widgets_manager->register(new \Scroll_Down_Indicator());
}
add_action('elementor/widgets/register', 'register_scrollDownIndicator');

# Register widget's style
function registerStyle_scrollDownSpinner() {
    wp_register_style('scrollDown', get_stylesheet_directory_uri() . '/widgets/css/scrollDown.min.css');
}
add_action('wp_enqueue_scripts', 'registerStyle_scrollDownSpinner');

/**
 * Register Custom Shape Dividers
 * @package lp-elementor-widgets
 * @version 1.0.1
 */
require_once(get_stylesheet_directory() . '/widgets/shapeDividers/customShapeDividers.php');
