<?php
/**
 * Events Navigation Bar Module Template
 * Renders our events navigation bar used across our views
 *
 * $filters and $views variables are loaded in and coming from
 * the show funcion in: lib/tribe-events-bar.class.php
 *
 * @package TribeEventsCalendar
 * @since  3.0
 * @author Modern Tribe Inc.
 *
 */
?>

<?php

$filters = tribe_events_get_filters();
$views   = tribe_events_get_views();

 ?>

<?php do_action('tribe_events_bar_before_template') ?>
<div id="tribe-events-bar">

	<form id="tribe-bar-form" class="tribe-clearfix" name="tribe-bar-form" method="post" action="<?php echo add_query_arg( array() ); ?>">

		<!-- Mobile Filters Toggle -->

		<div id="tribe-bar-collapse-toggle" <?php if ( count( $views ) == 1 ) { ?> class="tribe-bar-collapse-toggle-full-width"<?php } ?>>
			<?php _e( 'Find Events', 'tribe-events-calendar' ) ?><span class="tribe-bar-toggle-arrow"></span>
		</div>

		<!-- Views -->
		<?php if ( count( $views ) > 1 ) { ?>
		<div id="tribe-bar-views">
			<div class="tribe-bar-views-inner tribe-clearfix">
				<h3 class="tribe-events-visuallyhidden"><?php _e( 'Event Views Navigation', 'tribe-events-calendar' ) ?></h3>
				<label><?php _e( 'View As', 'tribe-events-calendar' ); ?></label>
				<ul class="tribe-bar-views-list">
					<?php $i = 0; foreach ( $views as $view ) :  ?>
						<li class="tribe-bar-views-option tribe-bar-views-option-<?php echo $view['displaying'] ?> <?php echo tribe_is_view($view['displaying']) ? 'tribe-bar-active' : 'tribe-inactive' ?>" data-view="<?php echo $view['displaying'] ?>" data-tribe-bar-order="<?php echo $i; ?>">
							<a href="<?php echo $view['url'] ?>"><span class="tribe-icon-<?php echo $view['displaying'] ?>"><?php echo $view['anchor'] ?></span></a>
						</li>												
					<?php $i++; endforeach; ?>
					<? /* <option <?php echo tribe_is_view($view['displaying']) ? 'selected' : 'tribe-inactive' ?> value="<?php echo $view['url'] ?>" data-view="<?php echo $view['displaying'] ?>"> */ ?>

				</ul>
			</div><!-- .tribe-bar-views-inner -->
		</div><!-- .tribe-bar-views -->
		<?php } // if ( count( $views ) > 1 ) ?>

		<?php if ( !empty( $filters ) ) { ?>
		<div class="tribe-bar-filters">
			<div class="tribe-bar-filters-inner tribe-clearfix">
				<?php foreach ( $filters as $filter ) : ?>
					<div class="<?php echo esc_attr( $filter['name'] ) ?>-filter">
						<label class="label-<?php echo esc_attr( $filter['name'] ) ?>" for="<?php echo esc_attr( $filter['name'] ) ?>"><?php echo $filter['caption'] ?></label>
						<?php echo $filter['html'] ?>
					</div>
				<?php endforeach; ?>
				<div class="tribe-bar-submit">
					<input class="tribe-events-button tribe-no-param" type="submit" name="submit-bar" value="<?php _e( 'Find Events', 'tribe-events-calendar' ) ?>" />
				</div><!-- .tribe-bar-submit -->
			</div><!-- .tribe-bar-filters-inner -->
		</div><!-- .tribe-bar-filters -->
		<?php } // if ( !empty( $filters ) ) ?>

	</form><!-- #tribe-bar-form -->

</div><!-- #tribe-events-bar -->
<?php do_action('tribe_events_bar_after_template') ?>
