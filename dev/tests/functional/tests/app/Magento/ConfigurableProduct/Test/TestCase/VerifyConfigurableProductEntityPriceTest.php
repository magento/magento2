<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Preconditions:
 * 1. Configurable product is created.
 *
 * Steps:
 * 1. Log in to Admin.
 * 2. Open Products -> Catalog.
 * 3. Search and open child of configurable product from preconditions.
 * 4. Fill in data according to dataset.
 * 5. Save product.
 * 6. Perform all assertions.
 *
 * @group Configurable_Product
 * @ZephyrId MAGETWO-60196, MAGETWO-60206, MAGETWO-60236, MAGETWO-60296, MAGETWO-60297, MAGETWO-60325, MAGETWO-60328,
 * MAGETWO-60329, MAGETWO-60330
 */
class VerifyConfigurableProductEntityPriceTest extends Scenario
{
    /**
     * Verify configurable product price.
     *
     * @return array
     */
    public function test()
    {
        $this->executeScenario();
    }
}
