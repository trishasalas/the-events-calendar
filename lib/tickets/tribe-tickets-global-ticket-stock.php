<?php
if ( ! class_exists( 'TribeEventsGlobalTicketStock' ) ) {
	class TribeEventsGlobalTicketStock {

		protected $ticket;

		public function __construct( TribeEventsTicketObject $ticket ) {
			$this->ticket = $ticket;
		}

		public function set_stock( $value ) {
			if ( ! is_int( $value ) ) {
				throw new Exception( 'Stock value must be an int' );
			}
			$event                                       = TribeEventsTickets::find_matching_event( $this->ticket->ID );
			$new_global_stocks                           = $old_global_stocks = $this->get_global_stocks( $event );
			$new_global_stocks[ $this->ticket->global_stock_id ] = $value;
			if ( '' == get_post_meta( $event->ID, TribeEventsTicketObject::GLOBAL_STOCKS_META, true ) ) {
				add_post_meta( $event->ID, TribeEventsTicketObject::GLOBAL_STOCKS_META, $new_global_stocks, true );
			} else {
				update_post_meta( $event->ID, Trct::GLOBAL_STOCKS_META, $new_global_stocks, $old_global_stocks );
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
		protected function get_global_stocks( $event ) {
			if ( ! $event ) {
				throw new Exception( 'There was a problem retrieving the event for the ticket' );
			}
			$stocks = $event->{TribeEventsTicketObject::GLOBAL_STOCKS_META};

			return $stocks;
		}

		public function get_stock() {
			$event         = TribeEventsTickets::find_matching_event( $this->ticket->ID );
			$global_stocks = $this->get_global_stocks( $event );

			return $global_stocks[ $this->ticket->global_stock_id ];
		}
	}
}
