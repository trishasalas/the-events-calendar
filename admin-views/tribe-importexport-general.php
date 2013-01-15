<?php
// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }
?>
<?php do_action( 'tribe_events_importexport_before_import_info_box' ); ?>
<?php do_action( 'tribe_events_importexport_before_import_info_box_tab_general' ); ?>
<div id="modern-tribe-info" style="max-width: 800px; padding-top: 15px;">
<?php
	echo '<h2>' . __( 'The Events Calendar: Import / Export', 'tribe-events-calendar' ) . '</h2>';
	echo '<h3>' . __( 'Instructions', 'tribe-events-calendar' ) . '</h3>';
	echo '<p class="admin-indent">' . __( 'To start importing / exporting you Eventful events, input your Eventful API Key below (which you can get from your Eventful account), and choose the status you would like your events to be imported with.', 'tribe-events-calendar' ) . '</p><p class="admin-indent">' . __( 'Save your settings and then move on to the Import or Export tab as needed to setup your criteria.', 'tribe-events-calendar' ) . '</p>';
?>
</div>
<?php do_action( 'tribe_events_importexport_after_import_info_box' ); ?>
<?php do_action( 'tribe_events_importexport_after_import_info_box_tab_general' ); ?>
<?php do_action( 'tribe_events_importexport_before_general_content' ); ?>
<div class="tribe-settings-form">
<form method="POST">
<div class="tribe-settings-form-wrap">
<?php do_action( 'tribe_events_importexport_before_apikeys' ); ?>
<h3><?php _e( 'API Credentials', 'tribe-events-calendar' ); ?></h3>
<?php do_action( 'tribe_events_importexport_apikeys' ); ?>
<?php do_action( 'tribe_events_importexport_after_apikeys' ); ?>

<?php do_action( 'tribe_events_importexport_before_import_settings' ); ?>
<h3><?php _e( 'Import Settings', 'tribe-events-calendar' ); ?></h3>
<p>
<?php _e( 'Default Imported Event Status:', 'tribe-events-calendar' ); ?>
<?php $import_statuses = array(
	'publish' => __( 'Published', 'tribe-events-calendar' ),
	'pending' => __( 'Pending', 'tribe-events-calendar' ),
	'draft' => __( 'Draft', 'tribe-events-calendar' ),
);
?>
 <select name="imported-post-status">
 <?php foreach ( $import_statuses as $key => $value ) {
 	echo '<option value="' . $key . '" ' . selected( $key, $this->getOption( 'imported_post_status' ) ) . '>' . $value . '</option>';
 }
 ?>
</select>
</p>
<?php
do_action( 'tribe_events_importexport_import_settings' );
do_action( 'tribe_events_importexport_after_import_settings' );

do_action( 'tribe_events_importexport_before_export_settings' );
do_action( 'tribe_events_importexport_export_settings' );
do_action( 'tribe_events_importexport_after_export_settings' );

wp_nonce_field( 'tribe-events-importexport-general-settings-nonce-submit', 'tribe-events-importexport-general-settings-nonce' );
?>
<p>
<input type="submit" name="tribe-events-importexport-general-settings-submit" class="button-primary" value="Save Settings" />
</p>
</div>
</form>
</div>
<?php do_action( 'tribe_events_importexport_after_general_content' ); ?>
