<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestCase;

use Magento\Config\Test\Fixture\ConfigData;
use Magento\Mtf\TestCase\Scenario;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Preconditions:
 * 1. Twenty Simple products are created.
 *
 * Steps:
 * 1. Navigate to frontend.
 * 2. Create additional product if needed.
 * 3. Open each test product page and add each of them to cart.
 * 4. Open shopping cart.
 * 5. Perform all assertions.
 *
 * @group Shopping_Cart
 * @ZephyrId MAGETWO-63338, MAGETWO-63339, MAGETWO-63337
 */
class ShoppingCartPagerTest extends Scenario
{
    /* tags */
    const MVP = 'yes';
    const SEVERITY = 'S2';
    /* end tags */

    /**
     * Prepare test data.
     *
     * @param FixtureFactory $fixtureFactory
     * @return array
     */
    public function __prepare(FixtureFactory $fixtureFactory)
    {
        $products = [];
        for ($i = 1; $i <= 20; $i++) {
            $products[$i] = $fixtureFactory->createByCode('catalogProductSimple', ['dataset' => 'default']);
            $products[$i]->persist();
        }

        return ['preconditionProducts' => $products];
    }

    /**
     * Run shopping cart pager test.
     *
     * @return void
     */
    public function test()
    {
        $this->executeScenario();
    }
}
