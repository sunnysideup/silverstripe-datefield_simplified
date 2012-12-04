var SimpleDateFieldAjaxValidation = {

	sendInput: function(id, value_value, url_value, settings_value) {
		jQuery.ajax(
			{
				url: url_value,
				dataType: "json",
				data: ({value: value_value,settings: settings_value}),
				success: function(returnDataAsJSON) {
					value = returnDataAsJSON.value;
					success = parseInt(returnDataAsJSON.success);
					if(!success) {
						jQuery("#" + id).attr("value","?");
						jQuery("label[for='"+id+"'].right").text(value);
					}
					else {
						jQuery("label[for='"+id+"'].right").text(" ");
						jQuery("#" + id).attr("value",value);
					}
					
				}
			}
		);
	}
}


