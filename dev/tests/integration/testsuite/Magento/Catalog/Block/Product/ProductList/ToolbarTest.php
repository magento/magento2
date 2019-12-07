<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Product\ProductList;

class ToolbarTest extends \PHPUnit\Framework\TestCase
{
    public function testGetPagerHtml()
    {
        /** @var $layout \Magento\Framework\View\Layout */
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        );
        /** @var $block \Magento\Catalog\Block\Product\ProductList\Toolbar */
        $block = $layout->createBlock(\Magento\Catalog\Block\Product\ProductList\Toolbar::class, 'block');
        /** @var $childBlock \Magento\Framework\View\Element\Text */
        $childBlock = $layout->addBlock(
            \Magento\Framework\View\Element\Text::class,
            'product_list_toolbar_pager',
            'block'
        );

        $expectedHtml = '<b>Any text there</b>';
        $this->assertNotEquals($expectedHtml, $block->getPagerHtml());
        $childBlock->setText($expectedHtml);
        $this->assertEquals($expectedHtml, $block->getPagerHtml());
    }
}
