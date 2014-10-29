<?php
	use AspectMock\Test as Test;

	class  TribeEventsTicketObjectTest extends \PHPUnit_Framework_TestCase {

		protected $sutClassName = 'TribeEventsTicketObject';

		protected function setUp() {
		}

		protected function tearDown() {
			Test::clean();
		}

		/**
		 * @test
		 * it should construct an instance of the stock object when constructor is called
		 */
		public function it_should_construct_an_instance_of_the_stock_object_when_constructor_is_called() {
			$sut = new TribeEventsTicketObject();

			$this->assertInstanceOf( 'TribeEventsTicketStockObject', $sut->get_stock_object() );
		}

		/**
		 * @test
		 * it should construct an instance of the ticket meta when constructor is called
		 */
		public function it_should_construct_an_instance_of_the_ticket_meta_when_constructor_is_called() {
			$sut = new TribeEventsTicketObject();

			$this->assertInstanceOf( 'TribeEventsTickets_TicketMeta', $sut->get_ticket_meta_object() );
		}

		/**
		 * @test
		 * it should call fetch meta on the ticket meta object when ID is set
		 */
		public function it_should_call_fetch_meta_on_the_ticket_meta_object_when_id_is_set() {
			$mock_ticket_meta = $this->getMock( 'TribeEventsTickets_TicketMeta' );
			$mock_stock = $this->getMock('TribeEventsTicketStockObject');

			$sut = new TribeEventsTicketObject($mock_ticket_meta, $mock_stock);
			$mock_ticket_meta->expects( $this->once() )->method( 'fetch_meta' );
			$mock_stock->expects($this->once())->method('update_from_meta');

			$sut->ID = 13;
		}

		/**
		 * @test
		 * it should call set stock method on the stock object when setting stock
		 */
		public function it_should_call_set_stock_method_on_the_stock_object_when_setting_stock() {
			$sut = new TribeEventsTicketObject();
			$mock_stock = $this->get_mock_stock( array( 'set_stock' ) );
			$mock_stock->expects( $this->once() )->method( 'set_stock' );
			$sut->set_stock_object( $mock_stock );

			$sut->stock = 12;
		}

		/**
		 * @test
		 * it should call get stock method on the stock object when getting the stock
		 */
		public function it_should_call_get_stock_method_on_the_stock_object_when_getting_the_stock() {
			$sut = new TribeEventsTicketObject();
			$mock_stock = $this->get_mock_stock( array( 'get_stock' ) );
			$mock_stock->expects( $this->once() )->method( 'get_stock' );
			$sut->set_stock_object( $mock_stock );

			$stock = $sut->stock;
		}


		/**
		 * @return object|PHPUnit_Framework_MockObject_MockObject
		 */
		protected function get_mock_stock( array $methods ) {
			$mock_stock = $this->getMockBuilder( 'TribeEventsTicketStockObject' )->setMethods( $methods )
			                   ->disableOriginalConstructor()->getMock();

			return $mock_stock;
		}
	}