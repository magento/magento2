<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Block\Adminhtml\Order\View;

class GiftmessageTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSaveButtonHtml()
    {
        $item = new \Magento\Framework\Object();
        $expectedHtml = 'some_value';

        /** @var $block \Magento\Sales\Block\Adminhtml\Order\View\Giftmessage */
        $block = $this->getMock(
            'Magento\Sales\Block\Adminhtml\Order\View\Giftmessage',
            ['getChildBlock', 'getChildHtml'],
            [],
            '',
            false
        );
        $block->setEntity(new \Magento\Framework\Object());
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
