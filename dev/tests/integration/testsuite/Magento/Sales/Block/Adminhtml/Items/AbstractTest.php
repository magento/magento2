<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Items;

/**
 * @magentoAppArea adminhtml
 */
class AbstractTest extends \PHPUnit_Framework_TestCase
{
    public function testGetItemExtraInfoHtml()
    {
        /** @var $layout \Magento\Framework\View\Layout */
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        );
        /** @var $block \Magento\Sales\Block\Adminhtml\Items\AbstractItems */
        $block = $layout->createBlock('Magento\Sales\Block\Adminhtml\Items\AbstractItems', 'block');

        $item = new \Magento\Framework\DataObject();

        $this->assertEmpty($block->getItemExtraInfoHtml($item));

        $expectedHtml = '<html><body>some data</body></html>';
        /** @var $childBlock \Magento\Framework\View\Element\Text */
        $childBlock = $layout->addBlock(
            'Magento\Framework\View\Element\Text',
            'other_block',
            'block',
            'order_item_extra_info'
        );
        $childBlock->setText($expectedHtml);

        $this->assertEquals($expectedHtml, $block->getItemExtraInfoHtml($item));
        $this->assertSame($item, $childBlock->getItem());
    }
}
