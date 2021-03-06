<?php
/**
 * Compact List View Single Event
 * This file contains one event in the list view
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/list/single-event.php
 *
 * @package TribeEventsCalendar
 *
 */
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

// Setup an array of venue details for use later in the template
$venue_details = tribe_get_venue_details();

// Venue
$has_venue_address = ( ! empty( $venue_details['address'] ) ) ? ' location' : '';

// Organizer
$organizer = tribe_get_organizer();

$cost = tribe_get_cost();

?>

<!-- Schedule & Recurrence Details -->
<td class="tribe-event-schedule-details">
	<?php echo tribe_get_start_date();?>
	<?php //echo tribe_events_event_schedule_details(); ?>
</td>

<!-- Event Title -->
<?php do_action( 'tribe_events_before_the_event_title' ) ?>
<td class="tribe-events-condensed-list-event-title">
	<a class="tribe-event-url" href="<?php echo esc_url( tribe_get_event_link() ); ?>" title="<?php the_title_attribute() ?>" rel="bookmark">
		<?php the_title() ?>
	</a>
</td>
<?php do_action( 'tribe_events_after_the_event_title' ) ?>

<?php if ( $venue_details ) : ?>
	<!-- Venue Display Info -->
	<td class="tribe-events-venue-details">
		<?php echo $venue_details["name"]; ?>
	</td> <!-- .tribe-events-venue-details -->
<?php endif; ?>

<?php do_action( 'tribe_events_after_the_meta' ) ?>

<!-- Event Cost -->

	<td class="tribe-events-event-cost">
		<?php if ( tribe_get_cost() ) : ?>
		<span><?php echo tribe_get_cost( null, true ); ?></span>
		<?php endif; ?>
	</td>


<td class="tribe-events-read-more">
	<a href="<?php echo esc_url( tribe_get_event_link() ); ?>" rel="bookmark"><?php esc_html_e( 'View Details', 'the-events-calendar' ) ?></a>
</td>
