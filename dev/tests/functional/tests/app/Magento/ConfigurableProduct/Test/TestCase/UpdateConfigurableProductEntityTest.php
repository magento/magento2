<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Test Flow:
 *
 * Preconditions:
 * 1. Two simple products are created.
 * 2. Configurable attribute with two options is created.
 * 3. Configurable attribute added to default template.
 * 4. Configurable product is created.
 *
 * Steps:
 * 1. Log in to backend.
 * 2. Open Products -> Catalog.
 * 3. Search and open configurable product from preconditions.
 * 4. Fill in data according to dataset.
 * 5. Save product.
 * 6. Perform all assertions.
 *
 * @group Configurable_Product_(MX)
 * @ZephyrId MAGETWO-29916
 */
class UpdateConfigurableProductEntityTest extends Scenario
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'MX';
    /* end tags */

    /**
     * Update configurable product.
     *
     * @return array
     */
    public function test()
    {
        $this->executeScenario();
    }
}
