<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\ProductAttribute;

use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Mtf\ObjectManager;
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
 * @group Product_Attributes_(MX)
 * @ZephyrId MAGETWO-24767
 */
class CreateProductAttributeEntityTest extends Scenario
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'MX';
    /* end tags */

    /**
     * CatalogProductAttribute object.
     *
     * @var CatalogProductAttribute
     */
    protected $attribute;

    /**
     * Run CreateProductAttributeEntity test.
     *
     * @param CatalogProductAttribute $productAttribute
     * @return array
     */
    public function testCreateProductAttribute(CatalogProductAttribute $productAttribute)
    {
        $this->attribute = $productAttribute;
        $this->executeScenario();
    }

    /**
     * Delete attribute after test.
     *
     * @return void
     */
    public function tearDown()
    {
        ObjectManager::getInstance()->create(
            'Magento\Catalog\Test\TestStep\DeleteAttributeStep',
            ['attribute' => $this->attribute]
        )->run();
    }
}
