<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\ProductAttribute;

use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductAttributeIndex;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductAttributeNew;
use Mtf\TestCase\Injectable;

/**
 * Test Creation for UpdateProductAttributeEntity
 *
 * Preconditions:
 * 1. Attribute is created
 * Test Flow:
 * 1. Log in as default admin user.
 * 2. Go to Stores > Attributes > Product
 * 3. Search product attribute in grid by given data
 * 4. Click on the required product attribute
 * 5. Click on the "Delete Attribute" button
 * 6. Perform all assertions
 *
 * @group Product_Attributes_(MX)
 * @ZephyrId MAGETWO-24998
 */
class DeleteProductAttributeEntityTest extends Injectable
{
    /**
     * Run DeleteProductAttributeEntity test
     *
     * @param CatalogProductAttribute $attribute
     * @param CatalogProductAttributeIndex $attributeIndex
     * @param CatalogProductAttributeNew $attributeNew
     * @return void
     */
    public function testDeleteProductAttribute(
        CatalogProductAttribute $attribute,
        CatalogProductAttributeIndex $attributeIndex,
        CatalogProductAttributeNew $attributeNew
    ) {
        //Precondition
        $attribute->persist();

        $filter = [
            'frontend_label' => $attribute->getFrontendLabel(),
        ];
        //Steps
        $attributeIndex->open();
        $attributeIndex->getGrid()->searchAndOpen($filter);
        $attributeNew->getPageActions()->delete();
    }
}
