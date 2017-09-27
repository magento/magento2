<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Preconditions:
 1. Choose the color attribute and set attribute properties to
 2. is_filterable = Filterable (with results)
 3. is_filterable_in_search = Yes
 *
 * Steps:
 * 1. Log in to Admin.
 * 2. Open Products -> Catalog.
 * 3. Create a configurable product with three variations.
 * 4. Search and open child of configurable product from preconditions.
 * 4. Fill in data according to dataset.
 * 5. Save product.
 * 6. Perform all assertions.
 *
 * @group Configurable_Product
 * @ZephyrId MAGETWO-72439
 */
class VerifyConfigurableProductLayeredNavigationTest extends Scenario
{
    /**
     * Verify configurable product options in layered navigation.
     *
     * @return array
     */
    public function test()
    {
        $this->executeScenario();
    }
}
