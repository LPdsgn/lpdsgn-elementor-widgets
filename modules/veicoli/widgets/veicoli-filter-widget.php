<?php
/**
 * Widget: Veicoli Filter
 * File: widgets/veicoli-filter-widget.php
 */

if ( ! defined( 'ABSPATH' ) )
	exit;

class Veicoli_Filter_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'veicoli_filter';
	}

	public function get_title() {
		return esc_html__( 'Filtro Veicoli', 'veicoli' );
	}

	public function get_icon() {
		return 'eicon-search';
	}

	public function get_categories() {
		return [ 'veicoli' ];
	}

	public function get_keywords() {
		return [ 'veicoli', 'filtro', 'ricerca', 'auto' ];
	}

	public function get_script_depends(): array {
		return [ 'veicoli-widgets' ];
	}

	public function get_style_depends(): array {
		return [ 'veicoli-widgets' ];
	}

	public function has_widget_inner_wrapper(): bool {
		return false;
	}

	protected function register_controls() {

		// Sezione Contenuto
		$this->start_controls_section(
			'content_section',
			[
				'label' => esc_html__( 'Impostazioni Filtro', 'veicoli' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'loop_id',
			[
				'label' => esc_html__( 'ID del Loop Grid', 'veicoli' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'description' => 'Lascia vuoto per usare il Loop Grid nella stessa pagina, oppure inserisci l\'ID CSS custom del Loop Grid',
			]
		);

		$this->add_control(
			'show_search',
			[
				'label' => esc_html__( 'Mostra campo ricerca testo', 'veicoli' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'accordion_title',
			[
				'label' => esc_html__( 'Titolo Accordion (Mobile)', 'veicoli' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Filtra i veicoli', 'veicoli' ),
				'description' => esc_html__( 'Visibile solo su schermi < 1024px. Il filtro sarà collassato di default.', 'veicoli' ),
			]
		);

		$this->add_control(
			'accordion_icon',
			[
				'label' => esc_html__( 'Icona Accordion', 'veicoli' ),
				'type' => \Elementor\Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-filter',
					'library' => 'fa-solid',
				],
			]
		);

		$this->add_control(
			'accordion_chevron_icon',
			[
				'label' => esc_html__( 'Icona Chevron', 'veicoli' ),
				'type' => \Elementor\Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-chevron-down',
					'library' => 'fa-solid',
				],
				'description' => esc_html__( 'Icona che indica lo stato aperto/chiuso del filtro', 'veicoli' ),
			]
		);

		$this->end_controls_section();

		// Sezione Stile Accordion Mobile
		$this->start_controls_section(
			'accordion_style_section',
			[
				'label' => esc_html__( 'Stile Accordion (Mobile)', 'veicoli' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'accordion_typography',
				'label' => esc_html__( 'Tipografia', 'veicoli' ),
				'selector' => '{{WRAPPER}} .veicoli-filter-toggle',
			]
		);

		$this->add_control(
			'accordion_text_color',
			[
				'label' => esc_html__( 'Colore Testo', 'veicoli' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .veicoli-filter-toggle' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'accordion_icon_color',
			[
				'label' => esc_html__( 'Colore Icona', 'veicoli' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .veicoli-filter-toggle .toggle-icon' => 'color: {{VALUE}};',
					'{{WRAPPER}} .veicoli-filter-toggle .toggle-chevron' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name' => 'accordion_background',
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .veicoli-filter-toggle',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'accordion_border',
				'selector' => '{{WRAPPER}} .veicoli-filter-toggle',
			]
		);

		$this->add_responsive_control(
			'accordion_border_radius',
			[
				'label' => esc_html__( 'Raggio del bordo', 'veicoli' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .veicoli-filter-toggle' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'accordion_padding',
			[
				'label' => esc_html__( 'Padding', 'veicoli' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .veicoli-filter-toggle' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// Sezione Stile
		$this->start_controls_section(
			'style_section',
			[
				'label' => esc_html__( 'Stile Filtro', 'veicoli' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'filter_background_color',
			[
				'label' => esc_html__( 'Colore Sfondo', 'veicoli' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .veicoli-filter' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'label_typography',
				'label' => esc_html__( 'Tipografia Etichette', 'veicoli' ),
				'selector' => '{{WRAPPER}} .filter-field label',
			]
		);

		$this->add_responsive_control(
			'filter_padding',
			[
				'label' => esc_html__( 'Padding', 'veicoli' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem', '%', 'custom' ],
				'selectors' => [
					'{{WRAPPER}} .veicoli-filter' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// Pulsante Applica - Content
		$this->start_controls_section(
			'button_apply_section',
			[
				'label' => esc_html__( 'Pulsante "Applica Filtro"', 'veicoli' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'apply_text',
			[
				'label' => esc_html__( 'Testo Pulsante', 'veicoli' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Applica filtro', 'veicoli' ),
			]
		);

		$this->add_control(
			'apply_selected_icon',
			[
				'label' => esc_html__( 'Icona', 'veicoli' ),
				'type' => \Elementor\Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-search',
					'library' => 'fa-solid',
				],
			]
		);

		$this->add_control(
			'apply_icon_align',
			[
				'label' => esc_html__( 'Posizione Icona', 'veicoli' ),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'default' => 'row',
				'options' => [
					'row' => [
						'title' => esc_html__( 'Sinistra', 'veicoli' ),
						'icon' => 'eicon-h-align-left',
					],
					'row-reverse' => [
						'title' => esc_html__( 'Destra', 'veicoli' ),
						'icon' => 'eicon-h-align-right',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .btn-apply .elementor-button-content-wrapper' => 'flex-direction: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'apply_icon_indent',
			[
				'label' => esc_html__( 'Spaziatura Icona', 'veicoli' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'default' => [
					'size' => 8,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .btn-apply .elementor-button-content-wrapper' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// Pulsante Reset - Content
		$this->start_controls_section(
			'button_reset_section',
			[
				'label' => esc_html__( 'Pulsante "Resetta Filtro"', 'veicoli' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		// Controlli manuali per il reset button (non usa il trait per evitare conflitti)
		$this->add_control(
			'reset_text',
			[
				'label' => esc_html__( 'Testo Pulsante', 'veicoli' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Resetta filtro', 'veicoli' ),
			]
		);

		$this->add_control(
			'reset_selected_icon',
			[
				'label' => esc_html__( 'Icona', 'veicoli' ),
				'type' => \Elementor\Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-redo',
					'library' => 'fa-solid',
				],
			]
		);

		$this->add_control(
			'reset_icon_align',
			[
				'label' => esc_html__( 'Posizione Icona', 'veicoli' ),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'default' => 'row',
				'options' => [
					'row' => [
						'title' => esc_html__( 'Sinistra', 'veicoli' ),
						'icon' => 'eicon-h-align-left',
					],
					'row-reverse' => [
						'title' => esc_html__( 'Destra', 'veicoli' ),
						'icon' => 'eicon-h-align-right',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .btn-reset .elementor-button-content-wrapper' => 'flex-direction: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'reset_icon_indent',
			[
				'label' => esc_html__( 'Spaziatura Icona', 'veicoli' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'default' => [
					'size' => 8,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .btn-reset .elementor-button-content-wrapper' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// Pulsante Applica - Style
		$this->start_controls_section(
			'button_apply_style_section',
			[
				'label' => esc_html__( 'Stile Pulsante Applica', 'veicoli' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'apply_typography',
				'selector' => '{{WRAPPER}} .btn-apply',
			]
		);

		$this->start_controls_tabs( 'apply_button_tabs' );

		// Tab Normale
		$this->start_controls_tab(
			'apply_tab_normal',
			[
				'label' => esc_html__( 'Normale', 'veicoli' ),
			]
		);

		$this->add_control(
			'apply_text_color',
			[
				'label' => esc_html__( 'Colore del testo', 'veicoli' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .btn-apply' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name' => 'apply_background',
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .btn-apply',
			]
		);

		$this->end_controls_tab();

		// Tab Hover
		$this->start_controls_tab(
			'apply_tab_hover',
			[
				'label' => esc_html__( 'Hover', 'veicoli' ),
			]
		);

		$this->add_control(
			'apply_hover_color',
			[
				'label' => esc_html__( 'Colore del testo', 'veicoli' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .btn-apply:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name' => 'apply_hover_background',
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .btn-apply:hover',
			]
		);

		$this->add_control(
			'apply_hover_border_color',
			[
				'label' => esc_html__( 'Colore del bordo', 'veicoli' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .btn-apply:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'apply_hover_animation',
			[
				'label' => esc_html__( 'Animazione', 'veicoli' ),
				'type' => \Elementor\Controls_Manager::HOVER_ANIMATION,
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'apply_border',
				'selector' => '{{WRAPPER}} .btn-apply',
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'apply_border_radius',
			[
				'label' => esc_html__( 'Raggio del bordo', 'veicoli' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .btn-apply' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'apply_text_padding',
			[
				'label' => esc_html__( 'Padding', 'veicoli' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .btn-apply' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// Pulsante Reset - Style
		$this->start_controls_section(
			'button_reset_style_section',
			[
				'label' => esc_html__( 'Stile Pulsante Reset', 'veicoli' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'reset_typography',
				'selector' => '{{WRAPPER}} .btn-reset',
			]
		);

		$this->start_controls_tabs( 'reset_button_tabs' );

		// Tab Normale
		$this->start_controls_tab(
			'reset_tab_normal',
			[
				'label' => esc_html__( 'Normale', 'veicoli' ),
			]
		);

		$this->add_control(
			'reset_text_color',
			[
				'label' => esc_html__( 'Colore del testo', 'veicoli' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .btn-reset' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name' => 'reset_background',
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .btn-reset',
			]
		);

		$this->end_controls_tab();

		// Tab Hover
		$this->start_controls_tab(
			'reset_tab_hover',
			[
				'label' => esc_html__( 'Hover', 'veicoli' ),
			]
		);

		$this->add_control(
			'reset_hover_color',
			[
				'label' => esc_html__( 'Colore del testo', 'veicoli' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .btn-reset:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name' => 'reset_hover_background',
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .btn-reset:hover',
			]
		);

		$this->add_control(
			'reset_hover_border_color',
			[
				'label' => esc_html__( 'Colore del bordo', 'veicoli' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .btn-reset:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'reset_hover_animation',
			[
				'label' => esc_html__( 'Animazione', 'veicoli' ),
				'type' => \Elementor\Controls_Manager::HOVER_ANIMATION,
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'reset_border',
				'selector' => '{{WRAPPER}} .btn-reset',
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'reset_border_radius',
			[
				'label' => esc_html__( 'Raggio del bordo', 'veicoli' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .btn-reset' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'reset_text_padding',
			[
				'label' => esc_html__( 'Padding', 'veicoli' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .btn-reset' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$loop_id = ! empty( $settings['loop_id'] ) ? $settings['loop_id'] : '';

		// Ottieni le tassonomie
		$produttori = get_terms( [ 'taxonomy' => 'produttore', 'hide_empty' => true ] );
		$modelli = get_terms( [ 'taxonomy' => 'modello', 'hide_empty' => true ] );
		$alimentazioni = get_terms( [ 'taxonomy' => 'alimentazione', 'hide_empty' => true ] );
		$segmenti = get_terms( [ 'taxonomy' => 'segmento', 'hide_empty' => true ] );
		$cambi = get_terms( [ 'taxonomy' => 'cambio', 'hide_empty' => true ] );

		// Messaggio di aiuto nell'editor
		if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			echo '<div style="background: #f0f0f0; padding: 15px; margin-bottom: 20px; border-left: 4px solid #7ab842;">';
			echo '<strong>👀 Anteprima Widget Filtro Veicoli</strong><br>';
			echo 'Il filtro funziona solo sul frontend. ';
			if ( empty( $loop_id ) ) {
				echo 'Cercherà automaticamente il Loop Grid nella stessa pagina.';
			} else {
				echo 'ID Loop Grid target: <code>' . esc_html( $loop_id ) . '</code>';
			}
			echo '<br><small>Apri la console del browser per vedere i log di debug del filtro.</small>';
			echo '</div>';
		}
		?>

		<div class="veicoli-filter-wrapper">
			<!-- Accordion Toggle (visibile solo mobile) -->
			<button type="button" class="veicoli-filter-toggle" aria-expanded="false">
				<?php if ( ! empty( $settings['accordion_icon']['value'] ) ) : ?>
					<span class="toggle-icon">
						<?php \Elementor\Icons_Manager::render_icon( $settings['accordion_icon'], [ 'aria-hidden' => 'true' ] ); ?>
					</span>
				<?php endif; ?>
				<span class="toggle-title"><?php echo esc_html( $settings['accordion_title'] ); ?></span>
				<?php if ( ! empty( $settings['accordion_chevron_icon']['value'] ) ) : ?>
					<span class="toggle-chevron">
						<?php \Elementor\Icons_Manager::render_icon( $settings['accordion_chevron_icon'], [ 'aria-hidden' => 'true' ] ); ?>
					</span>
				<?php endif; ?>
			</button>

			<!-- Form wrapper collassabile -->
			<div class="veicoli-filter-content">
				<form class="veicoli-filter"
					data-loop-id="<?php echo esc_attr( $loop_id ); ?>">

				<?php if ( $settings['show_search'] === 'yes' ) : ?>
					<div class="filter-row">
						<div class="filter-field filter-search">
							<label><?php esc_html_e( 'Cerca il tuo veicolo', 'veicoli' ); ?></label>
							<input type="text" name="search" placeholder="<?php esc_attr_e( 'Es: Mazda 2', 'veicoli' ); ?>">
						</div>
					</div>
				<?php endif; ?>

				<div class="filter-row">
					<div class="filter-field">
						<label><?php esc_html_e( 'Produttore', 'veicoli' ); ?></label>
						<select name="produttore">
							<option value=""><?php esc_html_e( '- Produttore -', 'veicoli' ); ?></option>
							<?php foreach ( $produttori as $term ) : ?>
								<option value="<?php echo esc_attr( $term->slug ); ?>"><?php echo esc_html( $term->name ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>

					<div class="filter-field">
						<label><?php esc_html_e( 'Modello', 'veicoli' ); ?></label>
						<select name="modello">
							<option value=""><?php esc_html_e( '- Modello -', 'veicoli' ); ?></option>
							<?php foreach ( $modelli as $term ) : ?>
								<option value="<?php echo esc_attr( $term->slug ); ?>"><?php echo esc_html( $term->name ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>

					<div class="filter-field">
						<label><?php esc_html_e( 'Segmento', 'veicoli' ); ?></label>
						<select name="segmento">
							<option value=""><?php esc_html_e( '- Segmento -', 'veicoli' ); ?></option>
							<?php foreach ( $segmenti as $term ) : ?>
								<option value="<?php echo esc_attr( $term->slug ); ?>"><?php echo esc_html( $term->name ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>

					<div class="filter-field">
						<label><?php esc_html_e( 'Alimentazione', 'veicoli' ); ?></label>
						<select name="alimentazione">
							<option value=""><?php esc_html_e( '- Alimentazione -', 'veicoli' ); ?></option>
							<?php foreach ( $alimentazioni as $term ) : ?>
								<option value="<?php echo esc_attr( $term->slug ); ?>"><?php echo esc_html( $term->name ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>

					<div class="filter-field">
						<label><?php esc_html_e( 'Trasmissione', 'veicoli' ); ?></label>
						<select name="cambio">
							<option value=""><?php esc_html_e( '- Trasmissione -', 'veicoli' ); ?></option>
							<?php foreach ( $cambi as $term ) : ?>
								<option value="<?php echo esc_attr( $term->slug ); ?>"><?php echo esc_html( $term->name ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>

					<div class="filter-field">
						<label><?php esc_html_e( 'Costo rata da:', 'veicoli' ); ?></label>
						<input type="number" name="prezzo_min" placeholder="130">
					</div>

					<div class="filter-field">
						<label><?php esc_html_e( 'A:', 'veicoli' ); ?></label>
						<input type="number" name="prezzo_max" placeholder="1848">
					</div>

					<div class="filter-field">
						<label><?php esc_html_e( 'Anticipo', 'veicoli' ); ?></label>
						<select name="anticipo">
							<option value=""><?php esc_html_e( 'Tutti', 'veicoli' ); ?></option>
							<option value="si"><?php esc_html_e( 'Con anticipo', 'veicoli' ); ?></option>
							<option value="no"><?php esc_html_e( 'Senza anticipo', 'veicoli' ); ?></option>
						</select>
					</div>
				</div>

				<div class="filter-actions">
					<?php 
					// Pulsante Reset
					$this->add_render_attribute( 'reset-wrapper', 'class', 'elementor-button-wrapper' );
					$this->add_render_attribute( 'reset-button', 'class', 'btn-reset elementor-button' );
					$this->add_render_attribute( 'reset-button', 'type', 'button' );
					
					if ( ! empty( $settings['reset_hover_animation'] ) ) {
						$this->add_render_attribute( 'reset-button', 'class', 'elementor-animation-' . $settings['reset_hover_animation'] );
					}
					?>
					<div <?php $this->print_render_attribute_string( 'reset-wrapper' ); ?>>
						<button <?php $this->print_render_attribute_string( 'reset-button' ); ?>>
							<?php $this->render_reset_button_content(); ?>
						</button>
					</div>

					<?php 
					// Pulsante Apply
					$this->add_render_attribute( 'apply-wrapper', 'class', 'elementor-button-wrapper' );
					$this->add_render_attribute( 'apply-button', 'class', 'btn-apply elementor-button' );
					$this->add_render_attribute( 'apply-button', 'type', 'submit' );
					
					if ( ! empty( $settings['apply_hover_animation'] ) ) {
						$this->add_render_attribute( 'apply-button', 'class', 'elementor-animation-' . $settings['apply_hover_animation'] );
					}
					?>
					<div <?php $this->print_render_attribute_string( 'apply-wrapper' ); ?>>
						<button <?php $this->print_render_attribute_string( 'apply-button' ); ?>>
							<?php $this->render_apply_button_content(); ?>
						</button>
					</div>
					</div>
				</form>

				<div class="filter-loading" style="display:none;">
					<div class="spinner"></div>
					<p><?php esc_html_e( 'Caricamento...', 'veicoli' ); ?></p>
				</div>
			</div>
			<!-- Fine contenuto collassabile -->
			</div>		<?php
	}

	/**
	 * Render reset button content
	 */
	protected function render_reset_button_content() {
		$settings = $this->get_settings_for_display();

		$this->add_render_attribute( [
			'reset-content-wrapper' => [
				'class' => 'elementor-button-content-wrapper',
			],
			'reset-icon' => [
				'class' => 'elementor-button-icon',
			],
			'reset-text' => [
				'class' => 'elementor-button-text',
			],
		] );
		?>
		<span <?php $this->print_render_attribute_string( 'reset-content-wrapper' ); ?>>
			<?php if ( ! empty( $settings['reset_selected_icon']['value'] ) ) : ?>
			<span <?php $this->print_render_attribute_string( 'reset-icon' ); ?>>
				<?php \Elementor\Icons_Manager::render_icon( $settings['reset_selected_icon'], [ 'aria-hidden' => 'true' ] ); ?>
			</span>
			<?php endif; ?>
			<?php if ( ! empty( $settings['reset_text'] ) ) : ?>
			<span <?php $this->print_render_attribute_string( 'reset-text' ); ?>><?php echo esc_html( $settings['reset_text'] ); ?></span>
			<?php endif; ?>
		</span>
		<?php
	}

	/**
	 * Render apply button content
	 */
	protected function render_apply_button_content() {
		$settings = $this->get_settings_for_display();

		$this->add_render_attribute( [
			'apply-content-wrapper' => [
				'class' => 'elementor-button-content-wrapper',
			],
			'apply-icon' => [
				'class' => 'elementor-button-icon',
			],
			'apply-text' => [
				'class' => 'elementor-button-text',
			],
		] );
		?>
		<span <?php $this->print_render_attribute_string( 'apply-content-wrapper' ); ?>>
			<?php if ( ! empty( $settings['apply_selected_icon']['value'] ) ) : ?>
			<span <?php $this->print_render_attribute_string( 'apply-icon' ); ?>>
				<?php \Elementor\Icons_Manager::render_icon( $settings['apply_selected_icon'], [ 'aria-hidden' => 'true' ] ); ?>
			</span>
			<?php endif; ?>
			<?php if ( ! empty( $settings['apply_text'] ) ) : ?>
			<span <?php $this->print_render_attribute_string( 'apply-text' ); ?>><?php echo esc_html( $settings['apply_text'] ); ?></span>
			<?php endif; ?>
		</span>
		<?php
	}

	protected function content_template() {
		// Template JS per Elementor Editor (opzionale)
	}
}