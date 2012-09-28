<?php
// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }
?>
<?php do_action( 'tribe_events_importexport_before_import_info_box' ); ?>
<?php do_action( 'tribe_events_importexport_before_import_info_box_tab_' . self::$pluginSlug ); ?>
<div id="modern-tribe-info" style="max-width: 800px; padding-top: 15px;">
	<h2><?php printf( __( 'How to Import Events from %s', 'tribe-events-calendar' ), self::$pluginShortName ); ?></h2>
	<h4><?php _e( 'Instructions', 'tribe-events-calendar' ); ?></h4>
	<?php do_action( 'tribe_events_importexport_import_instructions_tab_' . self::$pluginSlug ); ?>
	<?php do_action( 'tribe_events_importexport_import_instructions' ); ?>
</div>
<?php do_action( 'tribe_events_importexport_after_import_info_box' ); ?>
<?php do_action( 'tribe_events_importexport_after_import_info_box_tab_' . self::$pluginSlug ); ?>
<?php // TODO: Code this table.
?>
<table class="wp-list-table widefat tribe-events-importer-table">
<thead>
<tr>
<th>Location</th>
<th>Radius</th>
<th>Category</th>
<th>Initiated</th>
<th>Last Updates</th>
<th>Frequency</th>
<th># Imported</th>
</tr>
</thead>
<tbody>
<tr class="alternate">
<td>Boston, MA</td><td>25 Mi</td><td>Concert</td><td>10/12/2012</td><td>10/19/2012 18:00</td><td>Hourly</td><td>1,235</td>
</tr>
<tr>
<td>95060</td><td>5 Mi</td><td>All</td><td>10/12/2012</td><td>10/19/2012 0:00</td><td>Daily</td><td>546</td>
</tr>
</tbody>
</table>
<div style="margin: 20px;">
<p><strong>Search Eventful: </strong><input type="text" value="Location" />
<select>
<option>5mi</option>
</select>
<select>
<option>Category</option>
</select>
<input type="text" size="5" value="Today" />
<input type="submit" class="button-primary" value="Search" />
</p>
</div>