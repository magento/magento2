<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\ProductAttribute;

use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductAttributeIndex;
use Magento\Mtf\TestCase\Injectable;

/**
 * Cover DeleteSystemProductAttribute with functional tests designed for automation
 *
 * Test Flow:
 * 1. Log in as default admin user.
 * 2. Go to Stores > Attributes > Product.
 * 3. Search system product attribute in grid by given data.
 * 4. Click on line with search results.
 * 5. Perform assertion.
 *
 * @group Product_Attributes
 * @ZephyrId MAGETWO-24771
 */
class DeleteSystemProductAttributeTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    /* end tags */

    /**
     * Run delete system product attribute test
     *
     * @param CatalogProductAttribute $productAttribute
     * @param CatalogProductAttributeIndex $attributeIndex
     * @return void
     */
    public function testDeleteSystemProductAttribute(
        CatalogProductAttribute $productAttribute,
        CatalogProductAttributeIndex $attributeIndex
    ) {
        $filter = $productAttribute->getData();

        // Steps
        $attributeIndex->open();
        $attributeIndex->getGrid()->searchAndOpen($filter);
    }
}
