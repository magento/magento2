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
use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductAttributeNew;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductAttributeIndex;
use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProductInjectable;

/**
 * Test Creation for Delete Used in Configurable ProductAttribute
 *
 * Test Flow:
 *
 * Precondition:
 * 1. Configurable product is created.
 *
 * Steps:
 * 1. Log in as default admin user.
 * 2. Go to Stores > Attributes > Product.
 * 3. Search product attribute in grid by given data.
 * 4. Open this attribute by clicking.
 * 5. Click on the "Delete Attribute" button.
 * 6. Perform asserts.
 *
 * @group Product_Attributes_(MX)
 * @ZephyrId MAGETWO-26652
 */
class DeleteUsedInConfigurableProductAttributeTest extends Injectable
{
    /**
     * Catalog product attribute index page
     *
     * @var CatalogProductAttributeIndex
     */
    protected $attributeIndex;

    /**
     * Catalog product attribute new page
     *
     * @var CatalogProductAttributeNew
     */
    protected $attributeNew;

    /**
     * Injection data
     *
     * @param CatalogProductAttributeIndex $attributeIndex
     * @param CatalogProductAttributeNew $attributeNew
     * @return void
     */
    public function __inject(CatalogProductAttributeIndex $attributeIndex, CatalogProductAttributeNew $attributeNew)
    {
        $this->attributeIndex = $attributeIndex;
        $this->attributeNew = $attributeNew;
    }

    /**
     * Run Delete used in configurable product attribute test
     *
     * @param ConfigurableProductInjectable $product
     * @return array
     */
    public function test(ConfigurableProductInjectable $product)
    {
        // Precondition
        $product->persist();
        /** @var CatalogProductAttribute $attribute */
        $attribute = $product->getDataFieldConfig('configurable_attributes_data')['source']
            ->getAttributes()['attribute_key_0'];
        // Steps
        $this->attributeIndex->open();
        $this->attributeIndex->getGrid()->searchAndOpen(['attribute_code' => $attribute->getAttributeCode()]);
        $this->attributeNew->getPageActions()->delete();

        return ['attribute' => $attribute];
    }
}
