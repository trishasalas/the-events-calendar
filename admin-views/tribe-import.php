<?php
// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }
?>
<?php do_action( 'tribe_importexport_before_import_info_box' ); ?>
<?php do_action( 'tribe_importexport_before_import_info_box_tab_' . self::$pluginSlug ); ?>
<div id="modern-tribe-info" style="max-width: 800px; padding-top: 15px;">
	<h2><?php printf( __( 'How to Import Events from %s', 'tribe-events-calendar' ), self::$pluginShortName ); ?></h2>
	<h4><?php _e( 'Instructions', 'tribe-events-calendar' ); ?></h4>
	<?php do_action( 'tribe_importexport_import_instructions_tab_' . self::$pluginSlug ); ?>
	<?php do_action( 'tribe_importexport_import_instructions' ); ?>
</div>
<?php do_action( 'tribe_importexport_after_import_info_box' ); ?>
<?php do_action( 'tribe_importexport_after_import_info_box_tab_' . self::$pluginSlug ); ?>