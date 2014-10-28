<?php

	if ( ! class_exists( 'TribeEventsTicketStockObject' ) ) {
		class TribeEventsTicketStockObject {

			/**
			 * @var TribeEventsTicketObject
			 */
			protected $ticket;

			/** @var TribeEventsTicket_Stock_Type */
			public    $type;
			protected $local_qty;
			protected $global_stock_id;
			protected $event_stock_meta;

			public function __construct( TribeEventsTicketObject $ticket = null, TribeEventsTicket_TicketMeta $ticket_meta = null, TribeEventsTicket_Stock_Type $type = null ) {
				$this->ticket = $ticket;
				$this->ticket_meta = $ticket_meta ? $ticket_meta : $this->ticket->get_ticket_meta_object();
				$this->type = $type ? $type : new TribeEventsTickets_Stock_UnlimitedType();
			}

			public function set_stock( $value ) {
				if ( $this->type->is_unlimited() ) {
					$this->use_local( true );
				}
				if ( $this->type->is_local() ) {
					$this->local_qty = $value;
				}
//				if ( ! is_numeric( value ) ) {
//					throw new Exception( 'Stock value must be a number' );
//				}
//				$event = TribeEventsTickets::find_matching_event( $this->ticket->ID );
//				$new_global_stocks = $old_global_stocks = $this->get_global_stocks( $event );
//				$new_global_stocks[ $this->ticket->global_stock_id ] = (int) $value;
//				update_post_meta( $event->ID, TribeEventsTicketObject::GLOBAL_STOCKS_META, $new_global_stocks, $old_global_stocks );
			}

			public function get_local_qty() {
				return ( $this->type->is_local() || $this->type->is_global_and_local() ) ? $this->local_qty : false;
			}

			public function get_global_qty() {
				return ( $this->type->is_global() || $this->type->is_global_and_local() ) ? $this->event_stock_meta[ $this->global_stock_id ] : false;
			}

			public function get_global_stock_id() {
				return $this->global_stock_id ? $this->global_stock_id : '';
			}

			public function set_stock_meta( $meta = null ) {
				if ( ! is_array( $meta ) ) {
					return;
				}
				if ( isset( $meta['use_global'] ) ) {
					$this->use_global( (bool) $meta['use_global'] );
				}
				if ( isset( $meta['use_local'] ) ) {
					$this->use_local( (bool) $meta['use_local'] );
				}
				if ( isset( $meta['local_qty'] ) && is_numeric( $meta['local_qty'] ) ) {
					$this->local_qty = (int) $meta['local_qty'];
				}
				if ( isset( $meta['global_stock_id'] ) && is_string( $meta['global_stock_id'] ) ) {
					$this->global_stock_id = $meta['global_stock_id'];
				}

				$this->event_stock_meta = $this->ticket_meta->get_event_stock_meta();
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
					return $this->local_qty;
				} else if ( $this->type->is_global() ) {
					return $this->get_global_qty();
				} else if ( $this->type->is_global_and_local() ) {
					return min( $this->local_qty, $this->get_global_qty() );
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
			}

			public function use_global( $value ) {
				$this->set_type( $this->type->use_global( $value ) );
			}

			public function use_local( $value ) {
				$this->set_type( $this->type->use_local( $value ) );
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

