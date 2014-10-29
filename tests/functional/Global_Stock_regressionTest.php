<?php

class Global_Stock_regressionTest extends \WP_UnitTestCase
{
    public function setUp()
    {
        // before
        parent::setUp();

        // your set up methods here
    }

    public function tearDown()
    {
        // your tear down methods here

        // then
        parent::tearDown();
    }

    /**
     * @test
     * it should allow constructing ticket object and using it as a data structure
     */
    public function it_should_allow_constructing_ticket_object_and_using_it_as_a_data_structure() {
        $ticket = new TribeEventsTicketObject();
        $ticket->stock = 13;

        $this->assertEquals(13, $ticket->stock);
    }

    /**
     * @test
     * it should allow setting the ticket ID with no backlashes
     */
    public function it_should_allow_setting_the_ticket_id_with_no_backlashes() {
        $ticket = new TribeEventsTicketObject();
        $ticket->stock = 13;
        $ticket->ID = 23;

        $this->assertEquals(13, $ticket->stock);
        $this->assertEquals(23, $ticket->ID);
    }

}