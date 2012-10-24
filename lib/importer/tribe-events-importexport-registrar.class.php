<?php
/**
 * Controls the registration of different importers.
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if (!class_exists('Tribe_Events_ImportExport_Registrar')) {
	class Tribe_Events_ImportExport_Registrar {
		/**
		 * @static
		 * @var The instance of the class.
		 */
		protected static $instance;
		
		/**
		 * @static
		 * @var The Menu Page title.
		 */
		protected static $menuPageTitle;
		
		/**
		 * @static
		 * @var The import registrar slug.
		 */
		public static $slug;
		
		/**
		 * @var The importers.
		 */
		protected $import_apis;

		/**
		 * @var The exporters.
		 */
		protected $export_apis;

		/**
		 * Create the plugin instance and include the other class.
		 *
		 * @since 2.1
		 * @return void
		 */
		public static function init() {
			if ( !isset( self::$instance ) ) {
				self::$instance = self::instance();
				require_once( 'tribe-events-importer.class.php' );
			}
		}
		
		/**
		 * The singleton function.
		 *
		 * @since 2.1
		 * @return Tribe_Events_Importer_Registrar The instance.
		 */
		public static function instance() {
			if ( !is_a( self::$instance, __CLASS__ ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}
	
		/**
		 * The class constructor.
		 *
		 * @since 2.1
		 * @return null
		 */
		protected function __construct() {
			self::$menuPageTitle = apply_filters( 'tribe-events-importexport-registrar-menu-page-title', __( 'Import / Export', 'tribe-events-calendar' ) );
			self::$slug = apply_filters( 'tribe-events-importexport-registrar-slug', 'tribe-events-importexport' );
			$this->currentTab = apply_filters( 'tribe_events_importexport_current_tab', ( isset( $_GET['tab'] ) && $_GET['tab'] ) ? esc_attr( $_GET['tab'] ) : 'general' );
			$this->errors = array();
			$this->messages = array();
			$this->options = array();
			
			$this->addActions();
			$this->addFilters();
		}
		
		/**
		 * Add actions.
		 *
		 * @since 2.1
		 * @return null
		 */
		protected function addActions() {
			add_action( 'admin_menu', array( $this, 'createMenuPage' ) );
			add_action( 'tribe_events_importexport_content_tab_export', array( $this, 'addExportTab' ) );
			add_action( 'tribe_events_importexport_content_tab_general', array( $this, 'doGeneralTab' ) );
			add_action( 'admin_head', array( $this, 'processGeneralSettings' ) );
			add_action( 'admin_notices', array( $this, 'displayMessages' ) );
			add_action( 'admin_notices', array( $this, 'displayErrors' ) );
		}
		
		/**
		 * Add filters.
		 *
		 * @since 2.1
		 * @return null
		 */
		protected function addFilters() {
			add_filter( 'cron_schedules', array( $this, 'addCronIntervals' ) );
		}
		
		/**
		 * Display notification messages.
		 *
		 * @since 3.0
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
		 * Display notification messages.
		 *
		 * @since 3.0
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
		 * Get an import/export option.
		 *
		 * @since 3.1
		 * @author PaulHughes01
		 *
		 * @return mixed The option.
		 */
		public function getOption( $option_name ) {
			if ( empty( $this->options ) ) {
				$defaults = array(
					'imported_post_status' => 'publish',
				);
				$current_options = get_option( 'tribe-events-importexport-general-settings', array() );
				$this->options = wp_parse_args( $current_options, $defaults );
			}
			if ( isset( $this->options[$option_name] ) ) {
				return $this->options[$option_name];
			} else {
				return false;
			}
		}
		
		/**
		 * Create the submenu item for import/export.
		 *
		 * @since 2.1
		 * @return null
		 */
		public function createMenuPage() {
			add_submenu_page( '/edit.php?post_type=' . TribeEvents::POSTTYPE, self::$menuPageTitle, self::$menuPageTitle, 'edit_posts', self::$slug, array( $this, 'doImportExportPage' ) );
		}
		
		/**
		 * Draw the import submenu page.
		 *
		 * @author PaulHughes01
		 * @since 2.1
		 * @return null
		 */
		public function doImportExportPage() {
			$this->export_apis = apply_filters( 'tribe-events-importexport-export-apis', array() );
			$this->import_apis = apply_filters( 'tribe-events-importexport-import-apis', array() );
			
			do_action( 'tribe_events_importexport_top' );
			echo '<div class="tribe_events_importexport wrap">';
				screen_icon();
				echo '<h2>';
					printf( self::$menuPageTitle );
				echo '</h2>';
				do_action( 'tribe_events_importexport_above_tabs' );
				$this->generateTabs( $this->currentTab );
				do_action( 'tribe_events_importexport_below_tabs' );
				do_action( 'tribe_events_importexport_below_tabs_tab_'.$this->currentTab );
				echo '<div class="tribe-events_importexport-content">';
				do_action( 'tribe_events_importexport_before_content' );
				do_action( 'tribe_events_importexport_before_content_tab_'.$this->currentTab );
				do_action( 'tribe_events_importexport_content_tab_'.$this->currentTab );
				if ( !has_action( 'tribe_events_importexport_content_tab_'.$this->currentTab ) ) {
					echo '<p>' . __( "You've requested a non-existent tab.", 'tribe-events-calendar' ) . '</p>';
				}
				do_action( 'tribe_events_importexport_after_content_tab_'.$this->currentTab );
				do_action( 'tribe_events_importexport_after_content' );
				echo '</div>';
				do_action( 'tribe_events_importexport_after_form_div' );
			echo '</div>';
			do_action( 'tribe_events_importexport_bottom' );
		}
		
		/**
		 * Generate the Import / Export Tabs
		 *
		 * @since 2.1
		 * @return null
		 */
		protected function generateTabs() {
			echo '<h2 id="tribe-events-importexport-tabs" class="nav-tab-wrapper">';
			$tab = 'general';
			$name = apply_filters( 'tribe-events-importexport-general-tab-title', __( 'General', 'tribe-events-calendar' ) );
			$class = ( $tab == $this->currentTab ) ? ' nav-tab-active' : '';
			echo '<a id="' . $tab . '" class="nav-tab' . $class . '" href="?post_type=' .TribeEvents::POSTTYPE . '&page=' . self::$slug . '&tab=' . urlencode( $tab ) . '">' . $name . '</a>';
			if ( is_array( $this->export_apis ) && !empty( $this->export_apis ) ) {
				$tab = 'export';
				$name = apply_filters( 'tribe-events-importexport-export-tab-title', __( 'Export', 'tribe-events-calendar' ) );
				$class = ( $tab == $this->currentTab ) ? ' nav-tab-active' : '';
				echo '<a id="' . $tab . '" class="nav-tab' . $class . '" href="?post_type=' .TribeEvents::POSTTYPE . '&page=' . self::$slug . '&tab=' . urlencode( $tab ) . '">' . $name . '</a>';
			}
			if ( is_array( $this->import_apis ) && !empty( $this->import_apis ) ) {
			echo ' ' . __( 'Import:', 'tribe-events-calendar' ) . ' ';
				foreach ( $this->import_apis as $importer ) {
						$tab = esc_attr( $importer['slug'] );
						$name = esc_attr( $importer['name'] );
						$class = ( $tab == $this->currentTab ) ? ' nav-tab-active' : '';
						echo '<a id="' . $tab . '" class="nav-tab' . $class . '" href="?post_type=' .TribeEvents::POSTTYPE . '&page=' . self::$slug . '&tab=' . urlencode( $tab ) . '">' . $name . '</a>';
					}
					do_action( 'tribe_events_importexport_after_tabs' );
				echo '</h2>';
			}
		}
		
		/**
		 * Add the export tab, if there are any exporters registered.
		 *
		 * @author PaulHughes01
		 * @since 2.1
		 *
		 * @return null
		 */
		public function addExportTab() {
			
		}
		
		/**
		 * Add the general tab.
		 *
		 * @author PaulHughes01
		 * @since 3.0
		 *
		 * @return null
		 */
		public function doGeneralTab() {
			$tec = TribeEvents::instance();
			require_once( $tec->pluginPath . 'admin-views/tribe-importexport-general.php' );
		}
		
		/**
		 * Process the submission of the general settings tab.
		 *
		 * @author PaulHughes01
		 * @since 3.0
		 *
		 * @return null
		 */
		public function processGeneralSettings() {
			if ( isset( $_POST['tribe-events-importexport-general-settings-submit'] ) && isset( $_POST['tribe-events-importexport-general-settings-nonce'] ) && wp_verify_nonce( $_POST['tribe-events-importexport-general-settings-nonce'], 'tribe-events-importexport-general-settings-nonce-submit' ) ) {
				do_action( 'tribe_events_save_apikeys' );
				$current_settings = get_option( 'tribe-events-importexport-general-settings', array() );
				
				if ( isset( $_POST['imported-post-status'] ) ) {
					$current_settings['imported_post_status'] = $_POST['imported-post-status'];
				}
				
				$success = update_option( 'tribe-events-importexport-general-settings', $current_settings );
				
				if ( $success )
					$this->messages[] = 'Import / Export settings successfully saved.';
				else
					$this->errors[] = 'No settings changed.';
			}
		}
		
		/**
		 * Add weekly and monthly cron schedules.
		 *
		 * @author PaulHughes01
		 * @since 2.1
		 *
		 * @return null
		 */
		public function addCronIntervals() {
			$schedules['weekly'] = array(
				'interval' => 604800,
				'display' => __( 'Once Weekly', 'tribe-events-calendar' )
			);
			$schedules['monthly'] = array(
				'interval' => 2635200,
				'display' => __( 'Once Monthly', 'tribe-events-calendar' )
			);
			return $schedules;
		}
	}	
}