<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Weee\Ui\DataProvider\Product\Form\Modifier\Eav;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractEavTest;

/**
 * Provides tests for product form eav modifier with custom weee attribute.
 *
 * @magentoDataFixture Magento/Weee/_files/fixed_product_attribute.php
 * @magentoDataFixture Magento/Catalog/_files/product_simple.php
 * @magentoDbIsolation enabled
 */
class FixedAttributeTest extends AbstractEavTest
{
    /**
     * @return void
     */
    public function testModifyMeta(): void
    {
        $this->callModifyMetaAndAssert(
            $this->getProduct(),
            $this->addMetaNesting($this->getAttributeMeta(), 'product-details', 'fixed_product_attribute')
        );
    }

    /**
     * @return void
     */
    public function testModifyData(): void
    {
        $product = $this->getProduct();
        $attributeData = [
            'fixed_product_attribute' => [
                ['website_id' => 0, 'country' => 'US', 'state' => 0, 'price' => 12.70, 'delete' => '']
            ]
        ];
        $this->saveProduct($product, $attributeData);
        $expectedData = $this->addDataNesting(
            [
                'fixed_product_attribute' => [
                    [
                        'website_id' => '0',
                        'country' => 'US',
                        'state' => '0',
                        'value' => '12.7000',
                        'website_value' => 12.7,
                    ]
                ]
            ]
        );
        $this->callModifyDataAndAssert($this->getProduct(), $expectedData);
    }

    /**
     * @return array
     */
    private function getAttributeMeta(): array
    {
        return [
            'visible' => '1',
            'required' => '0',
            'label' => 'fixed product tax',
            'code' => 'fixed_product_attribute',
            'source' => 'product-details',
            'scopeLabel' => '[GLOBAL]',
            'globalScope' => true,
            'sortOrder' => '__placeholder__',
            'componentType' => 'field',
        ];
    }
}
