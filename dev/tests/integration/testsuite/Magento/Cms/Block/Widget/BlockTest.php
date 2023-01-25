<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Block\Widget;

class BlockTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @magentoDataFixture Magento/Cms/_files/block.php
     * @magentoDataFixture Magento/Variable/_files/variable.php
     * @magentoConfigFixture current_store web/unsecure/base_url http://example.com/
     * @magentoConfigFixture current_store web/unsecure/base_link_url http://example.com/
     */
    public function testToHtml()
    {
        $cmsBlock = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Cms\Model\Block::class
        );
        $cmsBlock->load('fixture_block', 'identifier');
        /** @var $block \Magento\Cms\Block\Widget\Block */
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        )->createBlock(
            \Magento\Cms\Block\Widget\Block::class
        );
        $block->setBlockId($cmsBlock->getId());
        $block->toHtml();
        $result = $block->getText();
        $this->assertStringContainsString('<a href="http://example.com/', $result);
        $this->assertStringContainsString('<p>Config value: "http://example.com/".</p>', $result);
        $this->assertStringContainsString('<p>Custom variable: "HTML Value".</p>', $result);
        $this->assertSame($cmsBlock->getIdentities(), $block->getIdentities());
    }
}
