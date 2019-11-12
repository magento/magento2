<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Eav;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractEavTest;

/**
 * Provides tests for product form eav modifier with custom multiselect attribute.
 *
 * @magentoDataFixture Magento/Catalog/_files/multiselect_attribute.php
 * @magentoDataFixture Magento/Catalog/_files/product_simple.php
 * @magentoDbIsolation enabled
 */
class MultiselectAttributeTest extends AbstractEavTest
{
    /**
     * @return void
     */
    public function testModifyMeta(): void
    {
        $this->callModifyMetaAndAssert(
            $this->getProduct(),
            $this->addMetaNesting($this->getAttributeMeta(), 'product-details', 'multiselect_attribute')
        );
    }

    /**
     * @return void
     */
    public function testModifyData(): void
    {
        $product = $this->getProduct();
        $optionValue = $this->getOptionValueByLabel('multiselect_attribute', 'Option 3');
        $attributeData = ['multiselect_attribute' => $optionValue];
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
            'dataType' => 'multiselect',
            'formElement' => 'multiselect',
            'visible' => '1',
            'required' => '0',
            'label' => 'Multiselect Attribute',
            'code' => 'multiselect_attribute',
            'source' => 'product-details',
            'scopeLabel' => '[GLOBAL]',
            'globalScope' => true,
            'sortOrder' => '__placeholder__',
            'options' => $this->getAttributeOptions('multiselect_attribute'),
            'componentType' => 'field',
        ];
    }
}
