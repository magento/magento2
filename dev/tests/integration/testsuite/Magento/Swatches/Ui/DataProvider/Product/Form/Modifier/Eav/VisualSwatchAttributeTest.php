<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Swatches\Ui\DataProvider\Product\Form\Modifier\Eav;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Eav\SelectAttributeTest;

/**
 * Provides tests for product form eav modifier with custom visual swatch attribute.
 *
 * @magentoDataFixture Magento/Swatches/_files/product_swatch_attribute.php
 * @magentoDataFixture Magento/Catalog/_files/product_simple.php
 * @magentoDbIsolation enabled
 */
class VisualSwatchAttributeTest extends SelectAttributeTest
{
    /**
     * @return void
     */
    public function testModifyMeta(): void
    {
        $expectedMeta = $this->addMetaNesting(
            $this->getAttributeMeta(),
            'product-details',
            'color_swatch'
        );
        $this->callModifyMetaAndAssert($this->getProduct(), $expectedMeta);
    }

    /**
     * @return void
     */
    public function testModifyData(): void
    {
        $product = $this->getProduct();
        $attributeData = [
            'color_swatch' => $this->getOptionValueByLabel('color_swatch', 'option 1')
        ];
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
            'dataType' => 'swatch_visual',
            'formElement' => 'swatch_visual',
            'visible' => '1',
            'required' => '1',
            'label' => 'Attribute ',
            'code' => 'color_swatch',
            'source' => 'product-details',
            'scopeLabel' => '[GLOBAL]',
            'globalScope' => true,
            'sortOrder' => '__placeholder__',
            'options' => $this->getAttributeOptions('color_swatch'),
            'componentType' => 'field',
            'validation' => [
                'required-entry' => true,
            ],
        ];
    }
}
