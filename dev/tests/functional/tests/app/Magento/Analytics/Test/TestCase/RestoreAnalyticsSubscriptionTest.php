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
 * 2. Unset checkbox in subscription pop-up
 * 3. Clcik Cancel on subscription pop-up
 * 3. Navigate to menu Stores > Configuration > General > Analytics > General
 * 4. Set option "Send the system and transaction data to Magento Analytics service to Yes
 *
 * @ZephyrId MAGETWO-63196
 */
class RestoreAnalyticsSubscriptionTest extends Scenario
{
    /* tags */
    const MVP = 'no';
    /* end tags */

    public function test()
    {
        $this->executeScenario();
    }
}
