<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Eav;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractEavTest;

/**
 * Provides tests for product form eav modifier with custom select attribute.
 *
 * @magentoDataFixture Magento/Catalog/_files/dropdown_attribute.php
 * @magentoDataFixture Magento/Catalog/_files/product_simple.php
 * @magentoDbIsolation enabled
 */
class SelectAttributeTest extends AbstractEavTest
{
    /**
     * @return void
     */
    public function testModifyMeta(): void
    {
        $this->callModifyMetaAndAssert(
            $this->getProduct(),
            $this->addMetaNesting($this->getAttributeMeta(), 'product-details', 'dropdown_attribute')
        );
    }

    /**
     * @return void
     */
    public function testModifyData(): void
    {
        $product = $this->getProduct();
        $attributeData = [
            'dropdown_attribute' => $this->getOptionValueByLabel('dropdown_attribute', 'Option 3')
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
            'dataType' => 'select',
            'formElement' => 'select',
            'visible' => '1',
            'required' => '0',
            'label' => 'Drop-Down Attribute',
            'code' => 'dropdown_attribute',
            'source' => 'product-details',
            'scopeLabel' => '[STORE VIEW]',
            'globalScope' => false,
            'sortOrder' => '__placeholder__',
            'options' => $this->getAttributeOptions('dropdown_attribute'),
            'componentType' => 'field',
        ];
    }
}
