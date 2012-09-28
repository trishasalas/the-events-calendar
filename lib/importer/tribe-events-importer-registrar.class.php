<?php
/**
 * Controls the registration of different importers.
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if (!class_exists('Tribe_Events_Importer_Registrar')) {
	class Tribe_Events_Importer_Registrar {
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
		protected static $slug;
		
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
		protected static function instance() {
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
			self::$menuPageTitle = apply_filters( 'tribe-events-importer-registrar-menu-page-title', __( 'Import / Export', 'tribe-events-calendar' ) );
			self::$slug = apply_filters( 'tribe-events-importer-registrar-slug', 'tribe-events-importer-registrar' );
			
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
		}
		
		/**
		 * Add filters.
		 *
		 * @since 2.1
		 * @return null
		 */
		protected function addFilters() {
		
		}
		
		/**
		 * Create the submenu item for import/export.
		 *
		 * @since 2.1
		 * @return null
		 */
		public function createMenuPage() {
			add_submenu_page( '/edit.php?post_type=' . TribeEvents::POSTTYPE, self::$menuPageTitle, self::$menuPageTitle, 'edit_posts', static::$slug, array( $this, 'doImportExportPage' ) );
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
			$this->currentTab = apply_filters( 'tribe_events_importexport_current_tab', ( isset( $_GET['tab'] ) && $_GET['tab'] ) ? esc_attr( $_GET['tab'] ) : $this->import_apis[0]['slug'] );
			
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
			if ( is_array( $this->export_apis ) && !empty( $this->export_apis ) ) {
				$tab = 'export';
				$name = apply_filters( 'tribe-events-importexport-export-tab-title', __( 'Export', 'tribe-events-calendar' )  );
				$class = ( $tab == $this->currentTab ) ? ' nav-tab-active' : '';
				echo '<a id="' . $tab . '" class="nav-tab' . $class . '" href="?post_type=' .TribeEvents::POSTTYPE . '&page=' . static::$slug . '&tab=' . urlencode( $tab ) . '">' . $name . '</a>';
				echo ' ' . __( 'Import:', 'tribe-events-calendar' ) . ' ';
			}
			if ( is_array( $this->import_apis ) && !empty( $this->import_apis ) ) {
				foreach ( $this->import_apis as $importer ) {
						$tab = esc_attr( $importer['slug'] );
						$name = esc_attr( $importer['name'] );
						$class = ( $tab == $this->currentTab ) ? ' nav-tab-active' : '';
						echo '<a id="' . $tab . '" class="nav-tab' . $class . '" href="?post_type=' .TribeEvents::POSTTYPE . '&page=' . static::$slug . '&tab=' . urlencode( $tab ) . '">' . $name . '</a>';
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
	}	
}