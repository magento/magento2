<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Eav;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractEavTest;

/**
 * Provides tests for product form eav modifier with custom date attribute.
 *
 * @magentoDbIsolation enabled
 */
class DateAttributeTest extends AbstractEavTest
{
    /**
     * @magentoDataFixture Magento/Catalog/_files/product_date_attribute.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @return void
     */
    public function testModifyMeta(): void
    {
        $this->callModifyMetaAndAssert(
            $this->getProduct(),
            $this->addMetaNesting($this->getAttributeMeta(), 'product-details', 'date_attribute')
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_date_attribute.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @return void
     */
    public function testModifyData(): void
    {
        $product = $this->getProduct();
        $attributeData = ['date_attribute' => '01/01/2010'];
        $this->saveProduct($product, $attributeData);
        $expectedData = $this->addDataNesting($attributeData);
        $this->callModifyDataAndAssert($product, $expectedData);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_date_attribute.php
     * @return void
     */
    public function testModifyMetaNewProduct(): void
    {
        $this->setAttributeDefaultValue('date_attribute', '01/01/2000');
        $attributesMeta = array_merge($this->getAttributeMeta(), ['default' => '2000-01-01 00:00:00']);
        $expectedMeta = $this->addMetaNesting(
            $attributesMeta,
            'product-details',
            'date_attribute'
        );
        $this->callModifyMetaAndAssert($this->getNewProduct(), $expectedMeta);
    }

    /**
     * @return array
     */
    private function getAttributeMeta(): array
    {
        return [
            'dataType' => 'date',
            'formElement' => 'date',
            'visible' => '1',
            'required' => '0',
            'label' => 'Date Attribute',
            'code' => 'date_attribute',
            'source' => 'product-details',
            'scopeLabel' => '[GLOBAL]',
            'globalScope' => true,
            'sortOrder' => '__placeholder__',
            'componentType' => 'field'
        ];
    }
}
