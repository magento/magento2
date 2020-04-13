<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Swatches\Ui\DataProvider\Product\Form\Modifier\Eav;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Eav\SelectAttributeTest;

/**
 * Provides tests for product form eav modifier with custom text swatch attribute.
 *
 * @magentoDataFixture Magento/Swatches/_files/product_text_swatch_attribute.php
 * @magentoDataFixture Magento/Catalog/_files/product_simple.php
 * @magentoDbIsolation enabled
 */
class TextSwatchAttributeTest extends SelectAttributeTest
{
    /**
     * @return void
     */
    public function testModifyMeta(): void
    {
        $this->callModifyMetaAndAssert(
            $this->getProduct(),
            $this->addMetaNesting($this->getAttributeMeta(), 'product-details', 'text_swatch_attribute')
        );
    }

    /**
     * @return void
     */
    public function testModifyData(): void
    {
        $product = $this->getProduct();
        $optionValue = $this->getOptionValueByLabel('text_swatch_attribute', 'Option 1');
        $attributeData = ['text_swatch_attribute' => $optionValue];
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
            'dataType' => 'select',
            'formElement' => 'select',
            'visible' => '1',
            'required' => '0',
            'label' => 'Text swatch attribute',
            'code' => 'text_swatch_attribute',
            'source' => 'product-details',
            'scopeLabel' => '[GLOBAL]',
            'globalScope' => true,
            'sortOrder' => '__placeholder__',
            'options' => $this->getAttributeOptions('text_swatch_attribute'),
            'componentType' => 'field',
        ];
    }
}
