<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid;

/**
 * @magentoAppArea adminhtml
 */
class ItemTest extends \PHPUnit\Framework\TestCase
{
    public function testGetAdditionalActionBlock()
    {
        /** @var $layout \Magento\Framework\View\Layout */
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        );
        /** @var $block \Magento\Backend\Block\Widget\Grid\Massaction\Item */
        $block = $layout->createBlock(\Magento\Backend\Block\Widget\Grid\Massaction\Item::class, 'block');
        $expected = $layout->addBlock(\Magento\Framework\View\Element\Template::class, 'additional_action', 'block');
        $this->assertSame($expected, $block->getAdditionalActionBlock());
    }
}
