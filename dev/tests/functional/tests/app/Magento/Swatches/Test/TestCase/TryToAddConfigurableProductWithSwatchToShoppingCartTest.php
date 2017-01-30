<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swatches\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Preconditions:
 * 1. Configure text swatch attribute.
 * 2. Create configurable product with this attribute
 * 3. Open it on catalog page
 * 4. Click on 'Add to Cart' button
 * Steps:
 * 1. Go to Frontend.
 * 2. Open category page with created product
 * 3. Click on 'Add to Cart' button
 * 4. Perform asserts
 *
 * @group Configurable_Product
 * @ZephyrId MAGETWO-59979
 */
class TryToAddConfigurableProductWithSwatchToShoppingCartTest extends Scenario
{
    /**
     * Runs add configurable product with swatches attributes test.
     *
     * @return void
     */
    public function test()
    {
        $this->executeScenario();
    }
}
