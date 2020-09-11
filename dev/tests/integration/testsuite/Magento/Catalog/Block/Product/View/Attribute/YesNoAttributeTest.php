<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Product\View\Attribute;

/**
 * Class checks boolean attribute displaying on frontend
 *
 * @magentoDbIsolation enabled
 * @magentoDataFixture Magento/Catalog/_files/product_boolean_attribute.php
 * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
 */
class YesNoAttributeTest extends AbstractAttributeTest
{
    /**
     * @return void
     */
    public function testAttributeWithNonDefaultValueView(): void
    {
        $this->processNonDefaultAttributeValueView('simple2', '0', 'No');
    }

    /**
     * @return void
     */
    public function testAttributeWithDefaultValueView(): void
    {
        $this->processDefaultValueAttributeView('simple2', 'Yes');
    }

    /**
     * @inheritdoc
     */
    protected function getAttributeCode(): string
    {
        return 'boolean_attribute';
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultAttributeValue(): string
    {
        return '1';
    }
}
