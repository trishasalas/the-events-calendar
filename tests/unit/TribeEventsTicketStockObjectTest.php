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
		public function it_should_default_to_local_stock() {

			list( $mock_ticket, $mock_ticket_meta ) = $this->get_mock_ticket_and_meta();
			$sut = TribeEventsTicketStockObject::from_ticket( $mock_ticket, $mock_ticket_meta );

			$this->assertInstanceOf( 'TribeEventsTickets_Stock_LocalType', $sut->type );
		}

		/**
		 * @test
		 * it should default to unlimited local quantity
		 */
		public function it_should_default_to_unlimited_local_quantity() {
			list( $mock_ticket, $mock_ticket_meta ) = $this->get_mock_ticket_and_meta();
			$sut = TribeEventsTicketStockObject::from_ticket( $mock_ticket, $mock_ticket_meta );

			$this->assertEquals( TribeEventsTicketObject::UNLIMITED_STOCK, $sut->get_local_qty() );
		}

		/**
		 * @return array
		 */
		protected function get_mock_ticket_and_meta() {
			$mock_ticket = $this->getMock( 'TribeEventsTicketObject' );
			$mock_ticket_meta = $this->getMock( 'TribeEventsTickets_TicketMeta' );

			return array( $mock_ticket, $mock_ticket_meta );
		}

		/**
		 * @test
		 * it should default to not using the global stock
		 */
		public function it_should_default_to_not_using_the_global_stock() {
			list( $mock_ticket, $mock_ticket_meta ) = $this->get_mock_ticket_and_meta();
			$sut = TribeEventsTicketStockObject::from_ticket( $mock_ticket, $mock_ticket_meta );

			$this->assertFalse( $sut->type->is_global() );
			$this->assertFalse( $sut->type->is_global_and_local() );
		}

		/**
		 * @test
		 * it should default to emtpy string global stock id
		 */
		public function it_should_default_to_emtpy_string_global_stock_id() {
			list( $mock_ticket, $mock_ticket_meta ) = $this->get_mock_ticket_and_meta();
			$sut = TribeEventsTicketStockObject::from_ticket( $mock_ticket, $mock_ticket_meta );

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

		public function meta_provider() {
			// use global, use local, local qty, global stock id, global qty, expected local qty, expected global quantity, expected stock
			return array(
				array(
					false, false, 12, 'default', 100, TribeEventsTicketObject::UNLIMITED_STOCK, false,
					TribeEventsTicketObject::UNLIMITED_STOCK
				), array(
					false, true, 12, 'default', 100, 12, false, 12
				), array(
					true, true, 12, 'default', 100, 12, 100, 12
				), array(
					true, false, 12, 'default', 100, false, 100, 100
				),
			);
		}

		/**
		 * @test
		 * it should init with meta when update from meta method is called
		 * @dataProvider meta_provider
		 */
		public function it_should_init_with_meta_when_update_from_meta_method_is_called( $use_global, $use_local, $local_qty, $global_stock_id, $global_qty, $exp_local_qty, $exp_global_qty, $exp_stock ) {
			list( $mock_ticket, $mock_ticket_meta ) = $this->get_mock_ticket_and_meta();
			$meta = array(
				'use_global' => $use_global, 'use_local' => $use_local, 'local_qty' => $local_qty,
				'global_stock_id' => $global_stock_id
			);
			$mock_ticket_meta->expects( $this->any() )->method( 'get_meta' )->will( $this->returnValue( $meta ) );
			$mock_ticket_meta->expects( $this->any() )->method( 'get_local_qty' )
			                 ->will( $this->returnValue( $local_qty ) );
			$mock_ticket_meta->expects( $this->any() )->method( 'get_global_qty' )
			                 ->will( $this->returnValue( $global_qty ) );
			$sut = TribeEventsTicketStockObject::from_ticket( $mock_ticket, $mock_ticket_meta );

			$sut->update_from_meta();

			$this->assertEquals( $use_local && ! $use_global, $sut->type->is_local() );
			$this->assertEquals( $use_global && $use_local, $sut->type->is_global_and_local() );
			$this->assertEquals( $use_global && ! $use_local, $sut->type->is_global() );
			$this->assertEquals( ! ( $use_local || $use_global ), $sut->type->is_unlimited() );
			$this->assertEquals( $exp_local_qty, $sut->get_local_qty() );
			$this->assertEquals( $exp_global_qty, $sut->get_global_qty() );
			$this->assertEquals( $exp_stock, $sut->get_stock() );
		}

		public function smaller_quantities() {
			return array(
				array( 23, 12, 12 ), array( 12, 12, 12 ), array( 12, 23, 12 ), array( 0, 12, 0 ), array( 0, 0, 0 ),
				array( 12, 0, 0 )
			);
		}

		/**
		 * @test
		 * it should return the min between local and global set if both set
		 * @dataProvider smaller_quantities
		 */
		public function it_should_return_the_min_between_local_and_global_set_if_both_set( $global_qty, $local_qty, $expected_stock ) {
			$mock_ticket = $this->getMock( 'TribeEventsTicketObject' );
			$mock_ticket_meta = $this->getMock( 'TribeEventsTickets_TicketMeta' );
			$meta = array(
				'use_global' => true, 'use_local' => true, 'local_qty' => $local_qty, 'global_stock_id' => 'default'
			);
			$mock_ticket_meta->expects( $this->any() )->method( 'get_meta' )->will( $this->returnValue( $meta ) );
			$mock_ticket_meta->expects( $this->any() )->method( 'get_local_qty' )
			                 ->will( $this->returnValue( $local_qty ) );
			$mock_ticket_meta->expects( $this->any() )->method( 'get_global_qty' )
			                 ->will( $this->returnValue( $global_qty ) );

			$sut = TribeEventsTicketStockObject::from_ticket( $mock_ticket, $mock_ticket_meta );

			$sut->update_from_meta();

			$this->assertEquals( $local_qty, $sut->get_local_qty() );
			$this->assertEquals( $global_qty, $sut->get_global_qty() );
			$this->assertEquals( $expected_stock, $sut->get_stock() );
		}

		public function new_glocal_values() {
//	$global_qty, $local_qty, $new_value, $new_global_qty, $new_local_qty
			return array(
				array( 10, 5, 3, 8, 3 ), array( 10, 20, 5, 5, 15 ), array( 20, 20, 10, 10, 10 ),
				array( 20, 0, 5, 25, 5 ), array( 0, 10, 15, 15, 25 ), array( 0, 0, 7, 7, 7 ), array( 20, 0, 0, 20, 0 )
			);
		}

		/**
		 * @test
		 * it should properly set the global and the local stock is ticket is global and local
		 * @dataProvider new_glocal_values
		 */
		public function it_should_properly_set_the_global_and_the_local_stock_is_ticket_is_global_and_local( $global_qty, $local_qty, $new_value, $new_global_qty, $new_local_qty ) {
			$mock_ticket = $this->getMock( 'TribeEventsTicketObject' );
			$mock_ticket_meta = $this->getMock( 'TribeEventsTickets_TicketMeta' );
			$meta = array(
				'use_global' => true, 'use_local' => true, 'local_qty' => $local_qty, 'global_stock_id' => 'default'
			);
			$mock_ticket_meta->expects( $this->any() )->method( 'get_meta' )->will( $this->returnValue( $meta ) );
			$mock_ticket_meta->expects( $this->any() )->method( 'get_local_qty' )
			                 ->will( $this->returnValue( $local_qty ) );
			$mock_ticket_meta->expects( $this->any() )->method( 'get_global_qty' )
			                 ->will( $this->returnValue( $global_qty ) );
			$mock_ticket_meta->expects( $this->once() )->method( 'set_local_qty' )->with( $new_local_qty );
			$mock_ticket_meta->expects( $this->once() )->method( 'set_global_qty' )->with( 'default', $new_global_qty );

			$sut = TribeEventsTicketStockObject::from_ticket( $mock_ticket, $mock_ticket_meta );
			$sut->update_from_meta();
			$sut->set_stock( $new_value );
		}

		/**
		 * @test
		 * it should set the ticket to unlimited stock if use global and use local are false in the meta
		 */
		public function it_should_set_the_ticket_to_unlimited_stock_if_use_global_and_use_local_are_false_in_the_meta() {
			$mock_ticket = $this->getMock( 'TribeEventsTicketObject' );
			$mock_ticket_meta = $this->getMock( 'TribeEventsTickets_TicketMeta' );
			$meta = array(
				'use_global' => false, 'use_local' => false, 'local_qty' => 13, 'global_stock_id' => 'default'
			);
			$mock_ticket_meta->expects( $this->any() )->method( 'get_meta' )->will( $this->returnValue( $meta ) );

			$sut = TribeEventsTicketStockObject::from_ticket( $mock_ticket, $mock_ticket_meta );
			$sut->update_from_meta();

			$this->assertInstanceOf( 'TribeEventsTickets_Stock_UnlimitedType', $sut->type );
		}

		public function states() {
			// local, global, glocal, unlimited
			return array(
				array( true, true ), array( true, false ), array( false, true ), array( false, false )
			);
		}

		/**
		 * @test
		 * it should set stock meta when changing ticket type
		 * @dataProvider states
		 */
		public function it_should_set_stock_meta_when_changing_ticket_type( $local, $global ) {
			// remind it starts from local
			$mock_ticket = $this->getMock( 'TribeEventsTicketObject' );
			$mock_ticket_meta = $this->getMock( 'TribeEventsTickets_TicketMeta' );
			$mock_ticket_meta->expects( $this->any() )->method( 'set_use_local' )->with( $local );
			$mock_ticket_meta->expects( $this->any() )->method( 'set_use_global' )->with( $global );

			$sut = TribeEventsTicketStockObject::from_ticket( $mock_ticket, $mock_ticket_meta );

			$sut->use_local( $local );
			$sut->use_global( $global );
		}
	}