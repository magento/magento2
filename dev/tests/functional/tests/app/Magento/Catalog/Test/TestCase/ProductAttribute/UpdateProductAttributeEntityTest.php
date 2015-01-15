<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\ProductAttribute;

use Magento\Catalog\Test\Fixture\CatalogAttributeSet;
use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductAttributeIndex;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductAttributeNew;
use Mtf\TestCase\Injectable;

/**
 * Test Creation for UpdateProductAttributeEntity
 *
 * Preconditions:
 * Preset : AttributeOptions
 * 1. Attribute is created (Attribute)
 * 2. Attribute set is created (Product Template)
 *
 * Test Flow:
 * 1. Log in to Backend.
 * 2. Navigate to Stores > Attributes > Product.
 * 3. Select created attribute from preconditions
 * 4. Fill data from dataset
 * 5. Click 'Save Attribute' button
 * 6. Perform all assertions
 *
 * @group Product_Attributes_(MX)
 * @ZephyrId MAGETWO-23459
 */
class UpdateProductAttributeEntityTest extends Injectable
{
    /**
     * Run UpdateProductAttributeEntity test
     *
     * @param CatalogProductAttribute $productAttributeOriginal
     * @param CatalogProductAttribute $attribute
     * @param CatalogAttributeSet $productTemplate
     * @param CatalogProductAttributeIndex $attributeIndex
     * @param CatalogProductAttributeNew $attributeNew
     * @return void
     */
    public function testUpdateProductAttribute(
        CatalogProductAttribute $productAttributeOriginal,
        CatalogProductAttribute $attribute,
        CatalogAttributeSet $productTemplate,
        CatalogProductAttributeIndex $attributeIndex,
        CatalogProductAttributeNew $attributeNew
    ) {
        //Precondition
        $productTemplate->persist();
        $productAttributeOriginal->persist();

        $filter = [
            'attribute_code' => $productAttributeOriginal->getAttributeCode(),
        ];

        //Steps
        $attributeIndex->open();
        $attributeIndex->getGrid()->searchAndOpen($filter);
        $attributeNew->getAttributeForm()->fill($attribute);
        $attributeNew->getPageActions()->save();
    }
}
