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
		 * The origin that will be stored with the imported events as coming from the given importer.
		 * @static
		 * @var string $origin
		 */
		protected static $eventOrigin;
		
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
		abstract public function processImportForm();
		
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
			add_action( 'admin_menu', array( $this, 'addImportPage' ) );
			add_action( 'admin_notices', array( $this, 'displayErrors' ) );
			
			add_action( 'wp_ajax_tribe_events_' . static::$pluginSlug . '_get_possible_events', array( $this, 'ajaxGetPossibleEvents' ) );
			add_action( 'tribe_events_importexport_content_tab_' . static::$pluginSlug, array( $this, 'generateImportTab' ) );
			add_action( 'tribe_events_importexport_import_instructions_tab_' . static::$pluginSlug, array( $this, 'importTabInstructions' ) );
			add_action( 'tribe_events_importexport_import_form_tab_' . static::$pluginSlug, array( $this, 'doImportForm' ) );
			add_action( 'tribe_events_importexport_apikey_tab_' . static::$pluginSlug, array( $this, 'doApiKeyForm' ) );
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
			add_filter( 'tribe-post-origin', array( $this, 'addEventOrigin' ) );
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
			return static::$eventOrigin;
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
		 * Add import submenu page and set the callback function to the abstract.
		 * method doImportPage().
		 *
		 * @since 2.1
		 * @author PaulHughes01
		 *
		 * @return void
		 */
		public function addImportPage() {
			add_submenu_page( '/edit.php?post_type=' . TribeEvents::POSTTYPE, static::$importPageName, static::$importPageName, 'edit_posts', static::$pluginSlug, array( $this, 'doImportPage' ) );
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
		 * Displays a checkbox list of possible events to import.
		 *
		 * @since 2.1
		 * @author PaulHughes01
		 *
		 * @param array $eventsData An array of event titles, dates, and unique ids (specific to the importer).
		 * @return void
		 */
		protected function displayPossibleEventsList( $eventsData ) {
			if ( is_array( $eventsData ) ) {
				do_action( 'tribe_events_before_possible_import_list' );
				echo '<div id="tribe-events-possible-import-events-wrapper">';
				echo apply_filters( 'tribe-events-possible-import-form', '<form method="post">' );
				echo '<div id="tribe-events-possible-import-events-list-wrapper">';
				echo '<ul id="tribe-events-possible-import-events-list">';
				$this->buildPossibleEventsListItems( $eventsData );
				echo '</ul>';
				echo '</div>';
				echo '<div style="clear:left;">';
				echo '<input id="tribe-events-' . static::$pluginSlug . '-import-submit" name="tribe-events-' . static::$pluginSlug . '-import-submit" class="button-primary" type="submit" value="' . apply_filters( 'tribe_events_importer_import_events_button', __( 'Import Selected Events', self::$pluginSlug ) ) . '" />';
				echo '</div>';
				echo '</form>';
				echo '</div>';
				do_action( 'tribe_events_after_possible_import_list' );
			}
		}
		
		/**
		 * Builds and echoes each list item in the possible events list.
		 *
		 * @param array $eventsData
		 * @return null
		 */
		protected function buildPossibleEventsListItems( $eventsData ) {
			foreach ( $eventsData as $event ) {
				$sep = ' - ';
				if ( $event['endDate'] == '' )
					$sep = '';
				echo '<tr>';
				echo '<th scope="row" class="check-column"><input type="checkbox" name="tribe_events_events_to_import[]" value="' . $event['uid'] . '" /></th><td>' . $event['startDate'] . $sep . $event['endDate'] . '</td><td><strong>' . $event['title'] . '</strong><div>' . $event['venue'] . '</div></td><td />';
				echo '</tr>';
			}
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
			
			if ( !$possible_events ) {
				$errors = array(
					'error' => $this->errors,
				);
				echo json_encode( $errors );
				die();
			}
			
			$possible_events_list = $this->parseIntoEventsList( $possible_events );
			
			$this->buildPossibleEventsListItems( $possible_events_list );
			die();
		}
		
		/**
		 * Class method that is used to save a standardized events array (see the setEventsData() method).
		 *
		 * @since 2.1
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
		 * @since 2.1
		 * @author PaulHughes01
		 *
		 * @param array $eventArray The standardized event array containing meta information about the event to be saved.
		 * @return int|null The id of the saved event or null if the save failed.
		 */
		protected function saveEvent( $eventArray ) {
		
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
		
	}
	
}