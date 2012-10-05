<?php
// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }
?>
<?php do_action( 'tribe_events_importexport_before_import_info_box' ); ?>
<?php do_action( 'tribe_events_importexport_before_import_info_box_tab_' . self::$pluginSlug ); ?>
<div id="modern-tribe-info" style="max-width: 800px; padding-top: 15px;">
	<h2><?php printf( __( 'How to Import Events from %s', 'tribe-events-calendar' ), self::$pluginShortName ); ?></h2>
	<h3><?php _e( 'Instructions', 'tribe-events-calendar' ); ?></h3>
	<?php do_action( 'tribe_events_importexport_import_instructions' ); ?>
	<?php do_action( 'tribe_events_importexport_import_instructions_tab_' . self::$pluginSlug ); ?>
	
	<?php do_action( 'tribe_events_importexport_apikey' ); ?>
	<?php do_action( 'tribe_events_importexport_apikey_tab_' . self::$pluginSlug ); ?>
</div>
<?php do_action( 'tribe_events_importexport_after_import_info_box' ); ?>
<?php do_action( 'tribe_events_importexport_after_import_info_box_tab_' . self::$pluginSlug ); ?>
<?php // TODO: Code this table.
?>
<h3>Saved Imports</h3>
<div class="tribe-events-importer-table">
<table class="wp-list-table widefat">
<thead>
<tr>
<th>Location</th>
<th>Radius</th>
<th>Category</th>
<th>Initiated</th>
<th>Last Updates</th>
<th>Frequency</th>
<th># Imported</th>
<th />
</tr>
</thead>
<tbody>
<tr class="alternate">
<td>Boston, MA</td><td>25 Mi</td><td>Concert</td><td>10/12/2012</td><td>10/19/2012 18:00</td><td>Hourly</td><td>1,235</td><td class="tribe-admin-action"><span class="delete row-actions"><a href="">Delete</a></span></td>
</tr>
<tr>
<td>95060</td><td>5 Mi</td><td>All</td><td>10/12/2012</td><td>10/19/2012 0:00</td><td>Daily</td><td>546</td><td class="tribe-admin-action"><span class="delete row-actions"><a href="">Delete</a></span></td>
</tr>
</tbody>
</table>
</div>
<h3>New Import</h3>
<script type="text/javascript">
function ajaxLoadMoreEvents( pluginSlug, args ) {
	args['action'] = 'tribe_events_' + pluginSlug + '_get_possible_events';
	jQuery.ajax({
		type: "POST",
		url: "<?php echo admin_url( 'admin-ajax.php' ); ?>",
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
</script>
<?php
do_action( 'tribe_events_importexport_before_import_form' );
do_action( 'tribe_events_importexport_before_import_form_tab_' . self::$pluginSlug );
?>
<div id="tribe-events-importexport-import-form">
<?php
do_action( 'tribe_events_importexport_import_form' );
do_action( 'tribe_events_importexport_import_form_tab_' . self::$pluginSlug );
?>
</div>
<?php
do_action( 'tribe_events_importexport_after_import_form' );
do_action( 'tribe_events_importexport_after_import_form_tab_' . self::$pluginSlug );
?>
<div class="tribe-events-importer-table" id="tribe-events-import-list-wrapper">
<script type="text/javascript">
jQuery( function($) {
	$("#tribe-events-importexport-list-check-all").click(function() {
		$("[name='tribe_events_events_to_import']").attr("checked", this.checked);
	});
});
</script>
<?php
do_action( 'tribe_events_importexport_before_import_table' );
do_action( 'tribe_events_importexport_before_import_table_tab_' . self::$pluginSlug );
?>
<div class="tribe-before-table-button"><input type="submit" class="button-primary" id="tribe-events-importexport-import-submit" name="tribe-events-importexport-import-submit" value="Import Checked" /></div>
<table id="tribe-events-possible-import-events-list" class="wp-list-table widefat">
<thead>
<tr>
<th class="manage-column column-cb check-column">
<input id="tribe-events-importexport-list-check-all" type="checkbox" />
</th>
<th style="width: 20%">Date(s)</th>
<th>Event</th>
<th />
</tr>
</thead>
<tbody>
</tbody>
</table>
<?php
do_action( 'tribe_events_importexport_after_import_table' );
do_action( 'tribe_events_importexport_after_import_table_tab_' . self::$pluginSlug );
wp_nonce_field( 'submit-import', 'tribe-events-' . self::$pluginSlug . '-submit-import' );
?>
<div class="tribe-after-table-button"><input type="submit" class="button-primary" id="tribe-events-importexport-import-submit" name="tribe-events-importexport-import-submit" value="Import Checked" /></div>
</div>
<?php
do_action( 'tribe_events_importexport_after_import_page' );
do_action( 'tribe_events_importexport_after_import_page_tab_' . self::$pluginSlug );