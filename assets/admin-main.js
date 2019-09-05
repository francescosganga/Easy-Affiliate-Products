jQuery(document).ready(function($) {
	$("select").each(function() {
		$(this).val($(this).attr("data-current-value"));
	});
});