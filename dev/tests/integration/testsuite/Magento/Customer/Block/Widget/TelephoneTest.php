<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Widget;

/**
 * Test class for \Magento\Customer\Block\Widget\Taxvat
 *
 * @magentoAppArea frontend
 */
class TelephoneTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @magentoAppIsolation enabled
     */
    public function testToHtml()
    {
        /** @var \Magento\Customer\Block\Widget\Telephone $block */
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Customer\Block\Widget\Telephone::class
        );

        $this->assertStringContainsString('title="Phone&#x20;Number"', $block->toHtml());
        $this->assertStringContainsString('required', $block->toHtml());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testToHtmlRequired()
    {
        /** @var \Magento\Customer\Model\Attribute $model */
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Customer\Model\Attribute::class
        );
        $model->loadByCode('customer_address', 'telephone')->setIsRequired(false);
        $model->save();

        /** @var \Magento\Customer\Block\Widget\Telephone $block */
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Customer\Block\Widget\Telephone::class
        );

        $this->assertStringContainsString('title="Phone&#x20;Number"', $block->toHtml());
        $this->assertStringNotContainsString('required', $block->toHtml());
    }

    protected function tearDown(): void
    {
        /** @var \Magento\Eav\Model\Config $eavConfig */
        $eavConfig = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Eav\Model\Config::class);
        $eavConfig->clear();
    }
}
