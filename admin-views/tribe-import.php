<?php
// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }
?>
<?php do_action( 'tribe_events_importexport_before_import_info_box' ); ?>
<?php do_action( 'tribe_events_importexport_before_import_info_box_tab_' . self::$pluginSlug ); ?>
<div id="modern-tribe-info" style="max-width: 800px; padding-top: 15px;">
	<?php do_action( 'tribe_events_importexport_import_info_box_top' ); ?>
	<?php do_action( 'tribe_events_importexport_import_info_box_top_tab_' . self::$pluginSlug ); ?>
	<?php do_action( 'tribe_events_importexport_import_instructions' ); ?>
	<?php do_action( 'tribe_events_importexport_import_instructions_tab_' . self::$pluginSlug ); ?>
	<?php do_action( 'tribe_events_importexport_import_info_box_bottom' ); ?>
	<?php do_action( 'tribe_events_importexport_import_info_box_bottom_tab_' . self::$pluginSlug ); ?>
</div>
<?php do_action( 'tribe_events_importexport_after_import_info_box' ); ?>
<?php do_action( 'tribe_events_importexport_after_import_info_box_tab_' . self::$pluginSlug ); ?>

<?php
do_action( 'tribe_events_importexport_before_saved_imports_table' );
do_action( 'tribe_events_importexport_before_saved_imports_table_tab' . self::$pluginSlug );

do_action( 'tribe_events_importexport_saved_imports_table' );
do_action( 'tribe_events_importexport_saved_imports_table_tab' . self::$pluginSlug );

do_action( 'tribe_events_importexport_after_saved_imports_table' );
do_action( 'tribe_events_importexport_after_saved_imports_table_tab' . self::$pluginSlug );
?>
<?php
do_action( 'tribe_events_importexport_before_import_form' );
do_action( 'tribe_events_importexport_before_import_form_tab_' . self::$pluginSlug );
?>

<?php
do_action( 'tribe_events_importexport_import_form' );
do_action( 'tribe_events_importexport_import_form_tab_' . self::$pluginSlug );
?>
<?php
do_action( 'tribe_events_importexport_after_import_form' );
do_action( 'tribe_events_importexport_after_import_form_tab_' . self::$pluginSlug );
?>
<?php
do_action( 'tribe_events_importexport_before_import_table' );
do_action( 'tribe_events_importexport_before_import_table_tab_' . self::$pluginSlug );
?>
<?php
do_action( 'tribe_events_importexport_import_table' );
do_action( 'tribe_events_importexport_import_table_tab_' . self::$pluginSlug );
?>
<?php
do_action( 'tribe_events_importexport_after_import_table' );
do_action( 'tribe_events_importexport_after_import_table_tab_' . self::$pluginSlug );
?>
<?php
do_action( 'tribe_events_importexport_after_import_page' );
do_action( 'tribe_events_importexport_after_import_page_tab_' . self::$pluginSlug );