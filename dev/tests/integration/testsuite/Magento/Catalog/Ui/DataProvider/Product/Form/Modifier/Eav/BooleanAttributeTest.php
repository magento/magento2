<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Eav;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractEavTest;

/**
 * Provides tests for product form eav modifier with custom boolean attribute.
 *
 * @magentoDbIsolation enabled
 */
class BooleanAttributeTest extends AbstractEavTest
{
    /**
     * @magentoDataFixture Magento/Catalog/_files/product_boolean_attribute.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @return void
     */
    public function testModifyMeta(): void
    {
        $this->callModifyMetaAndAssert(
            $this->getProduct(),
            $this->addMetaNesting($this->getAttributeMeta(), 'product-details', 'boolean_attribute')
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_boolean_attribute.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @return void
     */
    public function testModifyData(): void
    {
        $product = $this->getProduct();
        $attributeData = ['boolean_attribute' => 1];
        $this->saveProduct($product, $attributeData);
        $expectedData = $this->addDataNesting($attributeData);
        $this->callModifyDataAndAssert($product, $expectedData);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_boolean_attribute.php
     * @return void
     */
    public function testModifyMetaNewProduct(): void
    {
        $this->setAttributeDefaultValue('boolean_attribute', '0');
        $attributesMeta = array_merge($this->getAttributeMeta(), ['default' => '0']);
        $expectedMeta = $this->addMetaNesting(
            $attributesMeta,
            'product-details',
            'boolean_attribute'
        );
        $this->callModifyMetaAndAssert($this->getNewProduct(), $expectedMeta);
    }

    /**
     * @return array
     */
    private function getAttributeMeta(): array
    {
        return [
            'dataType' => 'boolean',
            'formElement' => 'checkbox',
            'visible' => '1',
            'required' => '0',
            'label' => 'Boolean Attribute',
            'code' => 'boolean_attribute',
            'source' => 'product-details',
            'scopeLabel' => '[STORE VIEW]',
            'globalScope' => false,
            'sortOrder' => '__placeholder__',
            'componentType' => 'field',
            'prefer' => 'toggle',
            'valueMap' => [
                'true' => '1',
                'false' => '0',
            ]
        ];
    }
}
