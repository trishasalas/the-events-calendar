<?php

use AspectMock\Test as Test;

class GlobalStocksSetAndGetTest extends \WP_UnitTestCase {

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
	}

	public function tearDown() {
		// your tear down methods here
		Test::clean();
		// then
		parent::tearDown();
	}

	/**
	 * @test
	 * it should add the stock array as event meta when setting and not set
	 */
	public function it_should_add_the_stock_array_as_event_meta_when_setting_and_not_set() {
		$ticket                  = new TribeEventsTicketObject();
		$ticket->global_stock_id = 'stock1';
		$postarr                 = [
			'post_content' => 'Event content',
			'post_title'   => 'Global stock not set',
			'post_type'    => TribeEvents::POSTTYPE,
			'post_status'  => 'publish'
		];
		$eventId                 = wp_insert_post( $postarr );
		$event                   = get_post( $eventId );
		Test::double( 'TribeEventsTickets', [ 'find_matching_event' => $event ] );
		$sut = new TribeEventsGlobalTicketStock( $ticket );

		$sut->set_stock( 23 );

		$metaStocks = get_post_meta( $eventId, TribeEventsTicketObject::GLOBAL_STOCKS_META, true );
		$metaStock  = $metaStocks['stock1'];
		$this->assertEquals( 23, $metaStock );
	}

	/**
	 * @test
	 * it should set the value in the meta array when setting stock on already existing global stock
	 */
	public function it_should_set_the_value_in_the_meta_array_when_setting_stock_on_already_existing_global_stock() {
		$ticket                  = new TribeEventsTicketObject();
		$ticket->global_stock_id = 'stock1';
		$postarr                 = [
			'post_content' => 'Event content',
			'post_title'   => 'Global stock set',
			'post_type'    => TribeEvents::POSTTYPE,
			'post_status'  => 'publish'
		];
		$eventId                 = wp_insert_post( $postarr );
		add_post_meta( $eventId, TribeEventsTicketObject::GLOBAL_STOCKS_META, array( 'stock1' => 9 ), true );
		$event = get_post( $eventId );
		Test::double( 'TribeEventsTickets', [ 'find_matching_event' => $event ] );
		$sut = new TribeEventsGlobalTicketStock( $ticket );

		$sut->set_stock( 54 );

		$metaStocks = get_post_meta( $eventId, TribeEventsTicketObject::GLOBAL_STOCKS_META, true );
		$metaStock  = $metaStocks['stock1'];
		$this->assertEquals( 54, $metaStock );
	}

	/**
	 * @test
	 * it should add a stock if not existing in the meta
	 */
	public function it_should_add_a_stock_if_not_existing_in_the_meta() {
		$ticket                  = new TribeEventsTicketObject();
		$ticket->global_stock_id = 'stockNew';
		$postarr                 = [
			'post_content' => 'Event content',
			'post_title'   => 'Global stock set but missing this stock',
			'post_type'    => TribeEvents::POSTTYPE,
			'post_status'  => 'publish'
		];
		$eventId                 = wp_insert_post( $postarr );
		add_post_meta( $eventId, TribeEventsTicketObject::GLOBAL_STOCKS_META, array( 'stock1' => 9 ), true );
		$event = get_post( $eventId );
		Test::double( 'TribeEventsTickets', [ 'find_matching_event' => $event ] );
		$sut = new TribeEventsGlobalTicketStock( $ticket );

		$sut->set_stock( 11 );

		$metaStocks = get_post_meta( $eventId, TribeEventsTicketObject::GLOBAL_STOCKS_META, true );
		$metaStock  = $metaStocks['stockNew'];
		$this->assertEquals( 11, $metaStock );
	}

	/**
	 * @test
	 * it should add the stock meta to the event if getting global stock of ticket for the first time
	 */
	public function it_should_add_the_stock_meta_to_the_event_if_getting_global_stock_of_ticket_for_the_first_time() {
		$ticket                  = new TribeEventsTicketObject();
		$ticket->global_stock_id = 'stock1';
		$postarr                 = [
			'post_content' => 'Event content',
			'post_title'   => 'Global stock not set 2',
			'post_type'    => TribeEvents::POSTTYPE,
			'post_status'  => 'publish'
		];
		$eventId                 = wp_insert_post( $postarr );
		$event                   = get_post( $eventId );
		Test::double( 'TribeEventsTickets', [ 'find_matching_event' => $event ] );
		$sut = new TribeEventsGlobalTicketStock( $ticket );

		$stock = $sut->get_stock();

		$this->assertEquals( 0, $stock );
		$metaStocks = get_post_meta( $eventId, TribeEventsTicketObject::GLOBAL_STOCKS_META, true );
		$metaStock  = $metaStocks['stock1'];
		$this->assertEquals( 0, $metaStock );
	}

	/**
	 * @test
	 * it should get the global stock meta from an event
	 */
	public function it_should_get_the_global_stock_meta_from_an_event() {
		$ticket                  = new TribeEventsTicketObject();
		$ticket->global_stock_id = 'stock1';
		$postarr                 = [
			'post_content' => 'Event content',
			'post_title'   => 'Global stock set 2',
			'post_type'    => TribeEvents::POSTTYPE,
			'post_status'  => 'publish'
		];
		$eventId                 = wp_insert_post( $postarr );
		$event                   = get_post( $eventId );
		add_post_meta($event->ID, TribeEventsTicketObject::GLOBAL_STOCKS_META, array('stock1' => 35), true);
		Test::double( 'TribeEventsTickets', [ 'find_matching_event' => $event ] );
		$sut = new TribeEventsGlobalTicketStock( $ticket );

		$stock = $sut->get_stock();

		$this->assertEquals( 35, $stock );
	}

	/**
	 * @test
	 * it should create a new global stock when trying to get a non existing one
	 */
	public function it_should_create_a_new_global_stock_when_trying_to_get_a_non_existing_one() {
		$ticket                  = new TribeEventsTicketObject();
		$ticket->global_stock_id = 'stock2';
		$postarr                 = [
			'post_content' => 'Event content',
			'post_title'   => 'Global stock set but missing specific stock',
			'post_type'    => TribeEvents::POSTTYPE,
			'post_status'  => 'publish'
		];
		$eventId                 = wp_insert_post( $postarr );
		$event                   = get_post( $eventId );
		add_post_meta($event->ID, TribeEventsTicketObject::GLOBAL_STOCKS_META, array('stock1' => 35), true);
		Test::double( 'TribeEventsTickets', [ 'find_matching_event' => $event ] );
		$sut = new TribeEventsGlobalTicketStock( $ticket );

		$stock = $sut->get_stock();

		$this->assertEquals( 0, $stock );
		$metaStocks = get_post_meta( $eventId, TribeEventsTicketObject::GLOBAL_STOCKS_META, true );
		$metaStock  = $metaStocks['stock2'];
		$this->assertEquals( 0, $metaStock );
	}
}