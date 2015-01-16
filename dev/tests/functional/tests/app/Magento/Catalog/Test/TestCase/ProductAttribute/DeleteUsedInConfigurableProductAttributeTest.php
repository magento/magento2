<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\ProductAttribute;

use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductAttributeIndex;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductAttributeNew;
use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProductInjectable;
use Mtf\TestCase\Injectable;

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
