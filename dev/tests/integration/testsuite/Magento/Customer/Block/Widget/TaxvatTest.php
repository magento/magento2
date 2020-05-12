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
class TaxvatTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @magentoAppIsolation enabled
     */
    public function testToHtml()
    {
        /** @var \Magento\Customer\Block\Widget\Taxvat $block */
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Customer\Block\Widget\Taxvat::class
        );

        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Customer\Model\Attribute::class
        );
        $model->loadByCode('customer', 'taxvat');
        $attributeLabel = $model->getStoreLabel();

        $this->assertStringContainsString('title="' . $block->escapeHtmlAttr($attributeLabel) . '"', $block->toHtml());
        $this->assertStringNotContainsString('required', $block->toHtml());
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
        $model->loadByCode('customer', 'taxvat')->setIsRequired(true);
        $model->save();
        $attributeLabel = $model->getStoreLabel();

        /** @var \Magento\Customer\Block\Widget\Taxvat $block */
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Customer\Block\Widget\Taxvat::class
        );

        $this->assertStringContainsString('title="' . $block->escapeHtmlAttr($attributeLabel) . '"', $block->toHtml());
        $this->assertStringContainsString('required', $block->toHtml());
    }

    protected function tearDown(): void
    {
        /** @var \Magento\Eav\Model\Config $eavConfig */
        $eavConfig = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Eav\Model\Config::class);
        $eavConfig->clear();
    }
}
