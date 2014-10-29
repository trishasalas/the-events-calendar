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
				array( 10, false, true, 20 ),
				array( 11, true, true, 20 ),
				array( 12, false, false, '' ),
				array( 13, true, true, 20 )
			);
		}

		/**
		 * @test
		 * it should properly save stock meta
		 * @dataProvider ticket_settings
		 */
		public function it_should_properly_save_stock_meta( $id, $global, $local, $stock ) {
			$sut = new TribeEventsTicketObject();
			$sut->ID = $id;
			$sut->use_global_stock( $global );
			$sut->use_local_stock( $local );
			$sut->stock = $stock;

			$this->assertEquals( $global, $sut->has_global_stock() );
			$this->assertEquals( $local, $sut->has_local_stock() );
			$this->assertEquals( $stock, $sut->stock );
		}
	}