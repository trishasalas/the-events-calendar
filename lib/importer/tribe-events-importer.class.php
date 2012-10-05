<?php

// Don't load directly
if ( !defined( 'ABSPATH' ) ) die( '-1' );

if ( !class_exists( 'Tribe_Events_Importer' ) ) {
	
	/**
	 * Abstract class that is extended to create individual importers.
	 *
	 * @since 2.1
	 * @author PaulHughes01
	 */
	abstract class Tribe_Events_Importer {
		
		/**
		 * The singleton instance of the class.
		 * @var object $instance
		 */
		protected static $instance;
		
		/**
		 * The child's classname.
		 * @static
		 * @var string $className
		 */
		protected static $className;
		
		/**
		 * The child plugin name.
		 * @static
		 * @var string $pluginName
		 */
		protected static $pluginName;
		
		/**
		 * The child plugin short name.
		 * @static
		 * @var string $pluginShortName
		 */
		protected static $pluginShortName;
		
		/**
		 * The required TEC version.
		 * @static
		 * @var string $requiredTecVersion
		 */
		protected static $requiredTecVersion;
		
		/**
		 * The plugin's current version.
		 * @static
		 * @var string $currentVersion
		 */
		protected static $currentVersion;
		
		/**
		 * The path to the plugin's main directory.
		 * @static
		 * @var string $pluginPath
		 */
		protected static $pluginPath;
		
		/**
		 * The URL of the plugin's main directory.
		 * @static
		 * @var string $pluginUrl
		 */
		protected static $pluginUrl;
		
		/**
		 * The path to the plugin's main file.
		 * @static
		 * @var string $pluginFilePath
		 */
		protected static $pluginFilePath;
		
		/**
		 * The name of the Import Page.
		 * @static
		 * @var string $importPageName
		 */
		protected static $importPageName;
		
		/**
		 * The plugin slug, used in various places.
		 * @static
		 * @var string $pluginSlug
		 */
		protected static $pluginSlug;
		
		/**
		 * Any errors that may have come up during a function.
		 * @var array $errors
		 */
		protected $errors;
		
		/**
		 * Any alert messages that are not errors that may have come up during a function.
		 * @var array $messages
		 */
		protected $messages;
		
		/**
		 * The array that will be built into JSON to send to the user in response to AJAX requests.
		 * @var array $response
		 */
		protected $response;
		
		/**
		 * A basic array of possible events to import.
		 * @var $possibleEvents
		 */
		protected $possibleEvents;
		
		
		
		/**
		 * Must be defined as a singleton method by the child class.
		 *
		 * @static
		 * @return self
		 */
		abstract static function instance();
		
		/**
		 * The method used by the child class to add WordPress actions.
		 *
		 * @since 2.1
		 * @author PaulHughes01
		 *
		 * @return void
		 */
		abstract protected function addActions();
		
		/**
		 * The method used by the child class to add WordPress filters.
		 *
		 * @since 2.1
		 * @author PaulHughes01
		 *
		 * @return void
		 */
		abstract protected function addFilters();
		
		/**
		 * Abstract method that processes the import form.
		 *
		 * @since 2.1
		 * @author PaulHughes01
		 */
		abstract public function processImportSubmission();
		
		/**
		 * Abstract method that is used to get event data from a source.
		 * The source could be an HTTP request, an XML document, or any other number of types of sources.
		 * It should return data that can be parsed by the setEventData() method.
		 *
		 * @since 2.1
		 * @author PaulHughes01
		 */
		abstract protected function getEventsData();
		
		/**
		 * Parse into events list.
		 *
		 * @param mixed $eventsData
		 * @return array For the events list.
		 */
		abstract protected function parseIntoEventsList( $eventsData );
		
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
		 * @since 2.1
		 * @author PaulHughes01
		 *
		 * @param mixed $eventsData The raw data representing a list of events.
		 * @return array The standardized array (see above) to be passed to the saveEvents() method.
		 */
		abstract protected function setEventsData( $eventsData );
		
		/**
		 * Abstract function for generating the API Key form.
		 *
		 * @since 2.1
		 * @return void
		 */
		abstract public function doApiKeyForm();
		
		/**
		 * Create the import submenu page.
		 *
		 * @since 2.1
		 * @author PaulHughes01
		 *
		 * @return void
		 */
		abstract public function doImportPage();
		
		/**
		 * Create the import tab's import form.
		 *
		 * @since 2.1
		 * @return void
		 */
		abstract public function doImportForm();
		
		/**
		 * Abstract function for writing the import page instructions.
		 *
		 * @since 2.1
		 * @return void
		 */
		abstract public function importTabInstructions();
		
		/**
		 * The class constructor function.
		 *
		 * @since 2.1
		 * @author PaulHughes01
		 *
		 * @return void
		 */
		protected function __construct() {
			$this->className = get_class( $this );
			
			$this->_addActions();
			$this->_addFilters();
			
			$this->addActions();
			$this->addFilters();
		}
		
		/**
		 * The method used to add the actions necessary for the class to work.
		 *
		 * @since 2.1
		 * @author PaulHughes01
		 *
		 * @return void
		 */
		protected function _addActions() {
			add_action( 'admin_notices', array( $this, 'displayMessages' ) );
			add_action( 'admin_notices', array( $this, 'displayErrors' ) );
			
			add_action( 'wp_ajax_tribe_events_' . static::$pluginSlug . '_get_possible_events', array( $this, 'ajaxGetPossibleEvents' ) );
			add_action( 'tribe_events_importexport_content_tab_' . static::$pluginSlug, array( $this, 'generateImportTab' ) );
			add_action( 'tribe_events_importexport_import_instructions_tab_' . static::$pluginSlug, array( $this, 'importTabInstructions' ) );
			add_action( 'tribe_events_importexport_import_form_tab_' . static::$pluginSlug, array( $this, 'doImportForm' ) );
			add_action( 'tribe_events_importexport_apikey_tab_' . static::$pluginSlug, array( $this, 'doApiKeyForm' ) );
			add_action( 'tribe_events_importexport_before_import_table_tab_' . static::$pluginSlug, array( $this, 'addTotalNumberCounter' ) );
			add_action( 'tribe_events_importexport_after_import_table_tab_' . self::$pluginSlug, array( $this, 'doLoadMoreLink' ) );
			add_action( 'tribe_events_importexport_before_import_table_tab_' . self::$pluginSlug, array( $this, 'doOpeningFormTag' ) );
			add_action( 'tribe_events_importexport_after_import_page_tab_' . self::$pluginSlug, array( $this, 'doClosingFormTag' ) );
			
			add_action( 'admin_head', array( $this, '_processImportSubmission' ) );
		}
		
		/**
		 * The method used to add the filters necessary for the class to work.
		 * This can be overridden by a child class, but it is highly recommended that it incorporate
		 * parent::addFilters() so that these essential ones are included. :-p
		 *
		 * @since 2.1
		 * @author PaulHughes01
		 *
		 * @return void
		 */
		protected function _addFilters() {
			add_filter( 'tribe-events-importexport-import-apis', array( $this, 'addEventImporter' ) );
			add_filter( 'tribe-events-importexport-export-apis', array( $this, 'addEventExporter' ) );
		}
		
		/**
		 * The function used to compare versions and initialize the addon.
		 *
		 * @since 2.1
		 * @author PaulHughes01
		 *
		 * @return void
		 */
		public function initAddon() {
			$plugins[] = array( 'plugin_name' => static::$pluginName, 'required_version' => static::$requiredTecVersion, 'current_version' => static::$currentVersion, 'plugin_dir_file' => static::$pluginFilePath );
			return $plugins;
		}
		
		/**
		 * The method is used to add the event origin slug to the event's audit trail.
		 *
		 * @return string The origin slug for a given importer.
		 */
		public static function addEventOrigin() {
			return static::$pluginSlug;
		}
		
		/**
		 * Add Event Importer
		 *
		 * @return array An array representing this specific event importer.
		 */
		public static function addEventImporter( $import_apis ) {
			$import_apis[] = array(
				'slug' => static::$pluginSlug,
				'name' => static::$pluginShortName,
			);
			return $import_apis;
		}
		
		/**
		 * Add Event Importer
		 *
		 * @return array An array representing this specific event importer.
		 */
		public static function addEventExporter( $export_apis ) {
			$export_apis[] = array(
				'slug' => static::$pluginSlug,
				'name' => static::$pluginShortName,
			);
			return $export_apis;
		}
		
		/**
		 * Display errors messages.
		 *
		 * @since 2.1
		 * @author PaulHughes01
		 *
		 * @return void
		 */
		public function displayErrors() {
			if ( isset( $this->errors ) && is_array( $this->errors ) ) {
				foreach ( $this->errors as $error ) {
					echo '<div class="error">';
					echo '<p>' . $error . '</p>';
					echo '</div>';
				}
			}
		}
		
		/**
		 * Display notification messages.
		 *
		 * @since 2.1
		 * @author PaulHughes01
		 *
		 * @return void
		 */
		public function displayMessages() {
			if ( isset( $this->messages ) && is_array( $this->messages ) ) {
				foreach ( $this->messages as $message ) {
					echo '<div class="updated">';
					echo '<p>' . $message . '</p>';
					echo '</div>';
				}
			}
		}
		
		/**
		 * Builds and echoes each list item in the possible events list.
		 *
		 * @param array $eventsData
		 * @return null
		 */
		protected function buildPossibleEventsListItems( $eventsData ) {
			$html = '';
			foreach ( $eventsData as $event ) {
				$sep = ' - ';
				if ( $event['endDate'] == '' )
					$sep = '';
				
				$event_id = $this->getEventByImportApiId( (string) $event['uid'] );
				if ( !$event_id ) {
					$html .= '<tr>';
					$html .= '<th scope="row" class="check-column"><input type="checkbox" name="tribe_events_importexport_events_to_import[]" value="' . $event['uid'] . '" /></th><td>' . $event['startDate'] . $sep . $event['endDate'] . '</td><td><strong>' . $event['title'] . '</strong><div>' . $event['venue'] . '</div></td><td />';
				} else {
					$html .= '<tr class="tribe-greyed">';
					$html .= '<th scope="row" class="check-column" /><td>' . $event['startDate'] . $sep . $event['endDate'] . '</td><td><strong>' . $event['title'] . '</strong><div>' . $event['venue'] . '</div></td><td><strong>Imported</strong></td>';
				}
				$html .= '</tr>';
			}
			return $html;
		}
		 
		
		/**
		 * Gets events using AJAX.
		 *
		 * @since 2.1
		 * @author PaulHughes01
		 *
		 * @uses self::getEvents()
		 * @return void
		 */
		public function ajaxGetPossibleEvents() {
			$possible_events = $this->getEventsData();
			
			if ( $possible_events === false ) {
				$errors = array(
					'error' => $this->errors,
				);
				echo json_encode( $errors );
				die();
			}
			
			$possible_events_list = $this->parseIntoEventsList( $possible_events );
						
			$this->response['body'] = $this->buildPossibleEventsListItems( $possible_events_list );
			$this->response['previous_request'] = $_POST;
			
			echo json_encode( $this->response );
			die();
		}
		
		/**
		 * Function that begins the process of processing the import submission.
		 *
		 * @since 2.1
		 * @author PaulHughes01
		 *
		 * @uses self::processImportSubmission()
		 * @return void
		 */
		public function _processImportSubmission() {
			if ( isset( $_GET['page'] ) && $_GET['page'] == Tribe_Events_ImportExport_Registrar::$slug && ( isset( $_POST['tribe-events-importexport-import-submit'] ) && check_admin_referer( 'submit-import', 'tribe-events-' . static::$pluginSlug . '-submit-import' ) ) || ( isset( $_POST['tribe-events-importexport-import-all'] ) && check_admin_referer( 'submit-import-all', 'tribe-events-' . static::$pluginSlug . '-submit-import-all' ) ) ) {
				$num_imported_events = $this->processImportSubmission();
				
				if ( $num_imported_events > 0 ) {
					$this->messages[] = sprintf( _n( '%s event successfully imported.', '%s events successfully imported.', $num_imported_events, 'tribe-events-calendar' ), $num_imported_events, $num_imported_events );
				} else {
					$this->errors[] = __( 'No events were imported.', 'tribe-events-calendar' );
				}
			}
		}
		
		/**
		 * Class method that is used to save a standardized events array (see the setEventsData() method).
		 *
		 * @since 2.1
		 * @author PaulHughes01
		 *
		 * @uses self::saveEvent()
		 * @param array $eventsArray The standardized array of event arrays to be saved.
		 * @return int The number of events that were imported.
		 */
		protected function saveEvents( $eventsArray ) {
			$i = 0;
			add_filter( 'tribe-post-origin', array( $this, 'addEventOrigin' ) );
			if ( is_array( $eventsArray ) ) {
				foreach ( $eventsArray as $event ) {
					$success = $this->saveEvent( $event );
					if ( $success ) {
						$i++;
					}
				}			
			}
			return $i;
		}
		
		/**
		 * Save individual event from a standardized Event Array (see setEventsData() method).
		 *
		 * @since 2.1
		 * @author PaulHughes01
		 *
		 * @param array $eventArray The standardized event array containing meta information about the event to be saved.
		 * @return int|null The id of the saved event or null if the save failed.
		 */
		protected function saveEvent( $event_array ) {
			$success = false;
			if ( isset( $event_array['event']['startDate'] ) && ( isset( $event_array['event']['endDate'] ) || ( isset( $event_array['event']['allDay'] ) && $event_array['event']['allDay'] == true ) ) && isset( $event_array['event']['title'] ) ) {
				$success = true;
				$event_data = array();
				$event_data['EventAllDay'] = ( isset( $event_array['event']['allDay'] ) && $event_array['event']['allDay'] == true ) ? 'yes' : '';
				$start_date = new DateTime( $event_array['event']['startDate'] );
				if ( !isset( $event_array['event']['endDate'] ) ) {
					$end_date = new DateTime( $event_array['event']['startDate'] );
				} else {
					$end_date = new DateTime( $event_array['event']['endDate'] );
				}
				$event_data['EventAllDay'] = ( $event_array['event']['allDay'] == true ) ? 'yes' : '';
				if ( $event_data['EventAllDay'] != 'yes' ) {
					$event_data['EventStartDate'] = $start_date->format( TribeDateUtils::DBDATEFORMAT );
					$event_data['EventStartHour'] = $start_date->format( 'H' );
					$event_data['EventStartMinute'] = $start_date->format( 'i' );
					$event_data['EventEndDate'] = $end_date->format( TribeDateUtils::DBDATEFORMAT );
					$event_data['EventEndHour'] = $end_date->format( 'H' );
					$event_data['EventEndMinute'] = $end_date->format( 'i' );
				} else {
					$event_data['EventStartDate'] = $start_date->format( TribeDateUtils::DBDATEFORMAT );
					$event_data['EventStartHour'] = '00';
					$event_data['EventStartMinute'] = '00';
					$event_data['EventEndDate'] = $end_date->format( TribeDateUtils::DBDATEFORMAT );
					$event_data['EventEndHour'] = '23';
					$event_data['EventEndMinute'] = '59';
				}
				$event_data['post_status'] = isset( $event_array['event']['post_status'] ) ? $event_array['event']['post_status'] : 'draft';
				$event_data['EventHideFromUpcoming'] = isset( $event_array['event']['hideFromUpcoming'] ) ? $event_array['event']['hideFromUpcoming'] : null;
				$event_data['post_title'] = $event_array['event']['title'];
				$event_data['post_content'] = isset( $event_array['event']['description'] ) ? $event_array['event']['description'] : '';
				$event_data['EventShowMap'] = isset( $event_array['event']['showMap'] ) ? $event_array['event']['showMap'] : null;
				$event_data['EventShowMapLink'] = isset( $event_array['event']['showMapLink'] ) ? $event_array['event']['showMapLink'] : null;
				
				$venue_id = isset( $event_array['venue_meta']['ImportApiID'] ) ? $this->getVenueByImportApiId( $event_array['venue_meta']['ImportApiID'] ) : false;
				if ( $venue_id ) {
					$event_data['Venue']['VenueID'] = $venue_id;
				} else {
					$created_venue = true;
					if ( isset( $event_array['venue']['title'] ) ) $event_data['Venue']['Venue'] = $event_array['venue']['title'];
					if ( isset( $event_array['venue']['address'] ) ) $event_data['Venue']['Address'] = $event_array['venue']['address'];
					if ( isset( $event_array['venue']['city'] ) ) $event_data['Venue']['City'] = $event_array['venue']['city'];
					if ( isset( $event_array['venue']['stateProvince'] ) ) $event_data['Venue']['StateProvince'] = $event_array['venue']['stateProvince'];
					if ( isset( $event_array['venue']['country'] ) ) $event_data['Venue']['Country'] = $event_array['venue']['country'];
					if ( isset( $event_array['venue']['zipCode'] ) ) $event_data['Venue']['Zip'] = $event_array['venue']['zipCode'];
					if ( isset( $event_array['venue']['phone'] ) ) $event_data['Venue']['Phone'] = $event_array['venue']['phone'];
					if ( isset( $event_array['venue_meta']['ImportApiID'] ) ) {
						$event_data['Venue']['ImportApiID'] = $event_array['venue_meta']['ImportApiID'];
						unset( $event_array['venue_meta']['ImportApiID'] );
					}
				}
				$organizer_id = isset( $event_array['organizer_meta']['ImportApiID'] ) ? $this->getOrganizerByImportApiId( $event_array['organizer_meta']['ImportApiID'] ) : false;
				if ( $organizer_id ) {
					$event_data['Organizer']['OrganizerID'] = $organizer_id;
				} else {
					$created_organizer = true;
					if ( isset( $event_array['organizer']['name'] ) ) $event_data['Organizer']['Organizer'] = $event_array['organizer']['name'];
					if ( isset( $event_array['organizer']['phone'] ) ) $event_data['Organizer']['Phone'] = $event_array['organizer']['phone'];
					if ( isset( $event_array['organizer']['url'] ) ) $event_data['Organizer']['Website'] = $event_array['organizer']['url'];
					if ( isset( $event_array['organizer']['email'] ) ) $event_data['Organizer']['Email'] = $event_array['organizer']['email'];
					if ( isset( $event_array['organizer_meta']['ImportApiID'] ) ) {
						$event_data['Organizer']['ImportApiID'] = $event_array['organizer_meta']['ImportApiID'];
						unset( $event_array['organizer_meta']['ImportApiID'] );	
					}
				}
				
				$id = tribe_create_event( $event_data );
				if ( $id ) {
					if ( isset( $event_array['event']['categories'] ) && count( $event_array['event']['categories'] ) > 0 ) {
						wp_set_object_terms( $id, $event_array['event']['categories'], TribeEvents::TAXONOMY );
					}
					if ( isset( $event_array['event']['tags'] ) && count( $event_array['event']['tags'] ) > 0 ) {
						wp_set_post_tags( $id, $event_array['event']['tags'] );
					}
					if ( isset( $event_array['event_meta'] ) && count( $event_array['event_meta'] ) > 0 ) {
						foreach ($event_array['event_meta'] as $key => $var) {
							update_post_meta($id, '_Event'.$key, $var);
						}	
					}
					if ( isset( $event_array['venue_meta'] ) && count( $event_array['venue_meta'] ) > 0 && isset( $event_data['Venue']['ImportApiID'] ) && $created_venue == true ) {
						$venue_id = $this->getVenueByImportApiId( $event_data['Venue']['ImportApiID'] );
						$event_array['venue_meta']['Venue'] = $event_array['venue']['title'];
						tribe_update_venue( $venue_id, $event_array['venue_meta'] );
					}
					if ( isset( $event_array['organizer_meta'] ) && count( $event_array['organizer_meta'] ) > 0 && isset( $event_data['Organizer']['ImportApiID'] ) && $created_organizer == true  ) {
						$venue_id = $this->getOrganizerByImportApiId( $event_data['Organizer']['ImportApiID'] );
						$event_array['organizer_meta']['Organizer'] = $event_array['organizer']['title'];
						tribe_update_organizer( $venue_id, $event_array['organizer_meta'] );
					}
				} else {
					$success = false;
				}
			}
			return $success;
		}
		
		/**
		 * Generate this importer's import tab.
		 *
		 * @since 2.1
		 * @author PaulHughes01
		 *
		 * @return null
		 */
		public function generateImportTab() {
			$tec = TribeEvents::instance();
			require_once( $tec->pluginPath . 'admin-views/tribe-import.php' );
		}
		
		/**
		 * Get a venue based on the Import API ID.
		 *
		 * @since 2.1
		 * @author PaulHughes01
		 *
		 * @param string $venue_api_id The API ID of the venue.
		 * @return int The venue id.
		 */
		protected function getVenueByImportApiId( $venue_api_id ) {
			$args = array(
				'post_type' => TribeEvents::VENUE_POST_TYPE,
				'meta_query' => array( array (
					'key' => '_VenueImportApiID',
					'value' => $venue_api_id,
				) ),
				'posts_per_page' => 1,
			);
			$query = new WP_Query( $args );
			$venue_id = false;
			while( $query->have_posts() ) {
				$query->next_post();
				$venue_id = $query->post->ID;
			}

			return $venue_id;
		}
		
		/**
		 * Get an organizer based on the Import API ID.
		 *
		 * @since 2.1
		 * @author PaulHughes01
		 *
		 * @param string $organizer_api_id The API ID of the organizer.
		 * @return int The organizer id.
		 */
		protected function getOrganizerByImportApiId( $organizer_api_id ) {
			$args = array(
				'post_type' => TribeEvents::ORGANIZER_POST_TYPE,
				'meta_query' => array( array (
					'key' => '_OrganizerImportApiID',
					'value' => $organizer_api_id,
				) ),
				'posts_per_page' => 1,
			);
			$query = new WP_Query( $args );
			$organizer_id = false;
			while( $query->have_posts() ) {
				$query->next_post();
				$organizer_id = $query->post->ID;
			}
			
			return $organizer_id;
		}
		
		/**
		 * Get an event based on the Import API ID.
		 *
		 * @since 2.1
		 * @author PaulHughes01
		 *
		 * @param string $event_api_id The API ID of the event.
		 * @return int The event id.
		 */
		protected function getEventByImportApiId( $event_api_id ) {
			$args = array(
				'post_type' => TribeEvents::POSTTYPE,
				'meta_query' => array( array (
					'key' => '_EventImportApiID',
					'value' => $event_api_id,
				) ),
				'posts_per_page' => 1,
			);
			$query = new WP_Query( $args );
			$event_id = false;			
			while( $query->have_posts() ) {
				$query->next_post();
				$event_id = $query->post->ID;
			}

			return $event_id;
		}
		
		/**
		 * Add the total number counter above the import table.
		 *
		 * @since 2.1
		 * @author PaulHughes01
		 *
		 * @return void
		 */
		public function addTotalNumberCounter() {
			echo '<form method="POST" id="tribe-events-import-all-events-form">';
			wp_nonce_field( 'submit-import-all', 'tribe-events-' . static::$pluginSlug . '-submit-import-all' );
			echo '<span id="tribe-events-import-all-events-form-elements"></span>';
			echo '<p><input style="float:left" type="submit" name="tribe-events-importexport-import-all" id="tribe-events-importexport-import-all" value="' . sprintf( __( 'Import All %s', 'tribe-events-calendar' ), '(0)' ) . '" class="button-secondary" /></p>';
			echo '</form>';
		}
		
		/**
		 * Generates the 'Load more...' link after the import table.
		 *
		 * @since 2.1
		 * @author PaulHughes01
		 *
		 * @return void
		 */
		public function doLoadMoreLink() {
			echo '<div class="tribe-after-table-link"><a href="" id="tribe-events-importexport-' . static::$pluginSlug . '-load-more" style="display:none;">' . apply_filters('tribe-events-importexport-' . static::$pluginSlug . '-load-more-link-text', __( 'Load more...', 'tribe-events-eventful-importer' ) ) . '</a></div>';
		}
		
		/**
		 * Open the import form tag.
		 *
		 * @since 0.1
		 * @author PaulHughes01
		 *
		 * @return void
		 */
		public function doOpeningFormTag() {
			echo '<form method="POST" action="">';
		}
		
		/**
		 * Close the import form tag.
		 *
		 * @since 0.1
		 * @author PaulHughes01
		 *
		 * @return void
		 */
		public function doClosingFormTag() {
			echo '</form>';
		}
	}
}