<?php

	if ( ! class_exists( 'TribeEventsTicketStockObject' ) ) {
		class TribeEventsTicketStockObject {

			protected $ticket;

			/** @var TribeEventsTicket_Stock_Type */
			protected $type;

			public function __construct( TribeEventsTicketObject $ticket = null, TribeEventsTicket_Stock_Type $type = null ) {
				$this->ticket = $ticket;
				$this->type = $type ? $type : new TribeEventsTickets_Stock_LocalType();
			}

			public function set_stock( $value ) {
				$this->set_stock( $this->type->set_stock( $value ) );
				if ( ! is_numeric( $value ) ) {
					throw new Exception( 'Stock value must be a number' );
				}
				$event = TribeEventsTickets::find_matching_event( $this->ticket->ID );
				$new_global_stocks = $old_global_stocks = $this->get_global_stocks( $event );
				$new_global_stocks[ $this->ticket->global_stock_id ] = (int) $value;
				update_post_meta( $event->ID, TribeEventsTicketObject::GLOBAL_STOCKS_META, $new_global_stocks, $old_global_stocks );
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
			protected function get_global_stocks( $event ) {
				if ( ! $event ) {
					throw new Exception( 'There was a problem retrieving the event for the ticket' );
				}
				$global_stocks = $event->{TribeEventsTicketObject::GLOBAL_STOCKS_META};
				if ( ! $global_stocks ) {
					$global_stocks = array();
					$this->update_event_stock_meta( $event, $global_stocks );
				}

				return $global_stocks;
			}

			public function get_stock() {
				$event = TribeEventsTickets::find_matching_event( $this->ticket->ID );
				$global_stocks = $this->get_global_stocks( $event );
				if ( ! isset( $global_stocks[ $this->ticket->global_stock_id ] ) ) {
					$global_stocks[ $this->ticket->global_stock_id ] = 0;
					$this->update_event_stock_meta( $event, $global_stocks );
				}

				return $global_stocks[ $this->ticket->global_stock_id ];
			}

			protected function update_event_stock_meta( $event, array $global_stocks ) {
				$meta_key = TribeEventsTicketObject::GLOBAL_STOCKS_META;
				update_post_meta( $event->ID, $meta_key, $global_stocks );
			}

			public function get_type() {
				return $this->type;
			}

			public function is_local() {
				return ( $this->type instanceof TribeEventsTickets_Stock_LocalType || $this->type instanceof TribeEventsTickets_Stock_GlobalLocalType );
			}

			public function is_global() {
				return ( $this->type instanceof TribeEventsTickets_Stock_GlobalType || $this->type instanceof TribeEventsTickets_Stock_GlobalLocalType );
			}

			public function is_global_and_local() {
				return $this->type instanceof TribeEventsTickets_Stock_GlobalLocalType;
			}

			public function is_unlimited() {
				return $this->type instanceof TribeEventsTickets_Stock_UnlimitedType;
			}

			public function set_type( TribeEventsTicket_Stock_Type $type ) {
				$this->type = $type;
			}
		}
	}

	if ( ! interface_exists( 'TribeEventsTicket_Stock_Type' ) ) {
		interface TribeEventsTicket_Stock_Type {

			public function use_global( $value );

			public function use_local( $value );
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

