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
use Magento\Catalog\Test\Fixture\CatalogAttributeSet;
use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductAttributeIndex;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductAttributeNew;

/**
 * Test Creation for CreateProductAttributeEntity
 *
 * Test Flow:
 * 1. Log in to Backend.
 * 2. Navigate to Stores > Attributes > Product.
 * 3. Start to create new Product Attribute.
 * 4. Fill out fields data according to data set.
 * 5. Save Product Attribute.
 * 6. Perform appropriate assertions.
 *
 * @group Product_Attributes_(CS)
 * @ZephyrId MAGETWO-24767
 */
class CreateProductAttributeEntityTest extends Injectable
{
    /**
     * Run CreateProductAttributeEntity test
     *
     * @param CatalogProductAttribute $productAttribute
     * @param CatalogProductAttributeIndex $attributeIndex
     * @param CatalogProductAttributeNew $attributeNew
     * @param CatalogAttributeSet $productTemplate
     * @return array
     */
    public function testCreateProductAttribute(
        CatalogProductAttribute $productAttribute,
        CatalogProductAttributeIndex $attributeIndex,
        CatalogProductAttributeNew $attributeNew,
        CatalogAttributeSet $productTemplate
    ) {
        //Precondition
        $productTemplate->persist();

        //Steps
        $attributeIndex->open();
        $attributeIndex->getPageActionsBlock()->addNew();
        $attributeNew->getAttributeForm()->fill($productAttribute);
        $attributeNew->getPageActions()->save();
        return ['attribute' => $productAttribute];
    }
}
