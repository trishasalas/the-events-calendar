<?php


	class TribeEventsTicketStockObjectTest extends \PHPUnit_Framework_TestCase {

		protected function setUp() {
		}

		protected function tearDown() {
		}

		/**
		 * @test
		 * it should default to local stock
		 */
		public function it_should_default_to_local_stock() {
			$sut = new TribeEventsTicketStockObject();

			$this->assertInstanceOf( 'TribeEventsTickets_Stock_LocalType', $sut->get_type() );
		}

		public function types_and_queries() {
			return array(
				array( new TribeEventsTickets_Stock_LocalType, true, false, false, false ),
				array( new TribeEventsTickets_Stock_GlobalType, false, true, false, false ),
				array( new TribeEventsTickets_Stock_GlobalLocalType, true, true, true, false ),
				array( new TribeEventsTickets_Stock_UnlimitedType, false, false, false, true ),
			);
		}

		/**
		 * @test
		 * it should allow querying for the stock type
		 * @dataProvider types_and_queries
		 */
		public function it_should_allow_querying_for_the_stock_type( $type, $is_local, $is_global, $is_glocal, $is_unlimited ) {
			$sut = new TribeEventsTicketStockObject();
			$sut->set_type( $type );

			$this->assertEquals( $is_local, $sut->is_local() );
			$this->assertEquals( $is_global, $sut->is_global() );
			$this->assertEquals( $is_glocal, $sut->is_global_and_local() );
			$this->assertEquals( $is_unlimited, $sut->is_unlimited() );
		}
	}