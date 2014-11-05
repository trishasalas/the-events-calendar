<?php


	class Tribe__Events__Tickets__Global_Stock {

		/**
		 * The ID of the global stock the ticket uses.
		 *
		 * @var string
		 */
		protected $global_stock_id;

		/**
		 * Whether a ticker will affect a global stock or not.
		 *
		 * @var bool
		 */
		protected $global_stock_use;

		/**
		 * An array of global_stock_id/values that stores the amount of tickets
		 * per global stock.
		 *
		 * @var array
		 */
		protected $event_global_stocks;

		/**
		 * The meta key the tickes uses to store the global stock option.
		 *
		 * The stored value will be read and written as truthy or falsy and should
		 * be used a toggle.
		 *
		 * @var string
		 */
		private $global_stock_use_key = '_ticket_global_stock_use';

		/**
		 * The meta key the ticket will use to store the id of the global
		 * stock it uses.
		 *
		 * This will default to "default" and should match one of the keys
		 * of the array stored under the "_global_ticket_stocks" meta key
		 * by the event.
		 *
		 * @var string
		 */
		private $global_stock_id_key = '_ticket_global_stock_id';

		/**
		 * The meta key an event will use to store the global stocks information.
		 *
		 * The meta value is an array in the format of key/value pairs like
		 *
		 * @var string the global stock name
		 * @var int the current amount of tickets the global stock contains
		 *      e.g. ['default': '', 'upper_balcony': 100, 'lower_balcony': 150 ]
		 *      Defaults to ['default': '']
		 *
		 * @var string
		 */
		private $event_global_stocks_key = '_global_ticket_stocks';

		/**
		 * @var TribeEventsTickets
		 */
		protected $tribe_events_tickets;

		/**
		 * @var int The ticke ID
		 */
		protected $ticket_id;

		/**
		 * @var int The ID of the event the ticket refers to
		 */
		protected $event_id;

		/**
		 * Returns a global stock information object built on the ticket.
		 *
		 * @param int              $ticket_id
		 * @param null|int|WP_Post $event_id
		 *
		 * @return null|Tribe__Events__Tickets__Global_Stock null if `$ticket_id` is not numeric, `$event_id` is not null or an int or a WP_Post or if any other issue arises; an instance of the class otherwise.
		 */
		public static function from_ticket( $ticket_id, $event_id = null ) {
			if ( ! is_numeric( $ticket_id ) ) {
				return null;
			}
			if ( ! ( is_null( $event_id ) || is_numeric( $event_id ) || is_a( $event_id, 'WP_Post' ) ) ) {
				return null;
			}
			$instance = new self();
			$instance->ticket_id = (int) $ticket_id;
			$instance->tribe_events_tickets = TribeEventsTickets::get_instance();
			if ( $event_id ) {
				$instance->event_id = is_a( $event_id, 'WP_Post' ) ? $event_id->ID : (int) $event_id;
			} else {
				$instance->event_id = $instance->tribe_events_tickets->get_event_for_ticket( $instance->ticket_id );
			}

			if ( ! $instance->event_id ) {
				return null;
			}

			$instance->init_from_meta();

			return $instance;
		}

		/**
		 * Checks if a ticket affects a global stock or not.
		 *
		 * @see get_post_meta
		 *
		 * @return bool
		 */
		public function uses_global_stock() {
			return $this->global_stock_use;
		}

		/**
		 * Gets the id of the global stock a ticket affects or should affect.
		 *
		 * Note that the ticket might NOT be affecting any global stock due to
		 * "_global_stock_use" meta key setting but this function will return the
		 * global stock id nonetheless.
		 *
		 * @return string
		 */
		public function get_global_stock_id() {
			return $this->global_stock_id;
		}

		/**
		 * Returns the value of the global stock the ticket affects.
		 *
		 * Please note that the value of the global stock will be returned
		 * no matter if the ticket affects a global stock or not.
		 *
		 * @return int|string Either the value of the global stock or the string
		 *                    that represents the unlimited stock if the global
		 *                    stock value is not set.
		 */
		public function get_global_stock_value() {
			if ( ! isset( $this->event_global_stocks[ $this->global_stock_id ] ) ) {
				return TribeEventsTicketObject::UNLIMITED_STOCK;
			}

			return $this->event_global_stocks[ $this->global_stock_id ];
		}

		/**
		 * Gets the ticket global stock use option and global stock value.
		 *
		 * @return array [global_stock_use, global_stock_value]
		 */
		public function get_global_stock_data() {
			return array( $this->global_stock_use, $this->get_global_stock_value() );
		}

		/**
		 * Saves a ticket global stock related information.
		 *
		 * @param array $formData
		 */
		public function save_global_stock_information( array $formData = array() ) {
			$use_key = 'ticket_global_stock_use';
			$value_key = 'ticket_global_stock_value';
			// might be a form input in the future
			$global_stock_id = $this->global_stock_id;
			$ticket_uses_global_stock = isset( $formData[ $use_key ] ) ? 1 : 0;
			$global_stock_value = ( isset( $formData[ $value_key ] ) && is_numeric( $formData[ $value_key ] ) ) ? $formData[ $value_key ] : TribeEventsTicketObject::UNLIMITED_STOCK;

			$this->event_global_stocks[ $global_stock_id ] = $global_stock_value;
			$this->global_stock_use = $ticket_uses_global_stock;
			// might be used to save the new global stock id in the future
			// $this->global_stock_id = $global_stock_id;

			$this->update_event_global_stocks_meta();
			$this->update_ticket_global_stocks_meta();
		}

		private function init_from_meta() {
			$this->global_stock_id = get_post_meta( $this->ticket_id, $this->global_stock_id_key, true );
			$this->global_stock_use = get_post_meta( $this->ticket_id, $this->global_stock_use_key, true );
			$this->event_global_stocks = get_post_meta( $this->event_id, $this->event_global_stocks_key, true );

			// will not use the global stock by default
			$this->global_stock_use = is_bool( $this->global_stock_use ) ? $this->global_stock_use : false;
			// will use the 'default' global stock by default
			$this->global_stock_id = $this->global_stock_id !== '' ? $this->global_stock_id : 'default';
			// event global stock information will be empty by default
			$this->event_global_stocks = is_array( $this->event_global_stocks ) ? $this->event_global_stocks : array();
		}

		protected function update_event_global_stocks_meta() {
			update_post_meta( $this->event_id, $this->event_global_stocks_key, $this->event_global_stocks );
		}

		protected function update_ticket_global_stocks_meta() {
			update_post_meta( $this->ticket_id, $this->global_stock_use_key, $this->global_stock_use );
			// there might be the option to set the ticket global stock meta in the future
			// update_post_meta( $this->ticket_id, $this->global_stock_id_key, $this->global_stock_id );
		}
	}