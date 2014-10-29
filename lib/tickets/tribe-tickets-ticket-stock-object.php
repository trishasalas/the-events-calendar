<?php
	if ( ! class_exists( 'TribeEventsTicketStockObject' ) ) {
		class TribeEventsTicketStockObject {

			/**
			 * @var TribeEventsTicketObject
			 */
			protected $ticket;

			/** @var TribeEventsTicket_Stock_Type */
			public $type;

			/**
			 * @var string
			 */
			protected $global_stock_id;

			/**
			 * @var TribeEventsTickets_TicketMeta
			 */
			protected $ticket_meta;

			public static function from_ticket( TribeEventsTicketObject $ticket, TribeEventsTickets_TicketMeta $ticket_meta = null, TribeEventsTicket_Stock_Type $type = null ) {
				$instance = new self;
				$instance->ticket = $ticket;
				$instance->set_ticket_meta( $ticket_meta );
				$instance->type = $type ? $type : new TribeEventsTickets_Stock_LocalType();

				return $instance;
			}

			public function set_stock( $value ) {
				if ( $this->type->is_unlimited() ) {
					return;
				}

				$delta = $this->get_stock() - $value;

				if ( $this->type->is_local() || $this->type->is_global_and_local() ) {
					$new_local_qty = $this->ticket_meta->get_local_qty() - $delta;
					$this->ticket_meta->set_local_qty( $new_local_qty );
				}
				if ( $this->type->is_global_and_local() || $this->type->is_global() ) {
					$new_global_qty = $this->ticket_meta->get_global_qty( $this->global_stock_id ) - $delta;
					$this->ticket_meta->set_global_qty( $this->global_stock_id, $new_global_qty );
				}
			}

			public function get_local_qty() {
				return ( $this->type->is_local() || $this->type->is_global_and_local() ) ? $this->ticket_meta->get_local_qty() : false;
			}

			public function get_global_qty() {
				return ( $this->type->is_global() || $this->type->is_global_and_local() ) ? $this->ticket_meta->get_global_qty( $this->global_stock_id ) : false;
			}

			public function get_global_stock_id() {
				return $this->global_stock_id ? $this->global_stock_id : '';
			}

			public function update_from_meta() {
				if ( ! $this->ticket_meta ) {
					return;
				}

				$meta = $this->ticket_meta->get_meta();
				$this->global_stock_id = $meta['global_stock_id'];

				// reset the ticket type
				$this->use_local( false );
				$this->use_global( false );

				if ( isset( $meta['use_global'] ) ) {
					$this->use_global( (bool) $meta['use_global'] );
				}
				if ( isset( $meta['use_local'] ) ) {
					$this->use_local( (bool) $meta['use_local'] );
				}
			}

			/**
			 * Returns the global stocks array stored as an event meta.
			 *
			 * @param bool /WP_Post $event Either a `WP_Post` instance or `false`
			 *
			 * @return array The global stocks stored in the event meta.
			 *               The array will have the format `global_stock_id/value`:
			 *
			 *                  ['global_stock_1': 20, 'global_stock_2': 10]
			 *
			 * @throws Exception if the event object is false
			 */
//			protected function get_global_stocks( $event ) {
//				if ( ! $event ) {
//					throw new Exception( 'There was a problem retrieving the event for the ticket' );
//				}
//				$global_stocks = $event->{TribeEventsTicketObject::GLOBAL_STOCKS_META};
//				if ( ! $global_stocks ) {
//					$global_stocks = array();
//					$this->update_event_stock_meta( $event, $global_stocks );
//				}
//
//				return $global_stocks;
//			}

			public function get_stock() {
				if ( $this->type->is_local() ) {
					return $this->ticket_meta->get_local_qty();
				} else if ( $this->type->is_global() ) {
					return $this->ticket_meta->get_global_qty( $this->global_stock_id );
				} else if ( $this->type->is_global_and_local() ) {
					return min( $this->ticket_meta->get_local_qty(), $this->ticket_meta->get_global_qty( $this->global_stock_id ) );
				}

				return TribeEventsTicketObject::UNLIMITED_STOCK;
//				$event = TribeEventsTickets::find_matching_event( $this->ticket->ID );
//				$global_stocks = $this->get_global_stocks( $event );
//				if ( ! isset( $global_stocks[ $this->ticket->global_stock_id ] ) ) {
//					$global_stocks[ $this->ticket->global_stock_id ] = 0;
//					$this->update_event_stock_meta( $event, $global_stocks );
//				}

//				return $global_stocks[ $this->ticket->global_stock_id ];
			}

//			protected function update_event_stock_meta( $event, array $global_stocks ) {
//				$meta_key = TribeEventsTicketObject::GLOBAL_STOCKS_META;
//				update_post_meta( $event->ID, $meta_key, $global_stocks );
//			}

			protected function set_type( TribeEventsTicket_Stock_Type $type ) {
				$this->type = $type;
				if ( $this->type->is_global() || $this->type->is_global_and_local() ) {
					$this->ticket_meta->set_use_global( true );
				}
				if ( $this->type->is_local() || $this->type->is_global_and_local() ) {
					$this->ticket_meta->set_use_local( true );
				}
			}

			public function use_global( $value ) {
				$this->set_type( $this->type->use_global( $value ) );
			}

			public function use_local( $value ) {
				$this->set_type( $this->type->use_local( $value ) );
			}

			private function set_global_qty( $value ) {
				if ( $this->type->is_global() || $this->type->is_global_and_local() ) {
					$this->ticket_meta->set_global_qty( $this->global_stock_id, $value );
				}
			}

			/**
			 * @param TribeEventsTickets_TicketMeta $ticket_meta
			 */
			public function set_ticket_meta( TribeEventsTickets_TicketMeta $ticket_meta = null ) {
				$this->ticket_meta = $ticket_meta ? $ticket_meta : $this->ticket->get_ticket_meta_object();
			}

		}
	}
	if ( ! interface_exists( 'TribeEventsTicket_Stock_Type' ) ) {
		interface TribeEventsTicket_Stock_Type {

			public function use_global( $value );

			public function use_local( $value );

			public function is_local();

			public function is_global();

			public function is_global_and_local();

			public function is_unlimited();
		}
	}

	if ( ! class_exists( 'TribeEventsTickets_Stock_AbstractType' ) ) {
		abstract class TribeEventsTickets_Stock_AbstractType implements TribeEventsTicket_Stock_Type {

			public function use_global( $value ) {
				throw new Exception;
			}

			public function use_local( $value ) {
				throw new Exception;
			}

			public function is_local() {
				return ( $this instanceof TribeEventsTickets_Stock_LocalType );
			}

			public function is_global() {
				return ( $this instanceof TribeEventsTickets_Stock_GlobalType );
			}

			public function is_global_and_local() {
				return $this instanceof TribeEventsTickets_Stock_GlobalLocalType;
			}

			public function is_unlimited() {
				return $this instanceof TribeEventsTickets_Stock_UnlimitedType;
			}
		}
	}

	if ( ! class_exists( 'TribeEventsTickets_Stock_UnlimitedType' ) ) {
		class TribeEventsTickets_Stock_UnlimitedType extends TribeEventsTickets_Stock_AbstractType {

			public function use_global( $value ) {
				if ( $value ) {
					return new TribeEventsTickets_Stock_GlobalType();
				}

				return $this;
			}

			public function use_local( $value ) {
				if ( $value ) {
					return new TribeEventsTickets_Stock_LocalType();
				}

				return $this;
			}
		}
	}

	if ( ! class_exists( 'TribeEventsTickets_Stock_LocalType' ) ) {
		class TribeEventsTickets_Stock_LocalType extends TribeEventsTickets_Stock_AbstractType {

			public function use_global( $value ) {
				if ( $value ) {
					return new TribeEventsTickets_Stock_GlobalLocalType();
				}

				return $this;
			}

			public function use_local( $value ) {
				if ( $value ) {
					return $this;
				}

				return new TribeEventsTickets_Stock_UnlimitedType();
			}
		}
	}

	if ( ! class_exists( 'TribeEventsTickets_Stock_GlobalType' ) ) {
		class TribeEventsTickets_Stock_GlobalType extends TribeEventsTickets_Stock_AbstractType {

			public function use_global( $value ) {
				if ( $value ) {
					return $this;
				}

				return new TribeEventsTickets_Stock_UnlimitedType;
			}

			public function use_local( $value ) {
				if ( $value ) {
					return new TribeEventsTickets_Stock_GlobalLocalType();
				}

				return $this;
			}
		}
	}

	if ( ! class_exists( 'TribeEventsTickets_Stock_GlobalLocalType' ) ) {
		class TribeEventsTickets_Stock_GlobalLocalType extends TribeEventsTickets_Stock_AbstractType {

			public function use_global( $value ) {
				if ( $value ) {
					return $this;
				}

				return new TribeEventsTickets_Stock_LocalType();
			}

			public function use_local( $value ) {
				if ( $value ) {
					return $this;
				}

				return new TribeEventsTickets_Stock_GlobalLocalType;
			}
		}
	}

