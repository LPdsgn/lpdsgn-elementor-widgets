<?php
/**
 * Veicolo Loop Info Widget
 * Widget per visualizzare informazioni essenziali del veicolo nel loop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Veicolo_Loop_Info_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'veicolo_loop_info';
	}

	public function get_title() {
		return esc_html__( 'Veicolo Loop Info', 'veicoli' );
	}

	public function get_icon() {
		return 'eicon-product-info';
	}

	public function get_categories() {
		return [ 'veicoli' ];
	}

	public function get_keywords() {
		return [ 'veicolo', 'loop', 'info', 'prezzo', 'dettagli' ];
	}
	
	public function has_widget_inner_wrapper(): bool {
		return false;
	}

	/**
	 * Get categoria noleggio term ID
	 * @param int $post_id Post ID
	 * @return int|null Term ID or null
	 */
	private function get_categoria_noleggio( $post_id = null ) {
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}
		
		$terms = get_the_terms( $post_id, 'categoria_noleggio' );
		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			return $terms[0]->term_id;
		}
		return null;
	}

	/**
	 * Check if veicolo is Lungo Termine or Commerciale
	 * @param int $post_id Post ID
	 * @return bool
	 */
	private function is_lungo_termine( $post_id = null ) {
		$categoria = $this->get_categoria_noleggio( $post_id );
		// IDs: 40 = Lungo Termine, 41 = Veicoli Commerciali
		return in_array( $categoria, [ 40, 41 ], true );
	}

	/**
	 * Check if veicolo is Breve Termine
	 * @param int $post_id Post ID
	 * @return bool
	 */
	private function is_breve_termine( $post_id = null ) {
		$categoria = $this->get_categoria_noleggio( $post_id );
		// ID: 42 = Breve Termine
		return $categoria === 42;
	}

	/**
	 * Get prezzo minimo for Lungo Termine / Commerciali
	 * Trova il prezzo più basso tra prezzo_senza_anticipo e prezzo_con_anticipo
	 * @param int $post_id Post ID
	 * @return array|null Array with 'prezzo', 'durata', 'kilometri', 'anticipo', 'tipo_prezzo' or null
	 */
	private function get_prezzo_minimo_lungo_termine( $post_id = null ) {
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}

		$piani = get_field( 'piani_noleggio', $post_id );

		if ( ! $piani || ! is_array( $piani ) || empty( $piani ) ) {
			return null;
		}

		$prezzo_min = PHP_INT_MAX;
		$piano_minimo = null;
		$tipo_prezzo = 'senza_anticipo'; // 'senza_anticipo' o 'con_anticipo'

		foreach ( $piani as $piano ) {
			// Controlla prezzo senza anticipo
			$prezzo_senza = isset( $piano['prezzo_senza_anticipo'] ) ? floatval( $piano['prezzo_senza_anticipo'] ) : 0;
			if ( $prezzo_senza > 0 && $prezzo_senza < $prezzo_min ) {
				$prezzo_min = $prezzo_senza;
				$piano_minimo = $piano;
				$tipo_prezzo = 'senza_anticipo';
			}

			// Controlla prezzo con anticipo
			$prezzo_con = isset( $piano['prezzo_con_anticipo'] ) ? floatval( $piano['prezzo_con_anticipo'] ) : 0;
			if ( $prezzo_con > 0 && $prezzo_con < $prezzo_min ) {
				$prezzo_min = $prezzo_con;
				$piano_minimo = $piano;
				$tipo_prezzo = 'con_anticipo';
			}
		}

		if ( $prezzo_min === PHP_INT_MAX || ! $piano_minimo ) {
			return null;
		}

		// Ottieni anticipo
		$anticipo = get_field( 'anticipo', $post_id );
		$importo_anticipo = '';
		
		if ( $anticipo && $anticipo !== 'false' ) {
			$importo_anticipo_raw = get_field( 'importo_anticipo', $post_id );
			if ( $importo_anticipo_raw ) {
				$importo_anticipo = number_format( floatval( $importo_anticipo_raw ), 2, ',', '.' );
			}
		}

		return [
			'prezzo' => $prezzo_min,
			'durata' => isset( $piano_minimo['durata'] ) ? $piano_minimo['durata'] : '',
			'kilometri' => isset( $piano_minimo['kilometri'] ) ? $piano_minimo['kilometri'] : '',
			'anticipo' => $importo_anticipo,
			'tipo_prezzo' => $tipo_prezzo,
		];
	}

	/**
	 * Get prezzo minimo for Breve Termine
	 * @param int $post_id Post ID
	 * @return array|null Array with 'prezzo', 'giorni_min', 'giorni_max' or null
	 */
	private function get_prezzo_minimo_breve_termine( $post_id = null ) {
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}

		$prezzi = get_field( 'prezzi_noleggio_breve_termine', $post_id );

		if ( ! $prezzi || ! is_array( $prezzi ) || empty( $prezzi ) ) {
			return null;
		}

		$prezzo_min = PHP_INT_MAX;
		$giorni_min = PHP_INT_MAX;
		$giorni_max = 0;

		foreach ( $prezzi as $fascia ) {
			$prezzo = isset( $fascia['prezzo_giorno_breve_termine'] ) ? floatval( $fascia['prezzo_giorno_breve_termine'] ) : 0;
			$giorni = isset( $fascia['giorni_breve_termine'] ) ? intval( $fascia['giorni_breve_termine'] ) : 0;
			
			if ( $prezzo > 0 && $prezzo < $prezzo_min ) {
				$prezzo_min = $prezzo;
			}
			
			if ( $giorni > 0 ) {
				if ( $giorni < $giorni_min ) {
					$giorni_min = $giorni;
				}
				if ( $giorni > $giorni_max ) {
					$giorni_max = $giorni;
				}
			}
		}

		if ( $prezzo_min === PHP_INT_MAX ) {
			return null;
		}

		return [
			'prezzo' => $prezzo_min,
			'giorni_min' => ( $giorni_min === PHP_INT_MAX ) ? null : $giorni_min,
			'giorni_max' => ( $giorni_max === 0 ) ? null : $giorni_max,
		];
	}

	protected function register_controls() {

		// Sezione Contenuto
		$this->start_controls_section(
			'content_section',
			[
				'label' => esc_html__( 'Contenuto', 'veicoli' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'info_notice',
			[
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => esc_html__( 'Questo widget mostra automaticamente le informazioni del veicolo corrente nel loop.', 'veicoli' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
			]
		);

		$this->end_controls_section();

		// === STILE: Nome Veicolo ===
		$this->start_controls_section(
			'style_nome',
			[
				'label' => esc_html__( 'Nome Veicolo', 'veicoli' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'nome_typography',
				'selector' => '{{WRAPPER}} .veicolo-nome',
			]
		);

		$this->add_control(
			'nome_color',
			[
				'label' => esc_html__( 'Colore', 'veicoli' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .veicolo-nome' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'nome_margin',
			[
				'label' => esc_html__( 'Margine', 'veicoli' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .veicolo-nome' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// === STILE: Prezzo ===
		$this->start_controls_section(
			'style_prezzo',
			[
				'label' => esc_html__( 'Prezzo', 'veicoli' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		// INIZIO TABS
		$this->start_controls_tabs(
			'prezzo_tabs'
		);
		// TAB NORMALE
		$this->start_controls_tab(
			'prezzo_group_tab',
			[
				'label' => esc_html__( 'Riga Prezzo', 'veicoli' ),
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'prezzo_group_typography',
				'selector' => '{{WRAPPER}} .veicolo-prezzo',
			]
		);

		$this->add_control(
			'prezzo_group_color',
			[
				'label' => esc_html__( 'Colore', 'veicoli' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .veicolo-prezzo' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		// TAB CIFRA PREZZO
		$this->start_controls_tab(
			'prezzo_cifra_tab',
			[
				'label' => esc_html__( 'Cifra Prezzo', 'veicoli' ),
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'prezzo_cifra_typography',
				'selector' => '{{WRAPPER}} .priceEvidence',
			]
		);

		$this->add_control(
			'prezzo_cifra_color',
			[
				'label' => esc_html__( 'Colore Prezzo', 'veicoli' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .priceEvidence' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		// FINE TABS
		$this->end_controls_tabs();

		$this->add_responsive_control(
			'prezzo_margin',
			[
				'label' => esc_html__( 'Margine', 'veicoli' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
				'selectors' => [
					'{{WRAPPER}} .veicolo-prezzo' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// === STILE: Separatore ===
		$this->start_controls_section(
			'style_separatore',
			[
				'label' => esc_html__( 'Separatore', 'veicoli' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'mostra_separatore',
			[
				'label' => esc_html__( 'Mostra Separatore', 'veicoli' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Sì', 'veicoli' ),
				'label_off' => esc_html__( 'No', 'veicoli' ),
				'return_value' => 'yes',
				'default' => 'no',
			]
		);

		$this->add_control(
			'separatore_color',
			[
				'label' => esc_html__( 'Colore', 'veicoli' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .veicolo-separatore' => 'background-color: {{VALUE}};',
				],
				'condition' => [
					'mostra_separatore' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'separatore_width',
			[
				'label' => esc_html__( 'Spessore', 'veicoli' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 10,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 1,
				],
				'selectors' => [
					'{{WRAPPER}} .veicolo-separatore' => 'height: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'mostra_separatore' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'separatore_margin',
			[
				'label' => esc_html__( 'Margine', 'veicoli' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', '%' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 16,
					],
					'em' => [
						'min' => 0,
						'max' => 1,
						'step' => 0.025,
					],
					'%' => [
						'min' => 0,
						'max' => 3.5,
						'step' => 0.025,
					],
				],
				'default' => [
					'unit' => 'em',
					'size' => 0.75,
				],
				'selectors' => [
					'{{WRAPPER}} .veicolo-separatore' => 'margin-block: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'mostra_separatore' => 'yes',
				],
			]
		);

		$this->end_controls_section();

		// === STILE: Dettagli ===
		$this->start_controls_section(
			'style_dettagli',
			[
				'label' => esc_html__( 'Dettagli', 'veicoli' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'dettagli_typography',
				'selector' => '{{WRAPPER}} .veicolo-dettagli',
			]
		);

		$this->add_control(
			'dettagli_color',
			[
				'label' => esc_html__( 'Colore', 'veicoli' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .veicolo-dettagli' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'dettagli_bullet_color',
			[
				'label' => esc_html__( 'Colore separatore', 'veicoli' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .veicolo-dettagli .bullet' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'dettagli_bullet_margin_x',
			[
				'label' => esc_html__( 'Margine X separatore', 'veicoli' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 10,
					],
					'em' => [
						'min' => 0,
						'max' => 1,
						'step' => 0.025,
					],
				],
				'default' => [
					'unit' => 'em',
					'size' => 0.35,
				],
				'selectors' => [
					'{{WRAPPER}} .veicolo-dettagli .bullet' => 'margin-inline: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'dettagli_margin',
			[
				'label' => esc_html__( 'Margine', 'veicoli' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .veicolo-dettagli' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$post_id = get_the_ID();
		$settings = $this->get_settings_for_display();

		// Verifica ACF
		if ( ! function_exists( 'get_field' ) ) {
			echo '<p>' . esc_html__( 'ACF non disponibile', 'veicoli' ) . '</p>';
			return;
		}

		// Ottieni produttore e modello
		$produttore_terms = get_the_terms( $post_id, 'produttore' );
		$modello_terms = get_the_terms( $post_id, 'modello' );

		$produttore = ( $produttore_terms && ! is_wp_error( $produttore_terms ) ) ? $produttore_terms[0]->name : '';
		$modello = ( $modello_terms && ! is_wp_error( $modello_terms ) ) ? $modello_terms[0]->name : '';

		$nome_veicolo = trim( $produttore . ' ' . $modello );

		// Determina categoria noleggio e rendering
		$is_breve_termine = $this->is_breve_termine( $post_id );
		$is_lungo_termine = $this->is_lungo_termine( $post_id );

		// Classe CSS condizionale
		$wrapper_class = 'veicolo-loop-info';
		if ( $is_breve_termine ) {
			$wrapper_class .= ' categoria-breve-termine';
		} elseif ( $is_lungo_termine ) {
			$wrapper_class .= ' categoria-lungo-termine';
		}

		echo '<div class="' . esc_attr( $wrapper_class ) . '">';
		
		// Nome veicolo
		if ( $nome_veicolo ) {
			echo '<div class="veicolo-nome">' . esc_html( $nome_veicolo ) . '</div>';
		}

		// === BREVE TERMINE ===
		if ( $is_breve_termine ) {
			$breve_data = $this->get_prezzo_minimo_breve_termine( $post_id );

			if ( $breve_data && $breve_data['prezzo'] ) {
				echo '<div class="veicolo-prezzo">A partire da <span class="priceEvidence">' . number_format( $breve_data['prezzo'], 0, ',', '.' ) . '&euro;</span><small>/giorno</small></div>';
				
				// Separatore (se abilitato)
				if ( 'yes' === $settings['mostra_separatore'] ) {
					echo '<div class="veicolo-separatore"></div>';
				}
				
				// Dettagli Breve Termine
				$dettagli_parts = [ 'Noleggio a breve termine' ];
				
				if ( $breve_data['giorni_min'] && $breve_data['giorni_max'] ) {
					if ( $breve_data['giorni_min'] === $breve_data['giorni_max'] ) {
						$dettagli_parts[] = $breve_data['giorni_min'] . ' giorni';
					} else {
						$dettagli_parts[] = 'Da ' . $breve_data['giorni_min'] . ' a ' . $breve_data['giorni_max'] . ' giorni';
					}
				}
				
				if ( ! empty( $dettagli_parts ) ) {
					echo '<div class="veicolo-dettagli">' . implode( '<span class="bullet">&bull;</span>', $dettagli_parts ) . '</div>';
				}
			} else {
				echo '<div class="veicolo-prezzo">' . esc_html__( 'Prezzo non disponibile', 'veicoli' ) . '</div>';
			}
		}
		// === LUNGO TERMINE / COMMERCIALI ===
		else {
			$piano_data = $this->get_prezzo_minimo_lungo_termine( $post_id );

			if ( ! $piano_data ) {
				echo '<div class="veicolo-prezzo">' . esc_html__( 'Prezzo non disponibile', 'veicoli' ) . '</div>';
				echo '</div>';
				return;
			}

			// Prezzo (sempre il più basso in assoluto)
			echo '<div class="veicolo-prezzo">A partire da <span class="priceEvidence">' . number_format( $piano_data['prezzo'], 0, ',', '.' ) . '&euro;</span><small>/mese</small></div>';

			// Separatore (se abilitato)
			if ( 'yes' === $settings['mostra_separatore'] ) {
				echo '<div class="veicolo-separatore"></div>';
			}

			// Dettagli
			$dettagli_parts = [];

			if ( $piano_data['durata'] ) {
				$dettagli_parts[] = $piano_data['durata'] . ' mesi';
			}

			if ( $piano_data['kilometri'] ) {
				$dettagli_parts[] = number_format( floatval( $piano_data['kilometri'] ), 0, ',', '.' ) . ' km';
			}

			// Se il prezzo minimo è "con anticipo", mostralo nei dettagli
			if ( $piano_data['tipo_prezzo'] === 'con_anticipo' && $piano_data['anticipo'] ) {
				$dettagli_parts[] = 'Anticipo &euro; ' . $piano_data['anticipo'];
			}

			if ( ! empty( $dettagli_parts ) ) {
				echo '<div class="veicolo-dettagli">' . implode( '<span class="bullet">&bull;</span>', $dettagli_parts ) . '</div>';
			}
		}

		echo '</div>';
	}
}
