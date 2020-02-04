<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Swatches\Block\Product\View\Attribute;

use Magento\Catalog\Block\Product\View\Attribute\AbstractAttributeTest;

/**
 * Class checks text attribute displaying on frontend
 *
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 * @magentoDataFixture Magento/Swatches/_files/product_text_swatch_attribute.php
 * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
 */
class TextSwatchAttributeTest extends AbstractAttributeTest
{
    /**
     * @return void
     */
    public function testAttributeView(): void
    {
        $attributeValue = $this->getAttribute()->getSource()->getOptionId('Option 2');
        $this->processAttributeView('simple2', $attributeValue, 'Option 2');
    }

    /**
     * @return void
     */
    public function testAttributeWithNonDefaultValueView(): void
    {
        $attributeValue = $this->getAttribute()->getSource()->getOptionId('Option 2');
        $this->processNonDefaultAttributeValueView('simple2', $attributeValue, 'Option 2');
    }

    /**
     * @return void
     */
    public function tesAttributeWithDefaultValueView(): void
    {
        $this->processDefaultValueAttributeView('simple2', 'Option 1');
    }

    /**
     * @inheritdoc
     */
    protected function getAttributeCode(): string
    {
        return 'text_swatch_attribute';
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultAttributeValue(): string
    {
        return $this->getAttribute()->getSource()->getOptionId('Option 1');
    }
}
