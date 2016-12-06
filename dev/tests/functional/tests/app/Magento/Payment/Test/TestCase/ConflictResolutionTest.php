<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Tests conflict resolution for payments configuration.
 * Class ConflictResolutionTest
 */
class ConflictResolutionTest extends Scenario
{
    /* tags */
    const MVP = 'no';
    const TEST_TYPE = '3rd_party_test';
    const SEVERITY = 'S2';
    /* end tags */

    /**
     * Runs test that verifies conflict resolution for payments configuration.
     *
     * @return void
     */
    public function test()
    {
        $this->executeScenario();
    }
}
