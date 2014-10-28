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
		 * @test
		 * it should trigger stock meta setting on the stock when ID is set
		 */
		public function it_should_trigger_stock_meta_setting_on_the_stock_when_id_is_set() {
			$sut = new TribeEventsTicketObject();
			$meta = TribeEventsTickets_TicketMeta::get_meta_defaults();
			$mock_meta = $this->getMockBuilder( 'TribeEventsTickets_TicketMeta' )->disableOriginalConstructor()
			                  ->setMethods( array( 'get_meta' ) )->getMock();
			$mock_stock = $this->get_mock_stock( array( 'set_stock_meta' ) );
			$sut->set_meta_object( $mock_meta );
			$sut->set_stock_object( $mock_stock );

			$sut->ID = 13;
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