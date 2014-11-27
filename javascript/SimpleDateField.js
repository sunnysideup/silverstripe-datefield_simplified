
jQuery(document).ready(
	function() {
		SimpleDateFieldAjaxValidation.init();
	}
);

var SimpleDateFieldAjaxValidation = {

	timer: [],

	milliseconds: 1500,

	includeKeyUp: false,

	url: "",

	inputSelector: ".simpledatefield",

	setURL: function(url) {
		this.url = url;
	},

	init: function(){
		if(this.includeKeyUp) {
			jQuery("body").on(
				"keyup",
				SimpleDateFieldAjaxValidation.inputSelector,
				function() {
					var value = jQuery(this).val();
					var id = jQuery(this).attr("id");
					if(value.length > 2) {
						window.clearTimeout (SimpleDateFieldAjaxValidation.timer[id]);
						SimpleDateFieldAjaxValidation.timer[id] = window.setTimeout(
							function() {SimpleDateFieldAjaxValidation.sendInput(id, value);},
							SimpleDateFieldAjaxValidation.milliseconds
						);
					}
				}
			);
		}
		jQuery("body").on(
			"change",
			SimpleDateFieldAjaxValidation.inputSelector,
			function() {
				var value = jQuery(this).val();
				var id = jQuery(this).attr("id");
				if(value.length > 2) {
					window.clearTimeout (SimpleDateFieldAjaxValidation.timer[id]);
					SimpleDateFieldAjaxValidation.sendInput(id, value);
				}
			}
		);
	},

	sendInput: function(id, value) {
		window.clearTimeout (SimpleDateFieldAjaxValidation.timer[id]);
		jQuery.ajax(
			{
				url: SimpleDateFieldAjaxValidation.url,
				dataType: "json",
				data: ({value: value}),
				success: function(returnDataAsJSON) {
					value = returnDataAsJSON.value;
					success = parseInt(returnDataAsJSON.success);
					if(success) {
						jQuery("label[for='"+id+"'].right").text(" ");
						jQuery("#" + id).attr("value",value);
					}
					else {
						jQuery("#" + id).attr("value","?").focus();
						jQuery("label[for='"+id+"'].right").text(value);

					}
				}
			}
		)
		.fail(function() {
			jQuery("#" + id).attr("value","?").focus();
			jQuery("label[for='"+id+"'].right").text("???");
		})
		;
	}
}


