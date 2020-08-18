<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Product\View\Attribute;

/**
 * Class checks date attribute displaying on frontend
 *
 * @magentoDbIsolation disabled
 * @magentoDataFixture Magento/Catalog/_files/product_date_attribute.php
 * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
 */
class DateAttributeTest extends AbstractAttributeTest
{
    /**
     * @magentoConfigFixture default_store general/locale/timezone UTC
     * @return void
     */
    public function testAttributeView(): void
    {
        $attributeValue = $this->getAttribute()->getBackend()->formatDate('12/20/19');
        $this->processAttributeView('simple2', $attributeValue, 'Dec 20, 2019');
    }

    /**
     * @magentoConfigFixture default_store general/locale/timezone UTC
     * @return void
     */
    public function testAttributeWithNonDefaultValueView(): void
    {
        $attributeValue = $this->getAttribute()->getBackend()->formatDate('12/20/19');
        $this->processNonDefaultAttributeValueView('simple2', $attributeValue, 'Dec 20, 2019');
    }

    /**
     * @magentoConfigFixture default_store general/locale/timezone UTC
     * @return void
     */
    public function testAttributeWithDefaultValueView(): void
    {
        $this->markTestSkipped('Test is blocked by issue MC-28950');
        $this->processDefaultValueAttributeView('simple2', $this->getDefaultAttributeValue());
    }

    /**
     * @inheritdoc
     */
    protected function getAttributeCode(): string
    {
        return 'date_attribute';
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultAttributeValue(): string
    {
        return $this->getAttribute()->getBackend()->formatDate('11/20/19');
    }
}
