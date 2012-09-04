<?php

// Don't load directly
if ( !defined( 'ABSPATH' ) ) die( '-1' );

if ( !class_exists( 'Tribe_Events_Importer' ) ) {
	
	/**
	 * Abstract class that is extended to create individual importers.
	 *
	 * @since 0.1
	 * @author PaulHughes01
	 */
	abstract class Tribe_Events_Importer {
		
		/**
		 * The origin that will be stored with the imported events as coming from the given importer.
		 * @static
		 * @var string $origin
		 */
		protected static $eventOrigin;
		
		/**
		 * The singleton instance of the class.
		 * @var object $instance
		 */
		private static $instance;
		
		
		
		
		/**
		 * Singleton method.
		 *
		 * @static
		 * @return self
		 */
		private static function instance() {
			if ( !is_a( self::$instance, __CLASS__ ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}
		
		/**
		 * Abstract method that is used to get event data from a source.
		 * The source could be an HTTP request, an XML document, or any other number of types of sources.
		 * It should return data that can be  parsed by the setEventData() method.
		 *
		 * @since 0.1
		 * @author PaulHughes01
		 */
		abstract protected function getEventsData();
		
		/**
		 * Abstract method that is used to set a given event's data to a standardized 
		 * Events Calendar Import Array.
		 * The returned array can have any number indexes of arrays with the following keys:
		 * [eventTitle], [eventStartDate], [eventEndDate]
		 * The [eventStartDate] and [eventEndDate] values should take the following form:
		 * 'Y-m-d H:i:s'
		 * The returned array could have any of the following keys, along with any others that you would like
		 * to assign post meta to by extending the saveEvent() method:
		 * [eventAllDay], [eventHideFromUpcoming], [eventShowMapLink], [eventShowMap], [eventPostStatus]
		 * [eventVenueId], [eventOrganizerId]
		 *
		 * @since 0.1
		 * @author PaulHughes01
		 *
		 * @param mixed $eventsData The raw data representing a list of events.
		 * @return array The standardized array (see above) to be passed to the saveEvents() method.
		 */
		abstract protected function setEventsData( $eventsData );
		
		/**
		 * The method used to add the actions necessary for the class to work.
		 * This can be overridden by a child class, but it is highly recommended that it incorporate
		 * parent::addActions() so that these essential ones are included. :-p
		 *
		 * @since 0.1
		 * @author PaulHughes01
		 *
		 * @return void
		 */
		protected function addActions() {
		
		}
		
		/**
		 * The method used to add the filters necessary for the class to work.
		 * This can be overridden by a child class, but it is highly recommended that it incorporate
		 * parent::addFilters() so that these essential ones are included. :-p
		 *
		 * @since 0.1
		 * @author PaulHughes01
		 *
		 * @return void
		 */
		protected function addFilters() {
			add_filter( 'tribe-post-origin', array( $this, 'addEventOrigin' ) );
		}
		
		/**
		 * The method used to set the event origin.
		
		/**
		 * The method is used to add the event origin slug to the event's audit trail.
		 *
		 * @return string The origin slug for a given importer.
		 */
		private static function addEventOrigin() {
			return self::$eventOrigin;
		}
		 
		
		/**
		 * Class method that is used to save a standardized events array (see the setEventsData() method).
		 *
		 * @since 0.1
		 * @author PaulHughes01
		 *
		 * @uses self::saveEvent()
		 * @param array $eventsArray The standardized array of event arrays to be saved.
		 * @return void
		 */
		protected function saveEvents( $eventsArray ) {
		
		}
		
		/**
		 * Save individual event from a standardized Event Array (see setEventsData() method).
		 *
		 * @since 0.1
		 * @author PaulHughes01
		 *
		 * @param array $eventArray The standardized event array containing meta information about the event to be saved.
		 * @return int|null The id of the saved event or null if the save failed.
		 */
		protected function saveEvent( $eventArray ) {
		
		}
		
	}
	
}