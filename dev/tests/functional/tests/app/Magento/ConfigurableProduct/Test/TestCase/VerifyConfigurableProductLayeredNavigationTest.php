<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
=======
declare(strict_types=1);
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3

namespace Magento\ConfigurableProduct\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Preconditions:
<<<<<<< HEAD
 1. Choose the color attribute and set attribute properties to
 2. is_filterable = Filterable (with results)
 3. is_filterable_in_search = Yes
 *
 * Steps:
 * 1. Log in to Admin.
 * 2. Open Products -> Catalog.
 * 3. Create a configurable product with three variations.
=======
 * 1. Choose an attribute for configurable options or create it and set attribute properties to
 * 2. is_filterable = Filterable (with results)
 * 3. is_filterable_in_search = Yes
 *
 * Steps:
 * 1. Log in to Admin.
 * 2. Open Catalog -> Products.
 * 3. Create a configurable product with an attribute from preconditions.
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
 * 4. Search and open child of configurable product from preconditions.
 * 4. Fill in data according to dataset.
 * 5. Save product.
 * 6. Perform all assertions.
 *
 * @group Configurable_Product
<<<<<<< HEAD
 * @ZephyrId MAGETWO-72439
=======
 * @ZephyrId MAGETWO-89751
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
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
