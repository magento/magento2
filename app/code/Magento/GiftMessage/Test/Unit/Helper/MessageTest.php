<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Test\Unit\Helper;

class MessageTest extends \PHPUnit\Framework\TestCase
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
        $this->layoutFactoryMock = $this->createMock(\Magento\Framework\View\LayoutFactory::class);

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
        $layoutMock = $this->createMock(\Magento\Framework\View\Layout::class);
        $entityMock = $this->createMock(\Magento\Framework\DataObject::class);
        $inlineMock = $this->createPartialMock(
            \Magento\GiftMessage\Block\Message\Inline::class,
            ['setId', 'setDontDisplayContainer', 'setEntity', 'setCheckoutType', 'toHtml']
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
