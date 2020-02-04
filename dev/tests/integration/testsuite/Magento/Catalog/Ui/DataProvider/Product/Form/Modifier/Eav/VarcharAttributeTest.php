<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Eav;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractEavTest;

/**
 * Provides tests for product form eav modifier with custom text attribute.
 *
 * @magentoDbIsolation enabled
 */
class VarcharAttributeTest extends AbstractEavTest
{
    /**
     * @magentoDataFixture Magento/Catalog/_files/product_varchar_attribute.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @return void
     */
    public function testModifyMeta(): void
    {
        $this->callModifyMetaAndAssert(
            $this->getProduct(),
            $this->addMetaNesting($this->getAttributeMeta(), 'product-details', 'varchar_attribute')
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_varchar_attribute.php
     * @return void
     */
    public function testModifyMetaNewProduct(): void
    {
        $this->setAttributeDefaultValue('varchar_attribute', 'test');
        $attributesMeta = array_merge($this->getAttributeMeta(), ['default' => 'test']);
        $expectedMeta = $this->addMetaNesting(
            $attributesMeta,
            'product-details',
            'varchar_attribute'
        );
        $this->callModifyMetaAndAssert($this->getNewProduct(), $expectedMeta);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_varchar_attribute.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @return void
     */
    public function testModifyData(): void
    {
        $product = $this->getProduct();
        $attributeData = ['varchar_attribute' => 'Test message'];
        $this->saveProduct($product, $attributeData);
        $expectedData = $this->addDataNesting($attributeData);
        $this->callModifyDataAndAssert($product, $expectedData);
    }

    /**
     * @return array
     */
    private function getAttributeMeta(): array
    {
        return [
            'dataType' => 'text',
            'formElement' => 'input',
            'visible' => '1',
            'required' => '0',
            'label' => 'Varchar Attribute',
            'code' => 'varchar_attribute',
            'source' => 'product-details',
            'scopeLabel' => '[GLOBAL]',
            'globalScope' => true,
            'sortOrder' => '__placeholder__',
            'componentType' => 'field'
        ];
    }
}
