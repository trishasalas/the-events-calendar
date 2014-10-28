<?php


	class TribeEventsTicketStockObjectTest extends \PHPUnit_Framework_TestCase {

		protected $sut;
		protected $mock_ticket_meta;
		protected $mock_ticket;

		protected function setUp() {
		}

		protected function tearDown() {
		}

		/**
		 * @test
		 * it should default to local stock
		 */
		public function it_should_default_to_unlimited_stock() {

			list( $mock_ticket, $mock_ticket_meta ) = $this->get_mock_ticket_and_meta();
			$sut = new TribeEventsTicketStockObject( $mock_ticket, $mock_ticket_meta );

			$this->assertInstanceOf( 'TribeEventsTickets_Stock_UnlimitedType', $sut->type );
		}

		/**
		 * @test
		 * it should default to unlimited local quantity
		 */
		public function it_should_default_to_unlimited_local_quantity() {
			list( $mock_ticket, $mock_ticket_meta ) = $this->get_mock_ticket_and_meta();
			$sut = new TribeEventsTicketStockObject( $mock_ticket, $mock_ticket_meta );

			$this->assertEquals( TribeEventsTicketObject::UNLIMITED_STOCK, $sut->get_local_qty() );
		}

		/**
		 * @return array
		 */
		protected function get_mock_ticket_and_meta() {
			$mock_ticket = $this->getMockBuilder( 'TribeEventsTicketObject' )->disableOriginalConstructor()->getMock();
			$mock_ticket_meta = $this->getMockBuilder( 'TribeEventsTicket_TicketMeta' )->disableOriginalConstructor()
			                         ->getMock();

			return array( $mock_ticket, $mock_ticket_meta );
		}

		/**
		 * @test
		 * it should default to not using the global stock
		 */
		public function it_should_default_to_not_using_the_global_stock() {
			list( $mock_ticket, $mock_ticket_meta ) = $this->get_mock_ticket_and_meta();
			$sut = new TribeEventsTicketStockObject( $mock_ticket, $mock_ticket_meta );

			$this->assertFalse( $sut->type->is_global() );
			$this->assertFalse( $sut->type->is_global_and_local() );
		}

		/**
		 * @test
		 * it should default to emtpy string global stock id
		 */
		public function it_should_default_to_emtpy_string_global_stock_id() {
			list( $mock_ticket, $mock_ticket_meta ) = $this->get_mock_ticket_and_meta();
			$sut = new TribeEventsTicketStockObject( $mock_ticket, $mock_ticket_meta );

			$this->assertEquals( '', $sut->get_global_stock_id() );
		}

		public function types_and_queries() {
			return array(
				array( new TribeEventsTickets_Stock_LocalType, true, false, false, false ),
				array( new TribeEventsTickets_Stock_GlobalType, false, true, false, false ),
				array( new TribeEventsTickets_Stock_GlobalLocalType, false, false, true, false ),
				array( new TribeEventsTickets_Stock_UnlimitedType, false, false, false, true ),
			);
		}

		/**
		 * @test
		 * it should allow querying for the stock type
		 * @dataProvider types_and_queries
		 */
		public function it_should_allow_querying_for_the_stock_type( $type, $is_local, $is_global, $is_glocal, $is_unlimited ) {
			list( $mock_ticket, $mock_ticket_meta ) = $this->get_mock_ticket_and_meta();
			$sut = new TribeEventsTicketStockObject( $mock_ticket, $mock_ticket_meta );
			$sut->type = $type;

			$this->assertEquals( $is_local, $sut->type->is_local() );
			$this->assertEquals( $is_global, $sut->type->is_global() );
			$this->assertEquals( $is_glocal, $sut->type->is_global_and_local() );
			$this->assertEquals( $is_unlimited, $sut->type->is_unlimited() );
		}

		/**
		 * @test
		 * it should set stock meta when set_stock_meta method is called
		 */
		public function it_should_set_stock_meta_when_set_stock_meta_method_is_called() {
			$mock_ticket = $this->getMockBuilder( 'TribeEventsTicketObject' )->disableOriginalConstructor()->getMock();
			$mock_ticket_meta = $this->getMockBuilder( 'TribeEventsTicket_TicketMeta' )
			                         ->setMethods( array( 'get_event_stock_meta' ) )->disableOriginalConstructor()
			                         ->getMock();
			$sut = new TribeEventsTicketStockObject( $mock_ticket, $mock_ticket_meta );

			$meta = array(
				'use_global'      => false,
				'use_local'       => true,
				'local_qty'       => 23,
				'global_stock_id' => 'default'
			);
			$sut->set_stock_meta( $meta );

			$this->assertTrue( $sut->type->is_local() );
			$this->assertFalse( $sut->type->is_global_and_local() );
			$this->assertFalse( $sut->type->is_global() );
			$this->assertFalse( $sut->type->is_unlimited() );
			$this->assertEquals( 23, $sut->get_local_qty() );
			$this->assertEquals( 23, $sut->get_stock() );
		}

		public function smaller_quantities() {
			return array(
				array( 23, 12, 12 ),
				array( 12, 12, 12 ),
				array( 12, 23, 12 ),
				array( 0, 12, 0 ),
				array( 0, 0, 0 ),
				array( 12, 0, 0 )
			);
		}

		/**
		 * @test
		 * it should return the min between local and global set if both set
		 * @dataProvider smaller_quantities
		 */
		public function it_should_return_the_min_between_local_and_global_set_if_both_set( $global_stock_qty, $local_stock_qty, $expected ) {
			$mock_ticket = $this->getMockBuilder( 'TribeEventsTicketObject' )->disableOriginalConstructor()->getMock();
			$mock_ticket_meta = $this->getMockBuilder( 'TribeEventsTicket_TicketMeta' )
			                         ->setMethods( array( 'get_event_stock_meta' ) )->disableOriginalConstructor()
			                         ->getMock();

			$sut = new TribeEventsTicketStockObject( $mock_ticket, $mock_ticket_meta );

			$event_stock_meta = array( 'default' => $global_stock_qty );
			$mock_ticket_meta->expects( $this->any() )->method( 'get_event_stock_meta' )
			                 ->will( $this->returnValue( $event_stock_meta ) );
			$meta = array(
				'use_global'      => true,
				'use_local'       => true,
				'local_qty'       => $local_stock_qty,
				'global_stock_id' => 'default'
			);
			$sut->set_stock_meta( $meta );

			$this->assertEquals( $local_stock_qty, $sut->get_local_qty() );
			$this->assertEquals( $global_stock_qty, $sut->get_global_qty() );
			$this->assertEquals( $expected, $sut->get_stock() );
		}
public function new_glocal_values(){
//	$global_qty, $local_qty, $new_value, $expected_global_qty, $expected_local_qty, $expected_stock
	return array(
		array(10, 5, 3, 8, 3, 3),
		array(10, 20, 1, 1, 11, 1),
		array(20, 20, 10, 10, 10, 10),
		array(20, 0, 5, 25, 5, 5),
		array(0, 10, 15, 15, 25, 15),
		array(0, 0, 7, 7, 7, 7),
	    array(20, 0, 0, 20, 0, 0)
	);
}
		/**
		 * @test
		 * it should properly set the global and the local stock is ticket is global and local
		 * @dataProvider new_glocal_values
		 */
		public function it_should_properly_set_the_global_and_the_local_stock_is_ticket_is_global_and_local( $global_qty, $local_qty, $new_value, $expected_global_qty, $expected_local_qty, $expected_stock ) {
			$mock_ticket = $this->getMockBuilder( 'TribeEventsTicketObject' )->disableOriginalConstructor()->getMock();
			$mock_ticket_meta = $this->getMockBuilder( 'TribeEventsTicket_TicketMeta' )
			                         ->setMethods( array( 'get_event_stock_meta' ) )->disableOriginalConstructor()
			                         ->getMock();

			$sut = new TribeEventsTicketStockObject( $mock_ticket, $mock_ticket_meta );

			$event_stock_meta = array( 'default' => $global_qty );
			$mock_ticket_meta->expects( $this->any() )->method( 'get_event_stock_meta' )
			                 ->will( $this->returnValue( $event_stock_meta ) );
			$meta = array(
				'use_global'      => true,
				'use_local'       => true,
				'local_qty'       => $local_qty,
				'global_stock_id' => 'default'
			);
			$sut->set_stock_meta( $meta );

			$sut->set_stock( $new_value );

			$this->assertEquals( $expected_local_qty, $sut->get_local_qty() );
			$this->assertEquals( $expected_global_qty, $sut->get_global_qty() );
			$this->assertEquals( $expected_stock, $sut->get_stock() );
		}
	}