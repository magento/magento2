<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Block\Adminhtml\Order\View;

class GiftmessageTest extends \PHPUnit\Framework\TestCase
{
    public function testGetSaveButtonHtml()
    {
        $item = new \Magento\Framework\DataObject();
        $expectedHtml = 'some_value';

        /** @var $block \Magento\Sales\Block\Adminhtml\Order\View\Giftmessage */
        $block = $this->createPartialMock(
            \Magento\Sales\Block\Adminhtml\Order\View\Giftmessage::class,
            ['getChildBlock', 'getChildHtml']
        );
        $block->setEntity(new \Magento\Framework\DataObject());
        $block->expects($this->once())->method('getChildBlock')->with('save_button')->will($this->returnValue($item));
        $block->expects(
            $this->once()
        )->method(
            'getChildHtml'
        )->with(
            'save_button'
        )->will(
            $this->returnValue($expectedHtml)
        );

        $this->assertEquals($expectedHtml, $block->getSaveButtonHtml());
        $this->assertNotEmpty($item->getOnclick());
    }
}
