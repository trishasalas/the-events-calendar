<?php


	class TribeEventsTicketStockObjectTest extends \PHPUnit_Framework_TestCase {

		protected $sut;
		protected $mock_ticket_meta;
		protected $mock_ticket;

		protected function setUp() {
			$this->mock_ticket = $this->getMockBuilder( 'TribeEventsTicketObject' )->disableOriginalConstructor()
			                          ->getMock();
			$this->mock_ticket_meta = $this->getMockBuilder( 'TribeEventsTickets_TicketMeta' )
			                               ->disableOriginalConstructor()->getMock();
			$this->sut = new TribeEventsTicketStockObject();
		}

		protected function tearDown() {
		}

		/**
		 * @test
		 * it should default to local stock
		 */
		public function it_should_default_to_unlimited_stock() {

			$this->assertInstanceOf( 'TribeEventsTickets_Stock_UnlimitedType', $this->sut->type );
		}

		/**
		 * @test
		 * it should default to unlimited local quantity
		 */
		public function it_should_default_to_unlimited_local_quantity() {

			$this->assertEquals( TribeEventsTicketObject::UNLIMITED_STOCK, $this->sut->get_local_qty() );
		}

		/**
		 * @test
		 * it should default to not using the global stock
		 */
		public function it_should_default_to_not_using_the_global_stock() {

			$this->assertFalse( $this->sut->type->is_global() );
			$this->assertFalse( $this->sut->type->is_global_and_local() );
		}

		/**
		 * @test
		 * it should default to emtpy string global stock id
		 */
		public function it_should_default_to_emtpy_string_global_stock_id() {

			$this->assertEquals( '', $this->sut->get_global_stock_id() );
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
			$this->sut->type = $type;

			$this->assertEquals( $is_local, $this->sut->type->is_local() );
			$this->assertEquals( $is_global, $this->sut->type->is_global() );
			$this->assertEquals( $is_glocal, $this->sut->type->is_global_and_local() );
			$this->assertEquals( $is_unlimited, $this->sut->type->is_unlimited() );
		}

		/**
		 * @test
		 * it should change to local stock type and set the quantity when setting a stock
		 */
		public function it_should_change_to_local_stock_type_and_set_the_quantity_when_setting_a_stock() {

			$this->sut->set_stock( 23 );

			$this->assertTrue( $this->sut->type->is_local() );
			$this->assertEquals( 23, $this->sut->get_stock() );
			$this->assertEquals( 23, $this->sut->get_local_qty() );
		}

		/**
		 * @test
		 * it should set stock meta when set_stock_meta method is called
		 */
		public function it_should_set_stock_meta_when_set_stock_meta_method_is_called() {
			$meta = array(
				'use_global'      => false,
				'use_local'       => true,
				'local_qty'       => 23,
				'global_stock_id' => 'default'
			);
			$this->sut->set_stock_meta( $meta );

			$this->assertTrue( $this->sut->type->is_local() );
			$this->assertFalse( $this->sut->type->is_global_and_local() );
			$this->assertFalse( $this->sut->type->is_global() );
			$this->assertFalse( $this->sut->type->is_unlimited() );
			$this->assertEquals( 23, $this->sut->get_local_qty() );
			$this->assertEquals( 23, $this->sut->get_stock() );
		}

		/**
		 * @test
		 * it should return the min between local and global set if both set
		 */
		public function it_should_return_the_min_between_local_and_global_set_if_both_set() {
			$event_stock_meta = array( 'default' => 12 );
			$this->mock_ticket_meta->expects( $this->any() )->method( 'get_event_stock_meta' )
			                       ->will( $this->returnValue( $event_stock_meta ) );
			$meta = array(
				'use_global'      => true,
				'use_local'       => true,
				'local_qty'       => 23,
				'global_stock_id' => 'default'
			);
			$this->sut->set_stock_meta( $meta );

			$this->assertEquals( 23, $this->sut->get_local_qty() );
			$this->assertEquals( 12, $this->sut->get_global_qty() );
			$this->assertEquals( 12, $this->sut->get_stock() );
		}
	}