<?php


	class Global_Stock_usageTest extends \WP_UnitTestCase {

		public function setUp() {
			// before
			parent::setUp();

			// your set up methods here
		}

		public function tearDown() {
			// your tear down methods here

			// then
			parent::tearDown();
		}

		public function ticket_settings() {
			return array(
				array( 10, false, true, 20, 'default' ),
				array( 11, true, true, 20, 'some_stock' ),
				array( 12, false, false, '', 'another_stock' ),
				array( 13, true, true, 20, 'foo_stock' )
			);
		}

		/**
		 * @test
		 * it should properly save stock meta
		 * @dataProvider ticket_settings
		 */
		public function it_should_properly_save_stock_meta( $id, $global, $local, $stock, $global_stock_id ) {
			$sut = new TribeEventsTicketObject();
			$sut->ID = $id;
			$sut->use_global_stock( $global, $global_stock_id );
			$sut->use_local_stock( $local );
			$sut->stock = $stock;

			$this->assertEquals( $global, $sut->has_global_stock($global_stock_id) );
			$this->assertEquals( $local, $sut->has_local_stock() );
			$this->assertEquals( $stock, $sut->stock );
		}

	}