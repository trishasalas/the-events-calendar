<?php

// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( ! class_exists( 'TribeAppShop' ) ) {

	/**
	 * Class that handles the integration with our Shop App API
	 */
	class TribeAppShop {

		/**
		 * Version of the data model
		 */
		const API_VERSION = "1.1";

		/**
		 * URL of the API
		 */
		const API_ENDPOINT = "http://tools/tribe-app-store/";

		/**
		 * Base name for the transients key
		 */
		const CACHE_KEY = "tribe-app-shop";

		/**
		 * Duration of the transients, in seconds.
		 */
		const CACHE_EXPIRATION = 1800; // 30mins

		/**
		 * Slug of the WP admin menu item
		 */
		const MENU_SLUG = "tribe-app-shop";

		/**
		 * Singleton instance
		 *
		 * @var null or TribeAppShop
		 */
		private static $instance = null;

		/**
		 * The slug for the new admin page
		 *
		 * @var string
		 */
		private $admin_page = null;

		/**
		 * Available components for the app store.
		 *
		 * @var array
		 */
		protected $data = array(
			'html' => '',
			'css'  => '',
			'js'   => ''
		);


		/**
		 * Class constructor
		 */
		public function __construct() {
			add_action( 'admin_menu', array( $this, 'add_menu_page' ), 100 );
			add_action( 'wp_before_admin_bar_render', array( $this, 'add_toolbar_item' ), 20 );
			add_action( 'current_screen', array( $this, 'setup_page' ) );
		}

		public function setup_page( $screen ) {
			if ( $screen->id !== $this->admin_page ) return;
			$this->get_data();
		}

		/**
		 * Adds the page to the admin menu
		 */
		public function add_menu_page() {
			$page_title = __( 'Event Add-Ons', 'tribe-events-calendar' );
			$menu_title = __( 'Event Add-Ons', 'tribe-events-calendar' );
			$capability = "edit_posts";

			$where = 'edit.php?post_type=' . TribeEvents::POSTTYPE;

			$this->admin_page = add_submenu_page(
				$where, $page_title, $menu_title, $capability, self::MENU_SLUG, array(
					$this,
					'do_menu_page'
				)
			);

			add_action( 'admin_print_styles-' . $this->admin_page, array( $this, 'enqueue' ) );
		}

		/**
		 * Adds a link to the shop app to the WP admin bar
		 */
		public function add_toolbar_item() {
			// prevent users who cannot manage the plugin to see addons link
			if ( current_user_can( 'edit_tribe_events' ) ) {
				global $wp_admin_bar;

				$where = 'edit.php?post_type=' . TribeEvents::POSTTYPE;

				$wp_admin_bar->add_menu(
							 array(
								 'id'     => 'tribe-events-app-shop',
								 'title'  => __( 'Event Add-Ons', 'tribe-events-calendar' ),
								 'href'   => admin_url( untrailingslashit( $where ) . "&page=" . self::MENU_SLUG ),
								 'parent' => 'tribe-events-settings-group'
							 )
				);
			}
		}

		/**
		 * Enqueue the styles and script
		 */
		public function enqueue() {
			wp_enqueue_style( 'app-shop', TribeEvents::instance()->pluginUrl . 'resources/app-shop.css', array(), apply_filters( 'tribe_events_css_version', TribeEvents::VERSION ) );
			wp_enqueue_script( 'app-shop', TribeEvents::instance()->pluginUrl . 'resources/app-shop.js', array(), apply_filters( 'tribe_events_js_version', TribeEvents::VERSION ) );

			$this->print_js();
			$this->print_css();
		}

		protected function print_js() {
			if ( empty( $this->data['js'] ) ) return;
			echo '<script type="text/javascript">';
			echo $this->data['js'];
			echo '</script>';
		}

		protected function print_css() {
			if ( empty( $this->data['css'] ) ) return;
			echo '<style>';
			echo $this->data['css'];
			echo '</style>';
		}

		/**
		 * Renders the Shop App page
		 */
		public function do_menu_page() {
			$html = $this->data['html'];
			include_once( TribeEvents::instance()->pluginPath . 'admin-views/app-shop.php' );
		}


		/**
		 * Get's all products from the API
		 *
		 * @return array|WP_Error
		 */
		private function get_data() {
			$data = get_transient( self::CACHE_KEY );

			if ( ! $data ) {
				$this->remote_get();
				if ( ! empty( $this->data['html'] ) )
					set_transient( self::CACHE_KEY, $this->data, self::CACHE_EXPIRATION );
			}

			if ( is_array( $data ) )
				$this->data = array_merge( $this->data, $data );
		}

		/**
		 * Makes the remote call to the API endpoint
		 *
		 * @param $action
		 */
		private function remote_get( $action = '' ) {
			$url = trailingslashit( self::API_ENDPOINT . self::API_VERSION ) . $action;
			$url = apply_filters( 'tribe_app_store_api', $url );

			$data = wp_remote_get( $url );

			if ( ! is_wp_error( $data ) && isset( $data['body'] ) ) {
				$this->data = array_merge( $this->data, json_decode( $data['body'], true ) );
			}
		}

		/**
		 * Static Singleton Factory Method
		 *
		 * @return TribeAppShop
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) ) {
				$className      = __CLASS__;
				self::$instance = new $className;
			}

			return self::$instance;
		}

	}

	TribeAppShop::instance();
}