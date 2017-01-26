<?php
/**
 * Copyright © 2013-2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Steps:
 * 1. Log in to backend.
 * 2. Click Cancel on subscription pop-up
 * 3. Navigate to menu Stores>Configuration>General>Analytics->General
 *
 * @ZephyrId MAGETWO-63156
 */
class DeclineAnalyticsSubscriptionTest extends Scenario
{
    /* tags */
    const MVP = 'no';
    /* end tags */

    public function test()
    {
        $this->executeScenario();
    }
}
