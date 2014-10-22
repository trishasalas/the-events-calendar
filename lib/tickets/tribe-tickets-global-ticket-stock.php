<?php
if ( ! class_exists( 'TribeEventsGlobalTicketStock' ) ) {
	class TribeEventsGlobalTicketStock {

		protected $ticket;

		public function __construct( TribeEventsTicketObject $ticket ) {
			$this->ticket = $ticket;
		}

		public function set_stock( $value ) {
			// todo use is_numeric
			if ( ! is_int( $value ) ) {
				throw new Exception( 'Stock value must be an int' );
			}
			$event                                               = TribeEventsTickets::find_matching_event( $this->ticket->ID );
			$new_global_stocks                                   = $old_global_stocks = $this->get_global_stocks( $event );
			$new_global_stocks[ $this->ticket->global_stock_id ] = $value;
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
			$event         = TribeEventsTickets::find_matching_event( $this->ticket->ID );
			$global_stocks = $this->get_global_stocks( $event );
			if ( ! isset( $global_stocks[ $this->ticket->global_stock_id ] ) ) {
				$global_stocks[ $this->ticket->global_stock_id ] = 0;
				$this->update_event_stock_meta( $event, $global_stocks );
			}

			return $global_stocks[ $this->ticket->global_stock_id ];
		}

		protected function update_event_stock_meta( $event, array $global_stocks ) {
			$meta_key = TribeEventsTicketObject::GLOBAL_STOCKS_META;
			if ( '' == get_post_meta( $event->ID, $meta_key, true ) ) {
				$stock_meta_was_added = add_post_meta( $event->ID, $meta_key, $global_stocks, true );
				if ( ! $stock_meta_was_added ) {
					throw new Exception( 'Stock meta could not be added to event with ID ' . $event->ID );
				}
			} else {
				// todo use just this!
				update_post_meta( $event->ID, $meta_key, $global_stocks );
			}

		}
	}
}
