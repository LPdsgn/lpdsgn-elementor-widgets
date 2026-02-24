/**
 * File: assets/veicoli-widgets.js
 * Script per i widget Elementor Veicoli
 */

(function ($) {
	"use strict";

	// ==========================================
	// FILTRO VEICOLI
	// ==========================================

	function initVeicoliFilter() {
		$(".veicoli-filter").each(function () {
			var $form = $(this);
			var $wrapper = $form.closest(".veicoli-filter-wrapper");
			var $loading = $wrapper.find(".filter-loading");
			var loopId = $form.data("loop-id");
			
			// Gestione accordion mobile - usa delegazione eventi
			$wrapper.find(".veicoli-filter-toggle").off("click").on("click", function(e) {
				e.preventDefault();
				var $toggle = $(this);
				var $content = $toggle.siblings(".veicoli-filter-content");
				var isOpen = $toggle.attr("aria-expanded") === "true";
				
				if (isOpen) {
					$toggle.attr("aria-expanded", "false");
					$content.removeClass("is-open");
				} else {
					$toggle.attr("aria-expanded", "true");
					$content.addClass("is-open");
				}
			});

			// Trova il Loop Grid di Elementor
			var $loopGrid = null;
			
			if (loopId) {
				// Cerca prima per ID custom, poi come classe
				$loopGrid = $("#" + loopId);
				if (!$loopGrid.length) {
					$loopGrid = $("." + loopId);
				}
			}
			
			// Se non trovato, cerca nella sezione corrente
			if (!$loopGrid || !$loopGrid.length) {
				$loopGrid = $form.closest(".elementor-section").find(".elementor-loop-container").first();
			}
			
			// Fallback: cerca il primo loop grid nella pagina
			if (!$loopGrid || !$loopGrid.length) {
				$loopGrid = $(".elementor-loop-container").first();
			}
			
			console.log('Veicoli Filter: Loop Grid trovato:', $loopGrid.length > 0, $loopGrid);

			// Collassa accordion su mobile dopo submit
			function closeAccordion() {
				setTimeout(function() {
					var $toggle = $wrapper.find(".veicoli-filter-toggle");
					var $content = $wrapper.find(".veicoli-filter-content");
					$toggle.attr("aria-expanded", "false");
					$content.removeClass("is-open");
				}, 500);
			}

			// Submit del form
			$form.on("submit", function (e) {
				e.preventDefault();
				applyFilter();
				closeAccordion();
			});

			// Reset filtri
			$form.find(".btn-reset").on("click", function () {
				$form[0].reset();
				applyFilter();
				closeAccordion();
			});

			function applyFilter() {
				var formData = {
					action: "filter_veicoli_elementor",
					nonce: veicoliAjax.nonce,
					produttore: $form.find('[name="produttore"]').val(),
					modello: $form.find('[name="modello"]').val(),
					alimentazione: $form.find('[name="alimentazione"]').val(),
					segmento: $form.find('[name="segmento"]').val(),
					cambio: $form.find('[name="cambio"]').val(),
					prezzo_min: $form.find('[name="prezzo_min"]').val(),
					prezzo_max: $form.find('[name="prezzo_max"]').val(),
					anticipo: $form.find('[name="anticipo"]').val(),
					search: $form.find('[name="search"]').val(),
				};

				console.log('Veicoli Filter: Invio richiesta AJAX', formData);
				$loading.fadeIn(200);

				$.ajax({
					url: veicoliAjax.ajaxurl,
					type: "POST",
					data: formData,
					success: function (response) {
						console.log('Veicoli Filter: Risposta ricevuta', response);
						if (response.success) {
							filterLoopGrid(response.data.post_ids);
						} else {
							console.error('Veicoli Filter: Risposta non valida', response);
						}
						$loading.fadeOut(200);
					},
					error: function (xhr, status, error) {
						console.error('Veicoli Filter: Errore AJAX', error, xhr.responseText);
						$loading.fadeOut(200);
						alert("Si è verificato un errore. Riprova.");
					},
				});
			}

			function filterLoopGrid(postIds) {
				if (!$loopGrid || !$loopGrid.length) {
					console.error("Veicoli Filter: Loop Grid non trovato!");
					alert('Errore: Loop Grid non trovato. Verifica che il widget sia nella stessa pagina del filtro.');
					return;
				}

				console.log('Veicoli Filter: Applico filtro per', postIds.length, 'veicoli', postIds);

				// Cerca items del loop - Elementor usa .e-loop-item
				var $items = $loopGrid.find(".e-loop-item");
				
				console.log('Veicoli Filter: Trovati', $items.length, 'elementi nel loop');

				if (postIds.length === 0) {
					// Nessun risultato
					console.log('Veicoli Filter: Nessun risultato - nascondo tutti');
					$items.fadeOut(300);

					if (!$loopGrid.find(".no-results-message").length) {
						$loopGrid.append(
							'<div class="no-results-message" style="text-align:center;padding:60px 20px;grid-column:1/-1;"><h3>Nessun veicolo trovato</h3><p>Prova a modificare i filtri di ricerca</p></div>'
						);
					}
				} else {
					// Rimuovi messaggio "nessun risultato"
					$loopGrid.find(".no-results-message").remove();

					// Mostra/nascondi items in base ai risultati
					var visibleCount = 0;
					var hiddenCount = 0;
					
					$items.each(function () {
						var $item = $(this);
						var itemId = null;
						
						// Estrai ID dalla classe "post-{ID}"
						var classes = $item.attr('class');
						if (classes) {
							var match = classes.match(/post-(\d+)/);
							if (match) {
								itemId = parseInt(match[1]);
							}
						}
						
						console.log('Veicoli Filter: Item con ID', itemId, 'in array?', postIds.includes(itemId));

						if (itemId && postIds.includes(itemId)) {
							$item.fadeIn(300);
							visibleCount++;
						} else {
							$item.fadeOut(300);
							hiddenCount++;
						}
					});
					
					console.log('Veicoli Filter: Risultato - Visibili:', visibleCount, 'Nascosti:', hiddenCount);
				}

				// Trigger evento per compatibilità con altri plugin
				$(document).trigger("veicoli_filtered", [postIds]);
			}

			// Filtro dipendente: Modello in base a Produttore
			$form.find('[name="produttore"]').on("change", function () {
				var produttoreSlug = $(this).val();
				var $modelloSelect = $form.find('[name="modello"]');

				if (!produttoreSlug) {
					$modelloSelect.find("option").show();
					return;
				}

				// Filtra i modelli (richiede data attribute sui modelli)
				$modelloSelect.find("option").each(function () {
					var $option = $(this);
					var produttore = $option.data("produttore");

					if (!produttore || produttore === produttoreSlug) {
						$option.show();
					} else {
						$option.hide();
					}
				});

				$modelloSelect.val("");
			});
		});
	}

	// ==========================================
	// PREZZO DINAMICO VEICOLO
	// ==========================================

	function initVeicoloPricing() {
		$(".veicolo-pricing-wrapper").each(function () {
			var $wrapper = $(this);
			// Lo script JSON è sibling del wrapper, non child
			var $dataScript = $wrapper.siblings(".veicoli-data");

			// Fallback: cerca anche come child per retrocompatibilità
			if (!$dataScript.length) {
				$dataScript = $wrapper.find(".veicoli-data");
			}

			if (!$dataScript.length) {
				console.warn("Script veicoli-data non trovato");
				return;
			}

			var data = JSON.parse($dataScript.text());
			var tipoNoleggio = data.tipo_noleggio; // 'lungo_termine' o 'breve_termine'

			// === BREVE TERMINE ===
			if (tipoNoleggio === 'breve_termine') {
				initPricingBreveTermine($wrapper, data);
			}
			// === LUNGO TERMINE ===
			else {
				initPricingLungoTermine($wrapper, data);
			}
		});
	}

	// Pricing per Lungo Termine
	function initPricingLungoTermine($wrapper, data) {
		var piani = data.piani;
		var anticipoDisponibile = data.anticipo_disponibile;
		var anticipoImporto = data.importo_anticipo;
		var anticipoAttivo = anticipoDisponibile;

		var $prezzoCorrente = $wrapper.find(".prezzo-valore");
		var $durataCorrente = $wrapper.find(".dettaglio-durata");
		var $kmCorrente = $wrapper.find(".dettaglio-km");
		var $anticipoValore = $wrapper.find(".anticipo-valore");
		var $toggleAnticipo = $wrapper.find(".toggle-anticipo");
		var $pianoDropdown = $wrapper.find(".piano-dropdown");

		// Elementi confronto prezzi
		var $prezzoConAnticipo = $wrapper.find(".prezzo-con-anticipo");
		var $prezzoSenzaAnticipo = $wrapper.find(".prezzo-senza-anticipo");
		var $risparmioMensile = $wrapper.find(".risparmio-mensile");

		// Cambio piano di noleggio
		$pianoDropdown.on("change", function () {
			var index = $(this).val();
			var piano = piani[index];
			aggiornaPrezzi(piano);
		});

		// Toggle anticipo
		$toggleAnticipo.on("change", function () {
			anticipoAttivo = $(this).is(":checked");
			var index = $pianoDropdown.val();
			var piano = piani[index];
			aggiornaPrezzi(piano);
		});

		function aggiornaPrezzi(piano) {
			var prezzoConAnticipo = parseFloat(piano.prezzo_con_anticipo || 0);
			var prezzoSenzaAnticipo = parseFloat(piano.prezzo_senza_anticipo);

			// Determina quale prezzo mostrare
			var prezzoCorrente =
				anticipoDisponibile && anticipoAttivo
					? prezzoConAnticipo
					: prezzoSenzaAnticipo;

			// Animazione cambio prezzo
			$prezzoCorrente.addClass("updating");
			setTimeout(function () {
				$prezzoCorrente
					.text(formatPrice(prezzoCorrente) + "€")
					.attr("data-price", prezzoCorrente);
				$prezzoCorrente.removeClass("updating");
			}, 300);

			// Aggiorna durata e km
			$durataCorrente
				.text(piano.durata + " mesi")
				.attr("data-durata", piano.durata);
			$kmCorrente
				.text(formatNumber(piano.kilometri) + " km")
				.attr("data-km", piano.kilometri);

			// Aggiorna valore anticipo nella sezione dettagli piano
			if ($anticipoValore.length) {
				var testoAnticipo =
					anticipoAttivo && anticipoDisponibile
						? formatPrice(anticipoImporto) + "€"
						: "ZERO";
				$anticipoValore.text(testoAnticipo);
			}

			// Aggiorna confronto prezzi
			if ($prezzoConAnticipo.length) {
				$prezzoConAnticipo.text(formatPrice(prezzoConAnticipo) + "€/mese");
				$prezzoSenzaAnticipo.text(formatPrice(prezzoSenzaAnticipo) + "€/mese");

				var risparmio = prezzoSenzaAnticipo - prezzoConAnticipo;
				$risparmioMensile.text(formatPrice(risparmio) + "€");
			}

			// Trigger evento per tracking o integrazioni
			$(document).trigger("veicolo_price_changed", [
				{
					piano: piano,
					prezzo: prezzoCorrente,
					anticipo: anticipoAttivo,
					tipo: 'lungo_termine'
				},
			]);
		}
	}

	// Pricing per Breve Termine
	function initPricingBreveTermine($wrapper, data) {
		var prezzi = data.prezzi;
		var $prezzoCorrente = $wrapper.find(".prezzo-valore");
		var $durataButtons = $wrapper.find(".durata-button");

		// Click su pulsante durata
		$durataButtons.on("click", function () {
			var $button = $(this);
			var index = $button.data("index");
			var giorni = $button.data("giorni");
			var prezzo = parseFloat($button.data("prezzo"));

			// Aggiorna stato attivo
			$durataButtons.removeClass("active").attr("aria-pressed", "false");
			$button.addClass("active").attr("aria-pressed", "true");

			// Aggiorna prezzo con animazione
			$prezzoCorrente.addClass("updating");
			setTimeout(function () {
				$prezzoCorrente
					.text(formatPrice(prezzo) + "€")
					.attr("data-price", prezzo);
				$prezzoCorrente.removeClass("updating");
			}, 300);

			// Trigger evento per tracking
			$(document).trigger("veicolo_price_changed", [
				{
					giorni: giorni,
					prezzo: prezzo,
					tipo: 'breve_termine'
				},
			]);
		});
	}

	// Helper functions
	function formatPrice(price) {
		return Math.round(price).toLocaleString("it-IT");
	}

	function formatNumber(num) {
		return num.toLocaleString("it-IT");
	}

	// ==========================================
	// RICHIESTA PREVENTIVO
	// ==========================================

	window.veicoliRichiestaPreventivo = function (button) {
		var $wrapper = $(button).closest(".veicolo-pricing-wrapper");
		// Lo script JSON è sibling del wrapper
		var $dataScript = $wrapper.siblings(".veicoli-data");

		// Fallback: cerca anche come child
		if (!$dataScript.length) {
			$dataScript = $wrapper.find(".veicoli-data");
		}

		if (!$dataScript.length) {
			alert("Si è verificato un errore. Ricarica la pagina.");
			return false;
		}

		var data = JSON.parse($dataScript.text());
		var piani = data.piani;
		var anticipoDisponibile = data.anticipo_disponibile;
		var anticipoImporto = data.importo_anticipo;
		var veicoloTitle = data.veicolo_title;

		var $pianoDropdown = $wrapper.find(".piano-dropdown");
		var $toggleAnticipo = $wrapper.find(".toggle-anticipo");

		var pianoIndex = $pianoDropdown.val();
		var piano = piani[pianoIndex];
		var conAnticipo = anticipoDisponibile && $toggleAnticipo.is(":checked");
		var prezzo = conAnticipo ? piano.prezzo_con_anticipo : piano.prezzo_senza_anticipo;

		var messaggio =
			"Richiesta preventivo per:\n\n" +
			"Veicolo: " +
			veicoloTitle +
			"\n" +
			"Piano: " +
			piano.durata +
			" mesi / " +
			piano.kilometri.toLocaleString("it-IT") +
			" km\n" +
			"Anticipo: " +
			(conAnticipo ? anticipoImporto.toLocaleString("it-IT") + "€" : "ZERO") +
			"\n" +
			"Prezzo mensile: " +
			prezzo.toLocaleString("it-IT") +
			"€";

		alert(messaggio);

		return false;
	};

	// ==========================================
	// RICHIESTA PREVENTIVO - POPUP ELEMENTOR
	// ==========================================

	function initPreventivoPopup() {
		// Gestisce sia bottoni interni al widget che esterni nel template
		$(document).on("click", ".btn-richiedi-preventivo > a", function (e) {
			e.preventDefault();

			var $button = $(this);
			var $wrapper = jQuery(".veicolo-pricing-wrapper");

			if (!$wrapper.length) {
				alert(
					"Errore: widget pricing non trovato. Verifica che il widget sia presente nella pagina."
				);
				return;
			}

			// Determina il tipo di noleggio PRIMA di selezionare il popup
			var $dataScript = $wrapper.siblings(".veicoli-data").first();
			if (!$dataScript.length) {
				$dataScript = $wrapper.find(".veicoli-data").first();
			}

			if (!$dataScript.length) {
				alert("Errore nel caricamento dei dati. Ricarica la pagina.");
				return;
			}

			var data = JSON.parse($dataScript.text().trim());
			var tipoNoleggio = data.tipo_noleggio;

			// Seleziona il popup ID in base al tipo di noleggio
			var popupId =
				$button.data("popup-id") ||
				$button.closest(".elementor-widget-button").data("popup-id");

			if (!popupId && typeof veicoliAjax !== "undefined") {
				popupId =
					tipoNoleggio === "breve_termine"
						? veicoliAjax.popupIdBreveTermine
						: veicoliAjax.popupIdLungoTermine;
			}

			if (!popupId) {
				alert(
					"Errore: non è stato possibile determinare l'ID del popup. Contatta l'amministratore."
				);
				return;
			}
			
			var veicoloNome = data.veicolo_title;
			var preventivoData = {
				veicolo_nome: veicoloNome,
				tipo_noleggio: tipoNoleggio,
			};

			// === BREVE TERMINE ===
			if (tipoNoleggio === "breve_termine") {
				var $activeButton = $wrapper.find(".durata-button.active");
				var giorni = $activeButton.data("giorni");
				var prezzoGiornaliero = parseFloat($activeButton.data("prezzo"));

				preventivoData.noleggio_durata =
					giorni + (giorni == 1 ? " giorno" : " giorni");
				preventivoData.prezzo_giornaliero = prezzoGiornaliero + "€/giorno";
			}
			// === LUNGO TERMINE ===
			else {
				var piani = data.piani;
				var pianoIndex = $wrapper.find(".piano-dropdown").val();
				var piano = piani[pianoIndex];
				var anticipoAttivo = $wrapper
					.find(".toggle-anticipo")
					.is(":checked");
				var anticipoImporto =
					data.anticipo_disponibile && anticipoAttivo
						? data.importo_anticipo
						: 0;
				var prezzoMensile =
					anticipoAttivo && piano.prezzo_con_anticipo
						? piano.prezzo_con_anticipo
						: piano.prezzo_senza_anticipo;

				preventivoData.piano_durata = piano.durata;
				preventivoData.piano_km = piano.kilometri;
				preventivoData.anticipo_attivo = anticipoAttivo ? "Sì" : "No";
				preventivoData.anticipo_importo = anticipoImporto;
				preventivoData.prezzo_mensile = prezzoMensile;
			}

			sessionStorage.setItem(
				"veicolo_preventivo",
				JSON.stringify(preventivoData)
			);

			// Apri popup Elementor
			if (
				typeof elementorProFrontend !== "undefined" &&
				elementorProFrontend.modules.popup
			) {
				elementorProFrontend.modules.popup.showPopup({
					id: popupId,
				});
			} else {
				// Fallback se Elementor Pro non è disponibile
				console.error("Elementor Pro popup module non disponibile");
				alert(
					"Per utilizzare questa funzione è necessario Elementor Pro con i popup abilitati."
				);
			}
		});
	}

	// Popola campi nascosti quando il popup si apre
	$(document).on("elementor/popup/show", function (event, id, instance) {
		var preventivoData = sessionStorage.getItem("veicolo_preventivo");

		if (!preventivoData) return;

		var data = JSON.parse(preventivoData);
		var $popup = $(instance.$element);

		// Popola campi comuni
		$popup.find('input[name="form_fields[veicolo_nome]"]').val(data.veicolo_nome);

		// === BREVE TERMINE ===
		if (data.tipo_noleggio === 'breve_termine') {
			$popup.find('input[name="form_fields[noleggio_durata]"]').val(data.noleggio_durata);
			$popup.find('input[name="form_fields[prezzo_giornaliero]"]').val(data.prezzo_giornaliero);

			// Riepilogo visivo
			var riepilogo = `
				<div class="preventivo-riepilogo">
					<h4 class="elementor-hidden-mobile">Riepilogo selezione:</h4>
					<p>
						<strong>Veicolo:</strong> ${data.veicolo_nome}
					</p>
					<p>
						<strong>Durata:</strong> ${data.noleggio_durata}
					</p>
					<p><strong>Prezzo:</strong> <span class="rata">${data.prezzo_giornaliero}</span></p>
				</div>`;

			$popup.find("#riepilogo").append(riepilogo);
		}
		// === LUNGO TERMINE ===
		else {
			$popup.find('input[name="form_fields[piano_durata]"]').val(data.piano_durata + " mesi");
			$popup.find('input[name="form_fields[piano_km]"]').val(data.piano_km.toLocaleString("it-IT") + " km");
			$popup.find('input[name="form_fields[anticipo_attivo]"]').val(data.anticipo_attivo);
			$popup.find('input[name="form_fields[anticipo_importo]"]').val(data.anticipo_importo + "€");
			$popup.find('input[name="form_fields[prezzo_mensile]"]').val(data.prezzo_mensile + "€/mese");

			// Riepilogo visivo
			var riepilogo = `
				<div class="preventivo-riepilogo">
					<h4 class="elementor-hidden-mobile">Riepilogo selezione:</h4>
					<p>
						<strong>Veicolo:</strong> ${data.veicolo_nome}
					</p>
					<p>
						<strong>Noleggio:</strong> ${data.piano_durata} mesi / ${data.piano_km.toLocaleString("it-IT")} km
					</p>
					<p>
						<strong>Anticipo:</strong> ${(data.anticipo_importo > 0
				? data.anticipo_importo.toLocaleString("it-IT") + "€"
				: "ZERO")}
					</p>
					<p><strong>Rata mensile:</strong> <span class="rata">${data.prezzo_mensile.toLocaleString("it-IT")} €/mese</span></p>
				</div>`;

			$popup.find("#riepilogo").append(riepilogo);
		}

		// Pulisci sessionStorage dopo utilizzo
		sessionStorage.removeItem("veicolo_preventivo");
	});

	// ==========================================
	// INIT AL CARICAMENTO
	// ==========================================

	$(window).on("elementor/frontend/init", function () {
		// Init per Elementor frontend
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/veicoli_filter.default",
			function ($scope) {
				initVeicoliFilter();
			}
		);

		elementorFrontend.hooks.addAction(
			"frontend/element_ready/veicolo_pricing.default",
			function ($scope) {
				initVeicoloPricing();
			}
		);
	});

	// Init per pagine normali (non editor Elementor)
	$(document).ready(function () {
		if (!$("body").hasClass("elementor-editor-active")) {
			initVeicoliFilter();
			initVeicoloPricing();
			initPreventivoPopup();
		}
	});
})(jQuery);
