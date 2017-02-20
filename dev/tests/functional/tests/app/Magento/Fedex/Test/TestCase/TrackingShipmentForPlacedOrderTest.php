<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Fedex\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * @group Fedex
 * @ZephyrId MAGETWO-58158
 */
class TrackingShipmentForPlacedOrderTest extends Scenario
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = '3rd_party_test';
    const SEVERITY = 'S1';
    /* end tags */

    /**
     * Runs one page checkout test.
     *
     * @return void
     */
    public function test()
    {
        $this->executeScenario();
    }
}
