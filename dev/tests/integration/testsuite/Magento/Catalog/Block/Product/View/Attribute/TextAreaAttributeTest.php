<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Product\View\Attribute;

/**
 * Class checks textarea attribute displaying on frontend
 *
 * @magentoDbIsolation enabled
 * @magentoDataFixture Magento/Catalog/_files/product_text_attribute.php
 * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
 */
class TextAreaAttributeTest extends AbstractAttributeTest
{
    /**
     * @return void
     */
    public function testAttributeView(): void
    {
        $attributeValue = 'Value for text area attribute';
        $this->processAttributeView('simple2', $attributeValue, $attributeValue);
    }

    /**
     * @return void
     */
    public function testAttributeWithNonDefaultValueView(): void
    {
        $attributeValue = 'Text area attribute value';
        $this->processNonDefaultAttributeValueView('simple2', $attributeValue, $attributeValue);
    }

    /**
     * @return void
     */
    public function testAttributeWithDefaultValueView(): void
    {
        $this->processDefaultValueAttributeView('simple2', $this->getDefaultAttributeValue());
    }

    /**
     * @inheritdic
     */
    protected function getAttributeCode(): string
    {
        return 'text_attribute';
    }

    /**
     * @inheritdic
     */
    protected function getDefaultAttributeValue(): string
    {
        return 'Default value for text area attribute';
    }
}
