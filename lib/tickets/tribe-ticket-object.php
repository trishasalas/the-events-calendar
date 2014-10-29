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
			protected $ticket_meta_object;

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

			final public function __construct( TribeEventsTickets_TicketMeta $meta_object = null, TribeEventsTicketStockObject $stockObject = null ) {
				$this->ticket_meta_object = $meta_object ? $meta_object : TribeEventsTickets_TicketMeta::from_ticket( $this );
				$this->stock_object = $stockObject ? $stockObject : TribeEventsTicketStockObject::from_ticket( $this );
			}

			public function __set( $property, $value ) {
				if ( $property == 'stock' ) {
					$this->stock_object->set_stock( $value );

					return;
				} else if ( $property == 'ID' ) {
					$this->ID = $value;
					$this->ticket_meta_object->fetch_meta();
					$this->stock_object->update_from_meta();
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
				$this->ticket_meta_object = $meta_object;
			}

			public function get_event_stock_meta() {
				return $this->ticket_meta_object->get_event_stock_meta();
			}

			public function get_ticket_meta_object() {
				return $this->ticket_meta_object;
			}

			public function has_global_stock() {
				$has_local_stock = $this->stock_object->type->is_global() || $this->stock_object->type->is_global_and_local();

				return $has_local_stock;
			}

			public function has_local_stock() {
				$has_global_stock = $this->stock_object->type->is_local() || $this->stock_object->type->is_global_and_local();

				return $has_global_stock;
			}

			public function has_unlimited_stock() {
				return $this->stock_object->type->is_unlimited();
			}

			public function use_global_stock( $value ) {
				$this->stock_object->use_global( $value );
			}

			public function use_local_stock( $value ) {
				$this->stock_object->use_local( $value );
			}

		}
	}

	if ( ! class_exists( 'TribeEventsTickets_TicketMeta' ) ) {
		class TribeEventsTickets_TicketMeta {

			protected $ticket;

			protected $event;

			protected $meta_key = 'tribe_tickets_ticket_meta';
			protected $meta;
			protected $event_meta;

			protected $stock_meta_subkey = 'stock_meta';

			public static function from_ticket( TribeEventsTicketObject $ticket ) {
				$instance = new self();
				$instance->ticket = $ticket;

				return $instance;
			}

			public function get_stock_meta( $key = null ) {
				$stock_meta = $this->get_meta( $this->stock_meta_subkey );
				if ( is_string( $key ) ) {
					if ( isset( $stock_meta[ $key ] ) ) {
						return $stock_meta[ $key ];
					}

					return '';
				}

				return $stock_meta;
			}

			public function get_use_local() {
				return $this->get_stock_meta( 'use_local' );
			}

			public function set_use_local( $value = null ) {
				$this->meta[ $this->stock_meta_subkey ]['use_local'] = (bool) $value;
				$this->update_ticket_meta();
			}

			public function get_use_global() {
				return $this->get_stock_meta( 'use_global' );
			}

			public function set_use_global( $value = null ) {
				$this->meta[ $this->stock_meta_subkey ]['use_global'] = (bool) $value;
				$this->update_ticket_meta();
			}

			public function get_local_qty() {
				return $this->get_stock_meta( 'local_qty' );
			}

			public function set_local_qty( $value ) {
				$this->meta[ $this->stock_meta_subkey ]['local_qty'] = (int) $value;
				$this->update_ticket_meta();
			}

			public function get_global_stock_id() {
				return $this->get_stock_meta( 'global_stock_id' );
			}

			public function set_global_stock_id( $global_stock_id ) {
				$this->meta[ $this->stock_meta_subkey ]['global_stock_id'] = '' . $global_stock_id;
				$this->update_ticket_meta();
			}

			public function get_global_qty( $stock_id ) {
				return $this->get_event_stock_meta( $stock_id );
			}

			public function set_global_qty( $stock_id, $value ) {
				$this->event_meta[ (string) $stock_id ] = (int) $value;
				$this->update_event_meta();
			}

			public function get_meta( $key = null ) {
				$this->fetch_meta();

				if ( is_string( $key ) ) {
					if ( isset( $this->meta[ $key ] ) ) {
						return $this->meta[ $key ];
					}

					return '';
				}

				return $this->meta;
			}

			public static function get_meta_defaults() {
				return array(
					'stock_meta' => array(
						'use_global' => false, 'use_local' => true,
						'local_qty' => TribeEventsTicketObject::UNLIMITED_STOCK, 'global_stock_id' => 'default'
					)
				);
			}

			public function get_event_stock_meta( $key = null ) {
				if ( ! $this->event_meta ) {
					$this->event_meta = self::get_event_stock_meta_defaults();
				}
				if ( ! $this->ticket->ID ) {
					return $this->event_meta;
				}
				$event = TribeEventsTickets::find_matching_event( $this->ticket->ID );
				if ( $event ) {
					$this->event = $event;
					$event_meta = get_post_meta( $event->ID, TribeEventsTicketObject::GLOBAL_STOCKS_META, true );
					$this->event_meta = wp_parse_args( $this->event_meta, $event_meta );
				}

				if ( is_string( $key ) ) {
					if ( isset( $this->event_meta[ $key ] ) ) {
						return $this->event_meta[ $key ];
					}

					return '';
				}

				return $this->event_meta;
			}

			public static function get_event_stock_meta_defaults() {
				return array(
					'default' => TribeEventsTicketObject::UNLIMITED_STOCK
				);
			}

			protected function update_ticket_meta() {
				if ( ! $this->ticket->ID ) {
					return;
				}

				update_post_meta( $this->ticket->ID, $this->meta_key, $this->meta );
			}

			private function update_event_meta() {
				if ( ! $this->event ) {
					return;
				}

				udpate_post_meta( $this->event->ID, TribeEventsTicketObject::GLOBAL_STOCKS_META, $this->event_meta );
			}

			public function fetch_meta() {
				if ( ! $this->meta ) {
					$this->meta = self::get_meta_defaults();
				}
				if ( $this->ticket->ID && $ticket_meta = get_post_meta( $this->ticket->ID, $this->meta_key, true ) ) {
					$this->meta = wp_parse_args( $this->meta, $ticket_meta );
				}
			}
		}
	}
