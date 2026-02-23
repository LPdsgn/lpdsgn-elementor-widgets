<?php

use Elementor\Controls_Manager;
use Elementor\Icons_Manager;
use Elementor\Group_Control_Typography;

/**
 * Advanced Breadcrumbs Widget for Elementor
 *
 * @package lp-elementor-widgets
 * @version 2.0.0
 *
 * Features:
 * - Yoast SEO / RankMath primary category support
 * - Manual primary category selection
 * - Schema.org JSON-LD structured data
 * - Proper taxonomy hierarchy via get_ancestors()
 * - Custom Post Type + custom taxonomy support
 * - WooCommerce product support
 */
class Breadcrumbs extends \Elementor\Widget_Base {

	public function get_name() {
		return 'advanced-breadcrumbs';
	}

	public function get_title() {
		return esc_html__( 'Advanced Breadcrumbs', 'lp-widgets' );
	}

	public function get_icon() {
		return 'eicon-navigation-horizontal';
	}

	public function get_categories() {
		return [ 'general' ];
	}

	public function get_keywords() {
		return [ 'breadcrumbs', 'navigazione', 'navigation', 'seo', 'schema' ];
	}

	public function get_style_depends() {
		return [];
	}

	// ──────────────────────────────────────────────
	//  Helper: get all categories for manual dropdown
	// ──────────────────────────────────────────────
	protected function get_all_categories() {
		$options = [ '' => __( '— Select —', 'lp-widgets' ) ];
		$cats = get_categories( [ 'hide_empty' => false ] );
		if ( ! empty( $cats ) && ! is_wp_error( $cats ) ) {
			foreach ( $cats as $cat ) {
				$prefix = '';
				if ( $cat->parent ) {
					$ancestors = get_ancestors( $cat->term_id, 'category', 'taxonomy' );
					$prefix = str_repeat( '— ', count( $ancestors ) );
				}
				$options[ $cat->term_id ] = $prefix . $cat->name;
			}
		}
		return $options;
	}

	// ──────────────────────────────────────────────
	//  Helper: determine primary category
	// ──────────────────────────────────────────────
	protected function get_primary_category( $post_id, $taxonomy, $source, $manual_cat_id = '' ) {
		$primary = null;

		switch ( $source ) {
			case 'yoast':
				if ( class_exists( 'WPSEO_Primary_Term' ) ) {
					$yoast_primary = new \WPSEO_Primary_Term( $taxonomy, $post_id );
					$term_id = $yoast_primary->get_primary_term();
					if ( $term_id ) {
						$term = get_term( $term_id, $taxonomy );
						if ( $term && ! is_wp_error( $term ) ) {
							$primary = $term;
						}
					}
				}
				break;

			case 'rankmath':
				if ( class_exists( 'RankMath' ) ) {
					$term_id = get_post_meta( $post_id, 'rank_math_primary_' . $taxonomy, true );
					if ( $term_id ) {
						$term = get_term( (int) $term_id, $taxonomy );
						if ( $term && ! is_wp_error( $term ) ) {
							$primary = $term;
						}
					}
				}
				break;

			case 'manual':
				if ( ! empty( $manual_cat_id ) ) {
					$term = get_term( (int) $manual_cat_id, $taxonomy );
					if ( $term && ! is_wp_error( $term ) ) {
						$primary = $term;
					}
				}
				break;
		}

		// Fallback: first assigned term
		if ( ! $primary ) {
			$terms = get_the_terms( $post_id, $taxonomy );
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				$primary = $terms[0];
			}
		}

		return $primary;
	}

	// ──────────────────────────────────────────────
	//  Helper: build full ancestor chain for a term
	// ──────────────────────────────────────────────
	protected function build_term_hierarchy( $term, $taxonomy ) {
		$chain = [];
		$ancestors = get_ancestors( $term->term_id, $taxonomy, 'taxonomy' );
		$ancestors = array_reverse( $ancestors );

		foreach ( $ancestors as $ancestor_id ) {
			$ancestor = get_term( $ancestor_id, $taxonomy );
			if ( $ancestor && ! is_wp_error( $ancestor ) ) {
				$chain[] = [
					'title' => $ancestor->name,
					'url' => get_term_link( $ancestor->term_id, $taxonomy ),
				];
			}
		}

		return $chain;
	}

	// ──────────────────────────────────────────────
	//  Helper: get hierarchical public taxonomy for CPT
	// ──────────────────────────────────────────────
	protected function get_cpt_taxonomy( $post_type ) {
		$taxonomies = get_object_taxonomies( $post_type, 'objects' );
		foreach ( $taxonomies as $tax ) {
			if ( $tax->public && $tax->hierarchical ) {
				return $tax->name;
			}
		}
		// Fallback: first public taxonomy
		foreach ( $taxonomies as $tax ) {
			if ( $tax->public ) {
				return $tax->name;
			}
		}
		return null;
	}

	// ──────────────────────────────────────────────
	//  Helper: output breadcrumbs HTML + Schema
	// ──────────────────────────────────────────────
	protected function output_breadcrumbs( $breadcrumbs, $separator_type, $separator_text, $separator_icon, $enable_schema, $settings = [] ) {

		// Schema.org JSON-LD
		if ( $enable_schema && ! empty( $breadcrumbs ) ) {
			$items = [];
			foreach ( $breadcrumbs as $i => $crumb ) {
				$item = [
					'@type' => 'ListItem',
					'position' => $i + 1,
					'name' => $crumb['title'],
				];
				if ( ! empty( $crumb['url'] ) ) {
					$item['item'] = $crumb['url'];
				}
				$items[] = $item;
			}
			$schema = [
				'@context' => 'https://schema.org',
				'@type' => 'BreadcrumbList',
				'itemListElement' => $items,
			];
			echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>';
		}

		// Build separator markup once
		if ( $separator_type === 'icon' ) {
			$migrated = isset( $settings['__fa4_migrated']['separator_icon'] );
			$is_new = empty( $settings['icon'] ?? '' ) && Icons_Manager::is_migration_allowed();

			ob_start();
			if ( $is_new || $migrated ) :
				Icons_Manager::render_icon( $separator_icon, [ 'aria-hidden' => 'true' ] );
			else : ?>
				<i class="<?php echo esc_attr( $separator_icon['value'] ?? '' ); ?>" aria-hidden="true"></i>
			<?php endif;
			$sep_html = '<span class="separator">' . ob_get_clean() . '</span>';
		} else {
			$sep_html = '<span class="separator">' . esc_html( $separator_text ) . '</span>';
		}

		// STYLE
		$style = '
			.breadcrumbs { display: flex; align-items: center; flex-wrap: nowrap; overflow: hidden; }
			.breadcrumbs > a,
			.breadcrumbs > .current { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; min-width: 0; flex-shrink: 1; }
			.breadcrumbs > a:not(:last-child) { flex-shrink: 0; }
			.breadcrumbs .separator { line-height: 1; display: inline-flex; align-items: center; flex-shrink: 0; }
			.breadcrumbs .breadcrumb-home { flex-shrink: 0; }
		';

		// HTML
		echo "<style>$style</style>";

		echo '<nav class="breadcrumbs" aria-label="Breadcrumb">';
		$last = count( $breadcrumbs ) - 1;
		foreach ( $breadcrumbs as $index => $breadcrumb ) {
			if ( $index > 0 ) {
				echo $sep_html;
			}
			$label = ! empty( $breadcrumb['html'] ) ? $breadcrumb['html'] : esc_html( $breadcrumb['title'] );
			$extra_class = ! empty( $breadcrumb['class'] ) ? ' ' . esc_attr( $breadcrumb['class'] ) : '';

			if ( $index === $last || empty( $breadcrumb['url'] ) ) {
				echo '<span class="current' . $extra_class . '">' . $label . '</span>';
			} else {
				echo '<a href="' . esc_url( $breadcrumb['url'] ) . '" class="' . trim( $extra_class ) . '">' . $label . '</a>';
			}
		}
		echo '</nav>';
	}

	// ══════════════════════════════════════════════
	//  CONTROLS
	// ══════════════════════════════════════════════
	protected function register_controls() {

		// ── Content tab ──────────────────────────
		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'Impostazioni', 'lp-widgets' ),
			]
		);

		$this->add_control(
			'separator_type',
			[
				'label' => __( 'Separator Type', 'lp-widgets' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'text' => [
						'title' => __( 'Text', 'lp-widgets' ),
						'icon' => 'eicon-font',
					],
					'icon' => [
						'title' => __( 'Icon', 'lp-widgets' ),
						'icon' => 'eicon-star',
					],
				],
				'default' => 'text',
				'toggle' => false,
			]
		);

		$this->add_control(
			'separator',
			[
				'label' => __( 'Separator', 'lp-widgets' ),
				'type' => Controls_Manager::TEXT,
				'default' => "\u{00BB}", // »
				'condition' => [
					'separator_type' => 'text',
				],
			]
		);

		$this->add_control(
			'separator_icon',
			[
				'label' => __( 'Separator Icon', 'lp-widgets' ),
				'type' => Controls_Manager::ICONS,
				'fa4compatibility' => 'icon',
				'skin' => 'inline',
				'label_block' => false,
				'default' => [
					'value' => 'fas fa-chevron-right',
					'library' => 'fa-solid',
				],
				'condition' => [
					'separator_type' => 'icon',
				],
			]
		);

		$this->add_control(
			'show_home',
			[
				'label' => __( 'Show Home', 'lp-widgets' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __( 'Show', 'lp-widgets' ),
				'label_off' => __( 'Hide', 'lp-widgets' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'home_type',
			[
				'label' => __( 'Home Display', 'lp-widgets' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'text' => [
						'title' => __( 'Text', 'lp-widgets' ),
						'icon' => 'eicon-font',
					],
					'icon' => [
						'title' => __( 'Icon', 'lp-widgets' ),
						'icon' => 'eicon-home',
					],
				],
				'default' => 'text',
				'toggle' => false,
				'condition' => [
					'show_home' => 'yes',
				],
			]
		);

		$this->add_control(
			'home_text',
			[
				'label' => __( 'Home Text', 'lp-widgets' ),
				'type' => Controls_Manager::TEXT,
				'default' => 'Home',
				'condition' => [
					'show_home' => 'yes',
					'home_type' => 'text',
				],
			]
		);

		$this->add_control(
			'home_icon',
			[
				'label' => __( 'Home Icon', 'lp-widgets' ),
				'type' => Controls_Manager::ICONS,
				'fa4compatibility' => 'home_icon_legacy',
				'skin' => 'inline',
				'label_block' => false,
				'default' => [
					'value' => 'fas fa-home',
					'library' => 'fa-solid',
				],
				'condition' => [
					'show_home' => 'yes',
					'home_type' => 'icon',
				],
			]
		);

		$this->add_control(
			'show_archive',
			[
				'label' => __( 'Show Archive', 'lp-widgets' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __( 'Show', 'lp-widgets' ),
				'label_off' => __( 'Hide', 'lp-widgets' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_primary_category',
			[
				'label' => __( 'Show Primary Category', 'lp-widgets' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __( 'Show', 'lp-widgets' ),
				'label_off' => __( 'Hide', 'lp-widgets' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'primary_category_source',
			[
				'label' => __( 'Primary Category Source', 'lp-widgets' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'auto',
				'options' => [
					'auto' => __( 'Auto (First Category)', 'lp-widgets' ),
					'yoast' => __( 'Yoast SEO Primary', 'lp-widgets' ),
					'rankmath' => __( 'RankMath Primary', 'lp-widgets' ),
					'manual' => __( 'Manual Selection', 'lp-widgets' ),
				],
				'condition' => [
					'show_primary_category' => 'yes',
				],
			]
		);

		$this->add_control(
			'manual_primary_category',
			[
				'label' => __( 'Select Primary Category', 'lp-widgets' ),
				'type' => Controls_Manager::SELECT,
				'options' => $this->get_all_categories(),
				'default' => '',
				'condition' => [
					'show_primary_category' => 'yes',
					'primary_category_source' => 'manual',
				],
			]
		);

		$this->add_control(
			'show_post_category',
			[
				'label' => __( 'Show Parent Categories', 'lp-widgets' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __( 'Show', 'lp-widgets' ),
				'label_off' => __( 'Hide', 'lp-widgets' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'max_child_categories',
			[
				'label' => __( 'Max Child Categories', 'lp-widgets' ),
				'type' => Controls_Manager::NUMBER,
				'description' => __( 'Maximum number of secondary categories to display.', 'lp-widgets' ),
				'default' => 3,
				'condition' => [
					'show_post_category' => 'yes',
				],
			]
		);

		$this->add_control(
			'show_current_post',
			[
				'label' => __( 'Show Current Post', 'lp-widgets' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __( 'Show', 'lp-widgets' ),
				'label_off' => __( 'Hide', 'lp-widgets' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'enable_schema',
			[
				'label' => __( 'Enable Schema.org Markup', 'lp-widgets' ),
				'type' => Controls_Manager::SWITCHER,
				'description' => __( 'Outputs JSON-LD BreadcrumbList for SEO.', 'lp-widgets' ),
				'label_on' => __( 'Yes', 'lp-widgets' ),
				'label_off' => __( 'No', 'lp-widgets' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->end_controls_section();

		// ── Style tab ────────────────────────────
		$this->start_controls_section(
			'section_style',
			[
				'label' => __( 'Style', 'lp-widgets' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'text_color',
			[
				'label' => __( 'Text Color', 'lp-widgets' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .breadcrumbs' => 'color: {{VALUE}};',
					'{{WRAPPER}} .breadcrumbs a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'separator_color',
			[
				'label' => __( 'Separator Color', 'lp-widgets' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .breadcrumbs .separator' => 'color: {{VALUE}};',
					'{{WRAPPER}} .breadcrumbs .separator svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'separator_icon_size',
			[
				'label' => __( 'Icon Size', 'lp-widgets' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range' => [
					'px' => [ 'min' => 4, 'max' => 40 ],
					'em' => [ 'min' => 0.2, 'max' => 3, 'step' => 0.1 ],
				],
				'default' => [ 'size' => 12, 'unit' => 'px' ],
				'selectors' => [
					'{{WRAPPER}} .breadcrumbs .separator i' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .breadcrumbs .separator svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'separator_type' => 'icon',
				],
			]
		);

		$this->add_responsive_control(
			'separator_icon_vertical',
			[
				'label' => __( 'Icon Vertical Offset', 'lp-widgets' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [ 'min' => -10, 'max' => 10 ],
				],
				'default' => [ 'size' => 0, 'unit' => 'px' ],
				'selectors' => [
					'{{WRAPPER}} .breadcrumbs .separator i' => 'position: relative; top: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .breadcrumbs .separator svg' => 'position: relative; top: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'separator_type' => 'icon',
				],
			]
		);

		$this->add_control(
			'current_color',
			[
				'label' => __( 'Current Item Color', 'lp-widgets' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .breadcrumbs .current' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'link_hover_color',
			[
				'label' => __( 'Link Hover Color', 'lp-widgets' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .breadcrumbs a:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'heading_home_icon_style',
			[
				'label' => __( 'Home Icon', 'lp-widgets' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'show_home' => 'yes',
					'home_type' => 'icon',
				],
			]
		);

		$this->add_control(
			'home_icon_color',
			[
				'label' => __( 'Home Icon Color', 'lp-widgets' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .breadcrumbs .breadcrumb-home i' => 'color: {{VALUE}};',
					'{{WRAPPER}} .breadcrumbs .breadcrumb-home svg' => 'fill: {{VALUE}};',
				],
				'condition' => [
					'show_home' => 'yes',
					'home_type' => 'icon',
				],
			]
		);

		$this->add_responsive_control(
			'home_icon_size',
			[
				'label' => __( 'Home Icon Size', 'lp-widgets' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range' => [
					'px' => [ 'min' => 8, 'max' => 40 ],
					'em' => [ 'min' => 0.5, 'max' => 3, 'step' => 0.1 ],
				],
				'default' => [ 'size' => 14, 'unit' => 'px' ],
				'selectors' => [
					'{{WRAPPER}} .breadcrumbs .breadcrumb-home i' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .breadcrumbs .breadcrumb-home svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'show_home' => 'yes',
					'home_type' => 'icon',
				],
			]
		);

		$this->add_responsive_control(
			'separator_spacing',
			[
				'label' => __( 'Separator Spacing', 'lp-widgets' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range' => [
					'px' => [ 'min' => 0, 'max' => 30 ],
					'em' => [ 'min' => 0, 'max' => 3, 'step' => 0.1 ],
				],
				'default' => [ 'size' => 5, 'unit' => 'px' ],
				'selectors' => [
					'{{WRAPPER}} .breadcrumbs .separator' => 'margin-left: {{SIZE}}{{UNIT}}; margin-right: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'typography',
				'label' => __( 'Typography', 'lp-widgets' ),
				'selector' => '{{WRAPPER}} .breadcrumbs, {{WRAPPER}} .breadcrumbs a',
			]
		);

		$this->end_controls_section();
	}

	// ══════════════════════════════════════════════
	//  RENDER
	// ══════════════════════════════════════════════
	protected function render() {
		$settings = $this->get_settings_for_display();

		$separator_type = $settings['separator_type'] ?? 'text';
		$separator_text = $settings['separator'] ?? '';
		$separator_icon = $settings['separator_icon'] ?? [];
		$show_home = $settings['show_home'] === 'yes';
		$show_archive = $settings['show_archive'] === 'yes';
		$show_primary_category = $settings['show_primary_category'] === 'yes';
		$primary_source = $settings['primary_category_source'] ?? 'auto';
		$manual_cat_id = $settings['manual_primary_category'] ?? '';
		$show_post_category = $settings['show_post_category'] === 'yes';
		$max_child_categories = (int) $settings['max_child_categories'];
		$show_current_post = $settings['show_current_post'] === 'yes';
		$enable_schema = $settings['enable_schema'] === 'yes';

		global $post;
		$blog_page_id = get_option( 'page_for_posts' );
		$breadcrumbs = [];

		// ── Home ─────────────────────────────────
		if ( $show_home ) {
			$home_type = $settings['home_type'] ?? 'text';
			$home_label = '';

			if ( $home_type === 'icon' ) {
				$home_icon = $settings['home_icon'] ?? [];
				$migrated_home = isset( $settings['__fa4_migrated']['home_icon'] );
				$is_new_home = empty( $settings['home_icon_legacy'] ?? '' ) && Icons_Manager::is_migration_allowed();

				ob_start();
				if ( $is_new_home || $migrated_home ) :
					Icons_Manager::render_icon( $home_icon, [ 'aria-hidden' => 'true' ] );
				else : ?>
					<i class="<?php echo esc_attr( $home_icon['value'] ?? '' ); ?>" aria-hidden="true"></i>
				<?php endif;
				$home_label = ob_get_clean();
			} else {
				$home_label = esc_html( $settings['home_text'] ?? __( 'Home', 'lp-widgets' ) );
			}

			$breadcrumbs[] = [
				'title' => __( 'Home', 'lp-widgets' ),
				'url' => home_url( '/' ),
				'html' => $home_label,
				'class' => ( $home_type === 'icon' ) ? 'breadcrumb-home' : '',
			];
		}

		// ── Single post / CPT ────────────────────
		if ( is_single() ) {
			$post_type = get_post_type( $post );

			// Archive link
			if ( $show_archive ) {
				if ( $post_type === 'post' && $blog_page_id ) {
					$breadcrumbs[] = [
						'title' => get_the_title( $blog_page_id ),
						'url' => get_permalink( $blog_page_id ),
					];
				} elseif ( $post_type !== 'post' ) {
					$pto = get_post_type_object( $post_type );
					if ( $pto && ! empty( $pto->has_archive ) ) {
						$breadcrumbs[] = [
							'title' => $pto->labels->name,
							'url' => get_post_type_archive_link( $post_type ),
						];
					}
				}
			}

			// Categories / taxonomy
			if ( $show_post_category ) {
				$taxonomy = ( $post_type === 'post' )
					? 'category'
					: $this->get_cpt_taxonomy( $post_type );

				if ( $taxonomy ) {
					$primary = $this->get_primary_category( $post->ID, $taxonomy, $primary_source, $manual_cat_id );

					if ( $primary ) {
						// Build full ancestor chain for primary
						if ( $show_primary_category ) {
							$ancestors = $this->build_term_hierarchy( $primary, $taxonomy );
							$breadcrumbs = array_merge( $breadcrumbs, $ancestors );
							$breadcrumbs[] = [
								'title' => $primary->name,
								'url' => get_term_link( $primary->term_id, $taxonomy ),
							];
						}

						// Secondary categories
						$all_terms = get_the_terms( $post->ID, $taxonomy );
						if ( ! empty( $all_terms ) && ! is_wp_error( $all_terms ) ) {
							$count = 0;
							foreach ( $all_terms as $term ) {
								if ( $term->term_id !== $primary->term_id ) {
									if ( $count >= $max_child_categories ) {
										break;
									}
									$breadcrumbs[] = [
										'title' => $term->name,
										'url' => get_term_link( $term->term_id, $taxonomy ),
									];
									$count++;
								}
							}
						}
					}
				}
			}

			// Current post
			if ( $show_current_post ) {
				$breadcrumbs[] = [
					'title' => get_the_title( $post->ID ),
					'url' => '',
				];
			}
		}

		// ── Page ─────────────────────────────────
		if ( is_page() ) {
			$ancestors = get_post_ancestors( $post->ID );
			if ( ! empty( $ancestors ) ) {
				$ancestors = array_reverse( $ancestors );
				foreach ( $ancestors as $ancestor_id ) {
					$breadcrumbs[] = [
						'title' => get_the_title( $ancestor_id ),
						'url' => get_permalink( $ancestor_id ),
					];
				}
			}
			$breadcrumbs[] = [
				'title' => get_the_title( $post->ID ),
				'url' => '',
			];
		}

		// ── Category archive ─────────────────────
		if ( is_category() ) {
			if ( $show_archive && $blog_page_id ) {
				$breadcrumbs[] = [
					'title' => get_the_title( $blog_page_id ),
					'url' => get_permalink( $blog_page_id ),
				];
			}
			$cat = get_queried_object();
			$ancestors = $this->build_term_hierarchy( $cat, 'category' );
			$breadcrumbs = array_merge( $breadcrumbs, $ancestors );
			$breadcrumbs[] = [
				'title' => $cat->name,
				'url' => '',
			];
		}

		// ── Custom taxonomy archive ──────────────
		if ( is_tax() ) {
			$term = get_queried_object();
			$taxonomy = $term->taxonomy;
			$pto = get_taxonomy( $taxonomy );
			if ( $pto && ! empty( $pto->object_type ) ) {
				$cpt = $pto->object_type[0];
				$cpt_obj = get_post_type_object( $cpt );
				if ( $show_archive && $cpt_obj && ! empty( $cpt_obj->has_archive ) ) {
					$breadcrumbs[] = [
						'title' => $cpt_obj->labels->name,
						'url' => get_post_type_archive_link( $cpt ),
					];
				}
			}
			$ancestors = $this->build_term_hierarchy( $term, $taxonomy );
			$breadcrumbs = array_merge( $breadcrumbs, $ancestors );
			$breadcrumbs[] = [
				'title' => $term->name,
				'url' => '',
			];
		}

		// ── Tag archive ──────────────────────────
		if ( is_tag() ) {
			$tag = get_queried_object();
			$breadcrumbs[] = [
				'title' => $tag->name,
				'url' => '',
			];
		}

		// ── Author archive ───────────────────────
		if ( is_author() ) {
			$author = get_queried_object();
			$breadcrumbs[] = [
				'title' => __( 'Author: ', 'lp-widgets' ) . $author->display_name,
				'url' => '',
			];
		}

		// ── Search ───────────────────────────────
		if ( is_search() ) {
			$breadcrumbs[] = [
				'title' => __( 'Search results for: ', 'lp-widgets' ) . get_search_query(),
				'url' => '',
			];
		}

		// ── 404 ──────────────────────────────────
		if ( is_404() ) {
			$breadcrumbs[] = [
				'title' => __( '404 Not Found', 'lp-widgets' ),
				'url' => '',
			];
		}

		// ── WooCommerce product ──────────────────
		if ( function_exists( 'is_woocommerce' ) && is_product() ) {
			$primary = $this->get_primary_category( $post->ID, 'product_cat', $primary_source, $manual_cat_id );
			if ( $primary ) {
				$ancestors = $this->build_term_hierarchy( $primary, 'product_cat' );
				$breadcrumbs = array_merge( $breadcrumbs, $ancestors );
				$breadcrumbs[] = [
					'title' => $primary->name,
					'url' => get_term_link( $primary->term_id, 'product_cat' ),
				];
			}
			if ( $show_current_post ) {
				$breadcrumbs[] = [
					'title' => get_the_title( $post->ID ),
					'url' => '',
				];
			}
		}

		// ── Output ───────────────────────────────
		$this->output_breadcrumbs( $breadcrumbs, $separator_type, $separator_text, $separator_icon, $enable_schema, $settings );
	}
}
