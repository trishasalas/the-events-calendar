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
jQuery( function($) {
	$("#tribe-events-importexport-list-check-all").click(function() {
		$("[name='tribe_events_events_to_import']").attr("checked", this.checked);
	});
});