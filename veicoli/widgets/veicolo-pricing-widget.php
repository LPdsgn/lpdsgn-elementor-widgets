<?php
/**
 * Widget: Veicolo Pricing
 * File: widgets/veicolo-pricing-widget.php
 */

if ( ! defined( 'ABSPATH' ) )
	exit;

class Veicolo_Pricing_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'veicolo_pricing';
	}

	public function get_title() {
		return esc_html__( 'Prezzo Veicolo Dinamico', 'veicoli' );
	}

	public function get_icon() {
		return 'eicon-price-table';
	}

	public function get_categories() {
		return [ 'veicoli' ];
	}

	public function get_keywords() {
		return [ 'veicoli', 'prezzo', 'noleggio', 'rata' ];
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

	protected function register_controls() {

		// Sezione Stile Prezzo
		$this->start_controls_section(
			'price_style_section',
			[
				'label' => esc_html__( 'Stile Prezzo', 'veicoli' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'price_color',
			[
				'label' => esc_html__( 'Colore Prezzo', 'veicoli' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .prezzo-valore' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'price_typography',
				'label' => esc_html__( 'Tipografia Prezzo', 'veicoli' ),
				'selector' => '{{WRAPPER}} .prezzo-valore',
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		// Verifica ACF
		if ( ! function_exists( 'get_field' ) ) {
			echo '<p>' . esc_html__( 'ACF non è attivo.', 'veicoli' ) . '</p>';
			return;
		}
		if ( ! is_singular( 'veicolo' ) ) {
			echo '<p>' . esc_html__( 'Questo widget funziona solo nelle pagine singole dei veicoli.', 'veicoli' ) . '</p>';
			return;
		}

		$settings = $this->get_settings_for_display();
		$post_id = get_the_ID();

		// Determina categoria noleggio
		$is_breve_termine = $this->is_breve_termine( $post_id );
		$is_lungo_termine = $this->is_lungo_termine( $post_id );

		// === BREVE TERMINE ===
		if ( $is_breve_termine ) {
			$this->render_breve_termine( $post_id );
		}
		// === LUNGO TERMINE / COMMERCIALI ===
		else {
			$this->render_lungo_termine( $post_id );
		}
	}

	/**
	 * Render pricing per Lungo Termine / Commerciali
	 */
	private function render_lungo_termine( $post_id ) {
		$veicolo = get_the_title();
		$anticipo_disponibile = get_field( 'anticipo', $post_id );
		$importo_anticipo = get_field( 'importo_anticipo', $post_id );
		$piani = get_field( 'piani_noleggio', $post_id );

		if ( ! $piani ) {
			echo '<p>' . esc_html__( 'Nessun piano di noleggio disponibile.', 'veicoli' ) . '</p>';
			return;
		}

		// Piano di default (primo)
		$piano_default = $piani[0];
		$anticipo_attivo = $anticipo_disponibile ? true : false;
		$prezzo_default = $anticipo_attivo && isset( $piano_default['prezzo_con_anticipo'] ) 
			? $piano_default['prezzo_con_anticipo'] 
			: $piano_default['prezzo_senza_anticipo'];
		?>

		<div class="veicolo-pricing-wrapper categoria-lungo-termine" data-categoria="lungo-termine" data-widget-id="<?php echo esc_attr( $this->get_id() ); ?>">

			<!-- Prezzo principale -->
			<div class="prezzo-principale">
				<span class="prezzo-valore" data-price="<?php echo esc_attr( $prezzo_default ); ?>">
					<?php echo number_format( $prezzo_default, 0, ',', '.' ); ?>€
				</span>
				<span class="prezzo-label">
					<?php esc_html_e( 'al mese', 'veicoli' ); ?>
					<small><?php esc_html_e( '(iva esclusa)', 'veicoli' ); ?></small>
				</span>
			</div>

			<!-- Dettagli piano inline -->
			<div class="piano-dettagli">
				<span class="dettaglio-durata" data-durata="<?php echo esc_attr( $piano_default['durata'] ); ?>">
					<?php echo esc_html( $piano_default['durata'] ); ?> <?php esc_html_e( 'mesi', 'veicoli' ); ?>
				</span>
				<span class="dettaglio-separatore">•</span>
				<span class="dettaglio-km" data-km="<?php echo esc_attr( $piano_default['kilometri'] ); ?>">
					<?php echo number_format( $piano_default['kilometri'], 0, ',', '.' ); ?> km
				</span>
				<span class="dettaglio-separatore">•</span>
				<span class="dettaglio-anticipo">
					<?php esc_html_e( 'Anticipo', 'veicoli' ); ?> 
					<span class="anticipo-valore" data-importo="<?php echo esc_attr( $importo_anticipo ? $importo_anticipo : 0 ); ?>">
						<?php 
						if ( $anticipo_disponibile && $anticipo_attivo ) {
							echo number_format( $importo_anticipo, 0, ',', '.' ) . '€';
						} else {
							echo esc_html__( 'ZERO', 'veicoli' );
						}
						?>
					</span>
				</span>
			</div>

			<!-- Toggle anticipo -->
			<?php if ( $anticipo_disponibile ) : ?>
				<div class="anticipo-toggle">
					<label class="toggle-switch">
						<input type="checkbox" class="toggle-anticipo" <?php checked( $anticipo_attivo ); ?>>
						<span class="toggle-slider"></span>
					</label>
					<span class="toggle-label">
						<?php esc_html_e( 'Anticipo', 'veicoli' ); ?>
						<strong class="anticipo-importo"><?php echo number_format( $importo_anticipo, 0, ',', '.' ); ?>€</strong>
					</span>
				</div>
			<?php endif; ?>

			<!-- Selettore piano -->
			<div class="piano-selector">
				<label for="seleziona-piano-<?php echo esc_attr( $this->get_id() ); ?>">
					<?php esc_html_e( 'Seleziona una quotazione', 'veicoli' ); ?>
				</label>
				<select class="piano-dropdown" id="seleziona-piano-<?php echo esc_attr( $this->get_id() ); ?>">
					<?php foreach ( $piani as $index => $piano ) : ?>
						<option value="<?php echo esc_attr( $index ); ?>" 
							data-durata="<?php echo esc_attr( $piano['durata'] ); ?>"
							data-km="<?php echo esc_attr( $piano['kilometri'] ); ?>"
							data-prezzo-con="<?php echo esc_attr( isset( $piano['prezzo_con_anticipo'] ) ? $piano['prezzo_con_anticipo'] : 0 ); ?>"
							data-prezzo-senza="<?php echo esc_attr( $piano['prezzo_senza_anticipo'] ); ?>">
							<?php echo esc_html( $piano['durata'] ); ?> <?php esc_html_e( 'mesi', 'veicoli' ); ?>, 
							<?php echo number_format( $piano['kilometri'], 0, ',', '.' ); ?> km
						</option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>

		<?php
		// Normalizza anticipo per JSON
		$anticipo_bool = false;
		if ( is_array( $anticipo_disponibile ) ) {
			$anticipo_bool = isset( $anticipo_disponibile['value'] ) && $anticipo_disponibile['value'] === 'true';
		} else {
			$anticipo_bool = (bool) $anticipo_disponibile;
		}
		
		$json_data = array(
			'tipo_noleggio' => 'lungo_termine',
			'piani' => $piani,
			'anticipo_disponibile' => $anticipo_bool,
			'importo_anticipo' => $importo_anticipo ? floatval( $importo_anticipo ) : 0,
			'veicolo_title' => get_the_title()
		);
		?>
		<script type="application/json" class="veicoli-data"><?php echo wp_json_encode( $json_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ); ?></script>
		<?php
	}

	/**
	 * Render pricing per Breve Termine
	 */
	private function render_breve_termine( $post_id ) {
		$veicolo = get_the_title();
		$prezzi = get_field( 'prezzi_noleggio_breve_termine', $post_id );

		if ( ! $prezzi || ! is_array( $prezzi ) || empty( $prezzi ) ) {
			echo '<p>' . esc_html__( 'Nessun prezzo disponibile per il noleggio breve termine.', 'veicoli' ) . '</p>';
			return;
		}

		// Prezzo default (prima fascia)
		$fascia_default = $prezzi[0];
		$prezzo_default = isset( $fascia_default['prezzo_giorno_breve_termine'] ) ? floatval( $fascia_default['prezzo_giorno_breve_termine'] ) : 0;
		$durata_default = isset( $fascia_default['giorni_breve_termine'] ) ? $fascia_default['giorni_breve_termine'] : '';
		?>

		<div class="veicolo-pricing-wrapper categoria-breve-termine" data-categoria="breve-termine" data-widget-id="<?php echo esc_attr( $this->get_id() ); ?>">

			<!-- Prezzo principale -->
			<div class="prezzo-principale">
				<span class="prezzo-valore" data-price="<?php echo esc_attr( $prezzo_default ); ?>">
					<?php echo number_format( $prezzo_default, 0, ',', '.' ); ?>€
				</span>
				<span class="prezzo-label">
					<?php esc_html_e( 'al giorno', 'veicoli' ); ?>
					<small><?php esc_html_e( '(iva esclusa)', 'veicoli' ); ?></small>
				</span>
			</div>

			<!-- Selettore durata (pulsanti) -->
			<div class="piano-selector durata-selector-breve">
				<label>
					<?php esc_html_e( 'Seleziona durata', 'veicoli' ); ?>
				</label>
				<div class="durata-buttons" role="group" aria-label="<?php esc_attr_e( 'Seleziona durata noleggio', 'veicoli' ); ?>">
					<?php foreach ( $prezzi as $index => $fascia ) : 
						$giorni = isset( $fascia['giorni_breve_termine'] ) ? $fascia['giorni_breve_termine'] : '';
						$prezzo = isset( $fascia['prezzo_giorno_breve_termine'] ) ? floatval( $fascia['prezzo_giorno_breve_termine'] ) : 0;
						$is_first = ( $index === 0 );
					?>
						<button 
							type="button" 
							class="durata-button<?php echo $is_first ? ' active' : ''; ?>" 
							data-index="<?php echo esc_attr( $index ); ?>"
							data-giorni="<?php echo esc_attr( $giorni ); ?>"
							data-prezzo="<?php echo esc_attr( $prezzo ); ?>"
							aria-pressed="<?php echo $is_first ? 'true' : 'false'; ?>">
							<?php echo esc_html( $giorni ); ?> <?php echo ( intval( $giorni ) === 1 ) ? esc_html__( 'giorno', 'veicoli' ) : esc_html__( 'giorni', 'veicoli' ); ?>
						</button>
					<?php endforeach; ?>
				</div>
			</div>
		</div>

		<?php
		$json_data = array(
			'tipo_noleggio' => 'breve_termine',
			'prezzi' => $prezzi,
			'veicolo_title' => get_the_title()
		);
		?>
		<script type="application/json" class="veicoli-data"><?php echo wp_json_encode( $json_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ); ?></script>
		<?php
	}

	protected function content_template() {
		// Non implementato
	}
}