<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Preconditions:
 * 1. Product is created.
 *
 * Steps:
 * 1. Login to backend.
 * 2. Open Reports > Low Stock.
 * 3. Perform appropriate assertions.
 *
 * @group Reports
 * @ZephyrId MAGETWO-27193
 */
class LowStockProductsReportEntityTest extends Scenario
{
    /* tags */
    const MVP = 'no';
    /* end tags */

    /**
     * Runs low stock products report test.
     *
     * @return void
     */
    public function test()
    {
        $this->executeScenario();
    }
}
