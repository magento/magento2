<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Catalog\Test\TestCase\ProductAttribute;

use Mtf\TestCase\Injectable;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Fixture\CatalogAttributeSet;
use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductAttributeIndex;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductAttributeNew;

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
     * @param CatalogProductAttribute $productAttribute
     * @param CatalogAttributeSet $productTemplate
     * @param CatalogProductAttributeIndex $attributeIndex
     * @param CatalogProductAttributeNew $attributeNew
     * @return void
     */
    public function testUpdateProductAttribute(
        CatalogProductAttribute $productAttributeOriginal,
        CatalogProductAttribute $productAttribute,
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
        $attributeNew->getAttributeForm()->fill($productAttribute);
        $attributeNew->getPageActions()->save();
    }
}
