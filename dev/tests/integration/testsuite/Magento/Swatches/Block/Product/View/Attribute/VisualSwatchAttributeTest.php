<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Swatches\Block\Product\View\Attribute;

use Magento\Catalog\Block\Product\View\Attribute\AbstractAttributeTest;

/**
 * Class checks visual swatch attribute displaying on frontend
 *
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 * @magentoDataFixture Magento/Swatches/_files/product_visual_swatch_attribute.php
 * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
 */
class VisualSwatchAttributeTest extends AbstractAttributeTest
{
    /**
     * @return void
     */
    public function testAttributeView(): void
    {
        $attributeValue = $this->getAttribute()->getSource()->getOptionId('option 2');
        $this->processAttributeView('simple2', $attributeValue, 'option 2');
    }

    /**
     * @return void
     */
    public function testAttributeWithNonDefaultValueView(): void
    {
        $attributeValue = $this->getAttribute()->getSource()->getOptionId('option 2');
        $this->processNonDefaultAttributeValueView('simple2', $attributeValue, 'option 2');
    }

    /**
     * @return void
     */
    public function tesAttributeWithDefaultValueView(): void
    {
        $this->processDefaultValueAttributeView('simple2', 'option 1');
    }

    /**
     * @inheritdoc
     */
    protected function getAttributeCode(): string
    {
        return 'visual_swatch_attribute';
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultAttributeValue(): string
    {
        return $this->getAttribute()->getSource()->getOptionId('option 1');
    }
}
