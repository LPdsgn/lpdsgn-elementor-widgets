<?php
defined('ABSPATH') || exit;

/**
 * Filtra i termini della tassonomia "modello" in base al produttore selezionato.
 */
add_filter('acf/fields/taxonomy/query/key=field_68de99ba147c0', function ($args, $field, $post_id) {
	// 1) prova a leggere il produttore dalla richiesta AJAX di ACF
	$brand = isset($_POST['brand']) ? intval($_POST['brand']) : 0;

	// 2) fallback: valore già salvato sul post
	if (!$brand && $post_id && function_exists('get_field')) {
		// Usa il valore RAW per evitare oggetti/array formattati
		$brand = get_field('produttore', $post_id, false);
		if (is_array($brand)) { // array di ID
			$brand = (int) reset($brand);
		} elseif (is_object($brand) && isset($brand->term_id)) { // oggetto WP_Term
			$brand = (int) $brand->term_id;
		} else {
			$brand = (int) $brand;
		}
	}

	// Se ho un produttore, limita i modelli a quelli collegati tramite term meta "produttore_collegato"
	if ($brand) {
		$args['meta_query'] = [
			[
				'key' => 'produttore_collegato',
				'value' => $brand,
				'compare' => '='
			]
		];
	}

	// Ordina alfabeticamente
	$args['orderby'] = 'name';
	$args['order'] = 'ASC';

	return $args;
}, 10, 3);

/**
 * Validazione: impedisce di salvare un modello che non appartiene al produttore scelto.
 */
add_filter('acf/validate_value/key=field_68de99ba147c0', function ($valid, $value, $field, $input, $post_id) {
	if ($valid !== true)
		return $valid;

	// Recupera il produttore in formato RAW (ID)
	$brand = function_exists('get_field') ? get_field('produttore', $post_id, false) : 0;
	if (is_array($brand)) {
		$brand = (int) reset($brand);
	} elseif (is_object($brand) && isset($brand->term_id)) {
		$brand = (int) $brand->term_id;
	} else {
		$brand = (int) $brand;
	}

	if (!$brand || !$value)
		return $valid;

	// Normalizza $value (può essere singolo ID o array di ID)
	$values = is_array($value) ? $value : [$value];
	foreach ($values as $val) {
		$linked = (int) get_term_meta((int) $val, 'produttore_collegato', true);
		if ($linked && $linked !== $brand) {
			return __('Il modello selezionato non appartiene al produttore scelto.', 'veicoli');
		}
	}

	return $valid;
}, 10, 5);

/**
 * JS admin: passa il produttore selezionato nelle richieste AJAX del campo "modello" e resetta il campo "modello" quando cambia "produttore".
 */
add_action('acf/input/admin_footer', function () {
	// Carica solo nella schermata del post type "veicolo"
	$screen = function_exists('get_current_screen') ? get_current_screen() : null;
	if (!$screen || $screen->post_type !== 'veicolo')
		return;
	
	// Passa il produttore selezionato nelle richieste AJAX del campo "modello"
	// e resetta il campo "modello" quando cambia "produttore".
	?>
	<script>
		(function ($) {
			if (typeof acf === 'undefined') return;

			// Aggiungi il parametro "brand" all'AJAX del campo Modello
			acf.addFilter('select2_ajax_data', function (data, args, $input, field, instance) {
				var $field = $input.closest('.acf-field');
				if ($field.data('key') === 'field_68de99ba147c0') {
					var brandField = acf.getField('field_68de9931147bf');
					data.brand = brandField ? brandField.val() : '';
				}
				return data;
			});

			// Quando cambia Produttore, svuota Modello
			acf.addAction('change_field/key=field_68de9931147bf', function (field) {
				var modelField = acf.getField('field_68de99ba147c0');
				if (!modelField) return;
				// Svuota valore e UI
				modelField.val(null);
				var $select = modelField.$input();
				if ($select && $select.length) {
					$select.val(null).trigger('change');
				}
			});
		})(jQuery);
	</script>
	<script>
		(function ($) {
			if (typeof acf === 'undefined') return;

			var keyBrand = 'field_68de9931147bf'; // <-- chiave campo Produttore
			var keyModel = 'field_68de99ba147c0'; // <-- chiave campo Modello
            var currentBrandId = 0; // cache del valore corrente del produttore

			function getBrandVal(){
				// 1) prova via API ACF
				var brandField = acf.getField(keyBrand);
				var val = brandField ? brandField.val() : null;
				// 2) fallback: leggi dall'input/select del DOM
				if (!val) {
					var $wrap = $('.acf-field[data-key="' + keyBrand + '"]');
					if ($wrap.length) {
						var $input = $wrap.find('input[type="hidden"], select').first();
						val = $input.length ? $input.val() : null;
					}
				}
				// normalizza (array/string/number)
				if (Array.isArray(val)) {
					val = val[0] || null;
				}
				if (typeof val === 'string' && val.indexOf(',') > -1) {
					val = val.split(',')[0];
				}
				val = parseInt(val, 10);
				if (!isNaN(val) && val > 0) {
					currentBrandId = val; // aggiorna cache
					return val;
				}
				// ritorna eventuale cache valida
				return currentBrandId || 0;
			}

			// aggiorna stato lock UI alla prima pronta e ad ogni change del Produttore
			function updateModelLock(){
				currentBrandId = getBrandVal();
				var locked = parseInt(currentBrandId, 10) <= 0;
				var $wrap = $('.acf-field[data-key="' + keyModel + '"], #modello');
				$wrap.toggleClass('is-locked', locked);
			}

			acf.addAction('ready', updateModelLock);
			acf.addAction('change_field/key=' + keyBrand, updateModelLock);
		})(jQuery);
	</script>
	<?php
});

/**
 * Se un termine "modello" collegato al post non ha ancora il meta "produttore_collegato",
 * assegnalo automaticamente al produttore selezionato nel veicolo.
 */
add_action('acf/save_post', function ($post_id) {
	if (get_post_type($post_id) !== 'veicolo') {
		return;
	}

	// Produttore (singolo)
	$brand = function_exists('get_field') ? get_field('produttore', $post_id, false) : 0;
	if (is_array($brand)) {
		$brand = (int) reset($brand);
	} else {
		$brand = (int) $brand;
	}
	if (!$brand)
		return;

	// Modello/i selezionati
	$models = function_exists('get_field') ? get_field('modello', $post_id, false) : [];
	if (!$models)
		return;
	if (!is_array($models)) {
		$models = [$models];
	}

	foreach ($models as $model_id) {
		$model_id = (int) $model_id;
		if (!$model_id)
			continue;

		$linked = get_term_meta($model_id, 'produttore_collegato', true);
		if (!$linked) {
			update_term_meta($model_id, 'produttore_collegato', $brand);
		}
	}
}, 30); // dopo che ACF salva i termini