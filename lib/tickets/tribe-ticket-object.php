<?php
if ( ! class_exists( 'TribeEventsTicketObject' ) ) {
	/**
	 *    Generic object to hold information about a single ticket
	 */
	class TribeEventsTicketObject {

		/**
		 * This value - an empty string - should be used to populate the stock
		 * property in situations where no limit has been placed on stock
		 * levels.
		 */
		const UNLIMITED_STOCK = '';

		/**
		 * Unique identifier
		 *
		 * @var
		 */
		public $ID;
		/**
		 * Name of the ticket
		 *
		 * @var string
		 */
		public $name;

		/**
		 * Free text with a description of the ticket
		 *
		 * @var string
		 */
		public $description;

		/**
		 * Price, without any sign. Just a float.
		 *
		 * @var float
		 */
		public $price;

		/**
		 * Link to the admin edit screen for this ticket in the provider system,
		 * or null if the provider doesn't have any way to edit the ticket.
		 *
		 * @var string
		 */
		public $admin_link;

		/**
		 * Link to the front end of this ticket, if the providers has single view
		 * for this ticket.
		 *
		 * @var string
		 */
		public $frontend_link;

		/**
		 * Class name of the provider handling this ticket
		 *
		 * @var
		 */
		public $provider_class;

		/**
		 * Amount of tickets of this kind in stock
		 *
		 * This property is used if the ticket is a ticket not affecting
		 * a global stock.
		 *
		 * @var mixed
		 */
		protected  $stock;

		/**
		 * Global stock affected by the ticket sales.
		 *
		 * Will be `false` if the ticket does not affect any global stock,
		 * will be a string defining the name of the global stock affected
		 * otherwise.
		 *
		 * @var bool
		 */
		public $global_stock = false;

		/**
		 * Amount of tickets of this kind sold
		 *
		 * @var int
		 */
		public $qty_sold;

		/**
		 * Number of tickets for which an order has been placed but not confirmed or "completed".
		 *
		 * @var int
		 */
		public $qty_pending = 0;

		/**
		 * When the ticket should be put on sale
		 *
		 * @var
		 */
		public $start_date;

		/**
		 * When the ticket should be stop being sold
		 *
		 * @var
		 */
		public $end_date;

		public function __set( $property, $value ) {
			if ( $property == 'stock' && $this->global_stock ) {
				$global_stock = $this->get_event_global_stock();
				if ( !$global_stock  ) {
					// TODO: throw? Return? What?
					return;
				}

			}
			$this->$property = $value;
		}

		public function __get( $property ) {
			if ( $property == 'stock' && $this->global_stock ) {
				$global_stock = $this->get_event_global_stock();
				if ( !$global_stock  ) {
					// TODO: throw? Return? What?
					return;
				}
			}

			return $this->$property;
		}

		/**
		 * Fetches the
		 */
		protected function get_event_global_stock() {
			if ( ! $event = TribeEventsTickets::find_matching_event( $this->ID ) ) {
				return false;
			}
			return $event->get_stock_object_by_name( $this->global_stock );
		}
	}
}