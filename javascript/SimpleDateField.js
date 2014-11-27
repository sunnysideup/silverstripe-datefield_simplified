

var SimpleDateFieldAjaxValidation = {

	timer: [],

	milliseconds: 1500,

	url: "",

	setupField: function(id, url){
		this.url = url;
		jQuery("#"+id).on(
			"keyup",
			function() {
				var value = jQuery(this).val();
				if(value.length > 2) {
					window.clearTimeout (SimpleDateFieldAjaxValidation.timer[id]);
					SimpleDateFieldAjaxValidation.timer[id] = window.setTimeout(
						function() {SimpleDateFieldAjaxValidation.sendInput(id, value);},
						SimpleDateFieldAjaxValidation.milliseconds
					);
				}
			}
		);
		jQuery("#"+id).on(
			"change",
			function() {
				var value = jQuery(this).val();
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


