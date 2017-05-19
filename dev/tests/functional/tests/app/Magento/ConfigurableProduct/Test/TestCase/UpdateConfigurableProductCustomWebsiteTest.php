<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Test Flow:
 * 1. In admin configure "Catalog Price Scope" to "Website"
 *     (Stores > Configuration > Catalog > Catalog > Price)
 * 2. Create Additional Website, Store, Store View.
 * 3. Create Configurable product with two variations, price for all variations is 10$ and assign it to both websites.
 * 4. Open all simple products, which are assigned to configurable and change their price in Default Store View to 15$
 * 5. Do reindex and clear magento cache
 * 6. Open on storefront on main website category with configurable product
 * 7. "As low as" price not shown for configurable product
 *
 * @group Configurable_Product_(MX)
 * @ZephyrId MAGETWO-64720
 */
class UpdateConfigurableProductCustomWebsiteTest extends Scenario
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'MX';
    /* end tags */

    /**
     * Test update Configurable product with custom website run.
     *
     * @return void
     */
    public function test()
    {
        $this->executeScenario();
    }
}
