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

			$this->assertInstanceOf('TribeEventsTicketStockObject', $sut->get_stock_object());
		}
	}