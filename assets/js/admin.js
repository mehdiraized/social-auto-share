jQuery(document).ready(function ($) {
	// Initialize Select2 for multiselect fields
	$(".setting-field select[multiple]").select2({
		width: "400px",
		placeholder: $(this).data("placeholder"),
		allowClear: true,
		closeOnSelect: false,
	});

	// Initialize Select2 for regular select fields
	$(".setting-field select:not([multiple])").select2({
		width: "400px",
		minimumResultsForSearch: 10,
	});

	// Reinitialize Select2 when settings are shown/hidden
	$(
		'.platform-enabled-field input[type="checkbox"], .content-type-enabled-field input[type="checkbox"]'
	).on("change", function () {
		setTimeout(function () {
			$(".setting-field select").select2("destroy").select2({
				width: "400px",
			});
		}, 100);
	});
});
