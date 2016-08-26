<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\ProductAttribute;

use Magento\Mtf\TestCase\Scenario;

/**
 * Steps:
 * 1. Log in to Backend.
 * 2. Navigate to Stores > Attributes > Product.
 * 3. Start to create new Product Attribute.
 * 4. Fill out fields data according to data set.
 * 5. Save Product Attribute.
 * 6. Perform appropriate assertions.
 *
 * @group Product_Attributes
 * @ZephyrId MAGETWO-24767
 */
class CreateProductAttributeEntityTest extends Scenario
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = 'extended_acceptance_test';
    /* end tags */

    /**
     * Run CreateProductAttributeEntity test.
     *
     * @return array
     */
    public function testCreateProductAttribute()
    {
        $this->executeScenario();
    }
}
