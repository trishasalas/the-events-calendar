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
<div class="tribe-events-importer-table">
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
?>
<div class="tribe-after-table-button"><input type="submit" class="button-primary" value="Import Checked" /></div>
</div>
<?php
do_action( 'tribe_events_importexport_after_import_page' );
do_action( 'tribe_events_importexport_after_import_page_tab_' . self::$pluginSlug );