<?php

class TribeEventsTicketStockObjectTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
    }

    protected function tearDown()
    {
    }

    /**
     * @test
     * it should default to local stock
     */
    public function it_should_default_to_local_stock() {
        $sut = new TribeEventsTicketStockObject();

        $this->assertInstanceOf('TribeEventsTickets_Stock_LocalType', $sut->get_type());
    }
}