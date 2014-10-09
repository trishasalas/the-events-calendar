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
	 * it should leave property access unaltered
	 */
	public function it_should_leave_property_access_unaltered() {
		$sut       = $this->getFilterEnabledMock();
		$sut->name = 'Foo';
		$this->assertEquals( 'Foo', $sut->name );
	}

	/**
	 * @test
	 * it should not affect the global stock by default
	 */
	public function it_should_not_affect_the_global_stock_by_default() {
		$sut = $this->getFilterEnabledMock();
		$this->assertFalse( $sut->global_stock_id );
	}

	/**
	 * @test
	 * it should allow accessing the ticket stock if ticket does not affect global stock
	 */
	public function it_should_allow_accessing_the_ticket_stock_if_ticket_does_not_affect_global_stock() {
		$sut        = $this->getFilterEnabledMock();
		$sut->stock = 23;
		$this->assertEquals( 23, $sut->stock );
	}

	/**
	 * @test
	 * it should get the event for the ticket when trying to set a global stock ticket
	 */
	public function it_should_get_the_event_for_the_ticket_when_trying_to_set_a_global_stock_ticket() {
		$sut                  = $this->getFilterEnabledMock();
		$sut->ID              = 11;
		$sut->global_stock_id = 'stock1';
		$tet                  = Test::double( 'TribeEventsTickets', [ 'find_matching_event' => false ] );

		$this->setExpectedException( 'Exception' );
		$sut->stock = 1;

		$tet->verifyInvoked( 'find_matching_event', [ 11 ] );
	}

	public function values() {
		return array_map( function ( $val ) {
			return array( $val );
		}, range( 0, 1000, 50 ) );
	}

	/**
	 * @test
	 * it should set the global stock when trying to set stock on global tickets
	 * @dataProvider values
	 */
	public function it_should_set_the_global_stock_when_trying_to_set_stock_on_global_tickets( $value ) {
		$sut                                                  = $this->getFilterEnabledMock();
		$sut->global_stock_id                                 = 'stock1';
		$meta                                                 = [ 'stock1' => 30 ];
		$event                                                = $this->getMock( 'WP_Post' );
		$event->{TribeEventsTicketObject::GLOBAL_STOCKS_META} = $meta;
		Test::double( 'TribeEventsTickets', [ 'find_matching_event' => $event ] );
		$globalStock = Test::double('TribeEventsGlobalTicketStock', ['set_stock' => true]);

		$sut->stock = $value;

		$globalStock->verifyInvokedOnce('set_stock');
	}

	/**
	 * @test
	 * it should get the global stock when trying to get stock of global ticket
	 * @dataProvider values
	 */
	public function it_should_get_the_global_stock_when_trying_to_get_stock_of_global_ticket( $value ) {
		$sut                                                  = $this->getFilterEnabledMock();
		$sut->global_stock_id                                 = 'stock1';
		$meta                                                 = [ 'stock1' => $value ];
		$event                                                = $this->getMock( 'WP_Post' );
		$event->{TribeEventsTicketObject::GLOBAL_STOCKS_META} = $meta;
		Test::double( 'TribeEventsTickets', [ 'find_matching_event' => $event ] );

		$this->assertEquals( $value, $sut->stock );
	}

	public function falsyValues() {
		return array(
			array( '' ),
			array( 0 ),
			array( array() ),
			array( null ),
			array( false )
		);
	}

	/**
	 * @test
	 * it should not use global stock at all if filter returns falsy value
	 * @dataProvider falsyValues
	 */
	public function it_should_not_use_global_stock_at_all_if_filter_returns_falsy_value( $falsyValue ) {
		$sut = $this->getFilterMock( $falsyValue );
		// global off by default: I'm setting the local value here
		$sut->stock = 30;
		// this will enable global stock
		$sut->global_stock_id = 'stock1';

		$stock = $sut->stock;

		$this->assertEquals( 30, $stock );
	}

	protected function getFilterEnabledMock() {

		return $this->getFilterMock( true );
	}

	protected function getFilterDisabledMock() {

		return $this->getFilterMock( false );
	}

	protected function getFilterMock( $filterReturnValue ) {
		// partial mock to skip WP functions
		$sut = $this->getMock( $this->sutClassName, array( 'is_global_stock_enabled' ) );
		// set filter to go, will test later
		$sut->expects( $this->any() )->method( 'is_global_stock_enabled' )
		    ->will( $this->returnValue( $filterReturnValue ) );

		return $sut;
	}

}