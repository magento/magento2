<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Test\Unit\Helper;

class MessageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutFactoryMock;

    /**
     * @var \Magento\GiftMessage\Helper\Message
     */
    protected $helper;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->layoutFactoryMock = $this->getMock(\Magento\Framework\View\LayoutFactory::class, [], [], '', false);

        $this->helper = $objectManager->getObject(
            \Magento\GiftMessage\Helper\Message::class,
            [
                'layoutFactory' => $this->layoutFactoryMock,
                'skipMessageCheck' => ['onepage_checkout']
            ]
        );
    }

    /**
     * Make sure that isMessagesAllowed is not called
     */
    public function testGetInlineForCheckout()
    {
        $expectedHtml = '<a href="here">here</a>';
        $layoutMock = $this->getMock(\Magento\Framework\View\Layout::class, [], [], '', false);
        $entityMock = $this->getMock(\Magento\Framework\DataObject::class, [], [], '', false);
        $inlineMock = $this->getMock(
            \Magento\GiftMessage\Block\Message\Inline::class,
            ['setId', 'setDontDisplayContainer', 'setEntity', 'setCheckoutType', 'toHtml'],
            [],
            '',
            false
        );

        $this->layoutFactoryMock->expects($this->once())->method('create')->will($this->returnValue($layoutMock));
        $layoutMock->expects($this->once())->method('createBlock')->will($this->returnValue($inlineMock));

        $inlineMock->expects($this->once())->method('setId')->will($this->returnSelf());
        $inlineMock->expects($this->once())->method('setDontDisplayContainer')->will($this->returnSelf());
        $inlineMock->expects($this->once())->method('setEntity')->with($entityMock)->will($this->returnSelf());
        $inlineMock->expects($this->once())->method('setCheckoutType')->will($this->returnSelf());
        $inlineMock->expects($this->once())->method('toHtml')->will($this->returnValue($expectedHtml));

        $this->assertEquals($expectedHtml, $this->helper->getInline('onepage_checkout', $entityMock));
    }
}
