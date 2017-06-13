<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\ProductAttribute;

use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Scenario;

/**
 * Preconditions:
 * 1. Create Product.
 *
 * Steps:
 * 1. Log in to Backend.
 * 2. Navigate to Products>Catalog.
 * 3. Open product created in preconditions.
 * 4. Click add new attribute.
 * 5. Fill out fields data according to data set.
 * 6. Save Product Attribute.
 * 7. Fill attribute value.
 * 8. Save product.
 * 7. Perform appropriate assertions.
 *
 * @group Product_Attributes
 * @ZephyrId MAGETWO-30528
 */
class CreateProductAttributeEntityFromProductPageTest extends Scenario
{
    /* tags */
    const MVP = 'yes';
    /* end tags */

    /**
     * Prepare data for test.
     *
     * @param FixtureFactory $fixtureFactory
     * @return array
     */
    public function __prepare(FixtureFactory $fixtureFactory)
    {
        $product = $fixtureFactory->createByCode(
            'catalogProductSimple',
            ['dataset' => 'product_with_category_with_anchor']
        );
        $product->persist();
        return ['product' => $product];
    }

    /**
     * Run CreateProductAttributeEntity from product page test.
     *
     * @return void
     */
    public function test()
    {
        $this->executeScenario();
    }
}
