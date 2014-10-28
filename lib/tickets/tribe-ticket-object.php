<?php
	if ( ! class_exists( 'TribeEventsTicketObject' ) ) {
		/**
		 *    Generic object to hold information about a single ticket
		 */
		class TribeEventsTicketObject {

			const GLOBAL_STOCKS_META  = 'tribe_events_global_stocks';
			const GLOBAL_STOCK_ENABLE = 'tribe_events_global_stocks_enable';
			protected $stock_attributes_meta_key = 'tribe_events_ticket_stock_attributes';

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
			protected $ID;
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
			 * @var TribeEventsTicketStockObject
			 */
			protected $stock_object;

			/**
			 * @var TribeEventsTickets_TicketMeta
			 */
			protected $meta_object;

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

			final public function __construct( TribeEventsTickets_TicketMeta $meta_object = null, TribeEventsTicketsStockObject $stockObject = null ) {
				$this->meta_object = $meta_object ? $meta_object : new TribeEventsTickets_TicketMeta( $this );
				$this->stock_object = $stockObject ? $stockObject : new TribeEventsTicketStockObject( $this );
			}

			public function __set( $property, $value ) {
				if ( $property == 'stock' ) {
					$this->stock_object->set_stock( $value );

					return;
				} else if ( $property == 'ID' ) {
					$meta = $this->meta_object->get_meta();
					$this->stock_object->set_stock_meta( $meta['stock_meta'] );
				}

				$this->$property = $value;
			}


			public function __get( $property ) {
				if ( $property == 'stock' ) {
					return $this->stock_object->get_stock();
				}

				return $this->$property;
			}


			/**
			 * Checks if the use of global stock has been enabled on a filter
			 * level.
			 *
			 * @return bool `true` if the filter return value evaluates to true, `false` otherwise.
			 */
			public function is_global_stock_enabled() {

				/**
				 * Enables or disables the use of global stock.
				 *
				 * Filter will return `null` disabling the function by
				 * default. To activate the function hook into the filter
				 * and return a value evaluating to boolean `true`.
				 *
				 * @since 1.5
				 *
				 * @param TribeEventsTicketObject $this The current ticket instance.
				 */
				$global_stock_enabled = apply_filters( self::GLOBAL_STOCK_ENABLE, $this );

				return $global_stock_enabled ? true : false;
			}

			public function get_stock_object() {
				return $this->stock_object;
			}

			public function set_stock_object( TribeEventsTicketStockObject $stock_object = null ) {
				$this->stock_object = $stock_object;
			}

			public function set_meta_object( TribeEventsTickets_TicketMeta $meta_object ) {
				$this->meta_object = $meta_object;
			}

			public function get_event_stock_meta() {
				return $this->meta_object->get_event_stock_meta();
			}

			public function get_ticket_meta_object() {
				return $this->meta_object;
			}

		}
	}

	if ( ! class_exists( 'TribeEventsTickets_TicketMeta' ) ) {
		class TribeEventsTickets_TicketMeta {

			protected $ticket;

			public function __construct( TribeEventsTicketObject $ticket ) {
				$this->ticket = $ticket;
			}

			public function get_meta() {
				if ( ! $this->ticket->ID ) {
					return array();
				}
				if ( $meta = get_post_meta( $this->ticket->ID, 'tribe_tickets_ticket_meta', true ) ) {
					return wp_parse_args( self::get_meta_defaults(), $meta );
				}

				return array( self::get_meta_defaults() );
			}

			public static function get_meta_defaults() {
				return array(
					'stock_meta' => array(
						'use_global'      => false,
						'use_local'       => false,
						'local_qty'       => TribeEventsTicketObject::UNLIMITED_STOCK,
						'global_stock_id' => 'default'
					)
				);
			}

			public function get_event_stock_meta() {
				$event = TribeEventsTickets::find_matching_event( $this->ticket->ID );
				$meta = get_post_meta( $event->ID, TribeEventsTicketObject::GLOBAL_STOCKS_META, true );

				return wp_parse_args( self::get_event_stock_meta_defaults(), $meta );
			}

			public static function get_event_stock_meta_defaults() {
				return array(
					'default' => TribeEventsTicketObject::UNLIMITED_STOCK
				);
			}
		}
	}
