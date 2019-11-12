<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Eav;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractEavTest;

/**
 * Provides tests for product form eav modifier with custom decimal attribute.
 *
 * @magentoDataFixture Magento/Catalog/_files/product_decimal_attribute.php
 * @magentoDataFixture Magento/Catalog/_files/product_simple.php
 * @magentoDbIsolation enabled
 */
class DecimalAttributeTest extends AbstractEavTest
{
    /**
     * @return void
     */
    public function testModifyMeta(): void
    {
        $this->callModifyMetaAndAssert(
            $this->getProduct(),
            $this->addMetaNesting($this->getAttributeMeta(), 'product-details', 'decimal_attribute')
        );
    }

    /**
     * @return void
     */
    public function testModifyData(): void
    {
        $product = $this->getProduct();
        $attributeData = ['decimal_attribute' => '10.00'];
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
            'dataType' => 'price',
            'formElement' => 'input',
            'visible' => '1',
            'required' => '0',
            'label' => 'Decimal Attribute',
            'code' => 'decimal_attribute',
            'source' => 'product-details',
            'scopeLabel' => '[GLOBAL]',
            'globalScope' => true,
            'sortOrder' => '__placeholder__',
            'componentType' => 'field',
            'validation' => [
                'validate-zero-or-greater' => true,
            ],
            'addbefore' => '$'
        ];
    }
}
