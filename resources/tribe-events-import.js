function ajaxLoadMoreEvents( ajaxUrl, pluginSlug, args ) {
	args['action'] = 'tribe_events_' + pluginSlug + '_get_possible_events';
	jQuery.ajax({
		type: "POST",
		url: ajaxUrl,
		data: args,
		beforeSend: function() {
			jQuery('#tribe-events-importexport-search-spinner').show();
		},
		success: function(data) {
			jQuery('#tribe-events-importexport-search-spinner').hide();
			jQuery('.error').remove();
			try {
				var response = jQuery.parseJSON(data);
			} catch(e) {}
			if ( response != null && typeof response == 'object' && response.error ) {
				var html = '';
				for( i=0; i<response.error.length; i++ ) {
					html = html + '<div class="error"><p>' + response.error[i] + '</p></div>';
				}
				jQuery('#tribe-events-importexport-import-form').append(html);
			}
			if ( response != null && typeof response == 'object' && response.body && response.body.length > 0 ) {
				jQuery('#tribe-events-possible-import-events-list tbody').append(response.body);
				jQuery('#tribe-events-importexport-' + pluginSlug + '-load-more').show();
			}
			if ( response != null && typeof response == 'object' && response.total_items ) {
				jQuery('#tribe-events-importexport-import-all').val(jQuery('#tribe-events-importexport-import-all').val().replace( /\([^\)]*\)/, '(' + response.total_items + ')' ));
			} else {
				jQuery('#tribe-events-importexport-import-all').val(jQuery('#tribe-events-importexport-import-all').val().replace( /\([^\)]*\)/, '(?)' ));
			}
			if ( response != null && typeof response == 'object' && response.previous_request ) {
				jQuery('#tribe-events-import-all-events-form-elements').empty();
				for( i in response.previous_request ) {
					var html = '<input type="hidden" name="' + i + '" value="' + response.previous_request[i] + '" />';
					jQuery('#tribe-events-import-all-events-form-elements').append(html);
				}
			}
			if ( response != null && typeof response == 'object' && response.page_count && args['page'] >= response.page_count ) {
				jQuery('#tribe-events-importexport-' + pluginSlug + '-load-more').hide();
			}
		}
	});
}
function ajaxSaveImportQuery( ajaxUrl, pluginSlug, args ) { 
	jQuery( function($) {
		args['action'] = 'tribe_events_' + pluginSlug + '_save_import_query';
		$.ajax({
			type: "POST",
			url: ajaxUrl,
			data: args,
			beforeSend: function() {
				$('#tribe-events-importexport-save-import-spinner').show();
			},
			success: function(data) {
				$('#tribe-events-importexport-save-import-spinner').hide();
				try {
					var response = jQuery.parseJSON(data);
				} catch(e) {}
				if ( response != null && typeof response == 'object' && response.error ) {
					var html = '';
					for( i=0; i<response.error.length; i++ ) {
						html = html + '<div class="error"><p>' + response.error[i] + '</p></div>';
					}
					jQuery('#tribe-events-importexport-import-form').append(html);
				}
				if ( response != null && typeof response == 'object' && response.body && response.body.length > 0 ) {
					jQuery('#tribe-events-importexport-saved-imports-table tbody').append(response.body);
				}
			}
		});
	});
}
function ajaxDeleteSavedImportQuery( ajaxUrl, pluginSlug, clicked ) {
	jQuery( function($) {
		var args = {
			action: 'tribe_events_' + pluginSlug + '_delete_saved_import_query',
			index: $(clicked).closest("tr")[0].rowIndex - 1
		};
		$.ajax({
			type: "POST",
			url: ajaxUrl,
			data: args,
			beforeSend: function() {
				$(clicked).hide();
			},
			success: function(data) {
				try { 
					var response = jQuery.parseJSON(data);
				} catch(e) {}
				if ( response != null && typeof response == 'object' && response.error ) {
					var html = '';
					for( i=0; i<response.error.length; i++ ) {
						html = html + '<div class="error"><p>' + response.error[i] + '</p></div>';
					}
					jQuery('#tribe-events-importexport-import-form').append(html);
				}
				if ( response != null && typeof response == 'object' && response.success ) {
					$($(clicked).closest("tr")[0]).hide('slow', function() {
							$(this).remove();
					});
				}
			}
		});
	});
}
function tribe_events_import_validate_form() {
	var success = false;
	jQuery( function($) {
		var search_fields = $('#tribe-events-importexport-import-form :input:not(:button, select)');
		var filled_fields = search_fields.filter(function() {
			if ( $(this).is(':checkbox') && !$(this).is(':checked') ) {
				return false;
			}
			return $.trim(this.value) != '';
		});
	
		if ( filled_fields.length > 0 ) {
			success = true;
		}
	});
	return success;
}
jQuery( function($) {
	$("#tribe-events-importexport-list-check-all").click(function() {
		$("[name='tribe_events_events_to_import']").attr("checked", this.checked);
	});
	
	$("#tribe-events-importexport-import-form :submit, #tribe-events-importexport-import-form :button").on( 'click', function() {
		if ( !tribe_events_import_validate_form() ) {
			$("#tribe_error_no_fields_filled").show();
		} else {
			$("#tribe_error_no_fields_filled").hide();
		}
	});
});