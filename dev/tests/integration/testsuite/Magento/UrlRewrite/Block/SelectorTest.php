<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Block;

/**
 * Test for \Magento\UrlRewrite\Block\Selector
 * @magentoAppArea adminhtml
 */
class SelectorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @magentoAppIsolation enabled
     */
    public function testGetModeUrl()
    {
        /** @var $layout \Magento\Framework\View\LayoutInterface */
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        );

        /** @var $block \Magento\UrlRewrite\Block\Selector */
        $block = $layout->createBlock(\Magento\UrlRewrite\Block\Selector::class);

        $modeUrl = $block->getModeUrl('mode');
        $this->assertEquals(1, preg_match('/admin\/index\/index\/key\/[0-9a-zA-Z]+\/mode/', $modeUrl));
    }
}
