<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\GiftMessage\Helper;

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
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->layoutFactoryMock = $this->getMock('\Magento\Framework\View\LayoutFactory', [], [], '', false);

        $this->helper = $objectManager->getObject('Magento\GiftMessage\Helper\Message', [
            'layoutFactory' => $this->layoutFactoryMock,
            'skipMessageCheck' => ['onepage_checkout'],
        ]);
    }

    /**
     * Make sure that isMessagesAvailable is not called
     */
    public function testGetInlineForCheckout()
    {
        $expectedHtml = '<a href="here">here</a>';
        $layoutMock = $this->getMock('\Magento\Framework\View\Layout', [], [], '', false);
        $entityMock = $this->getMock('\Magento\Framework\Object', [], [], '', false);
        $inlineMock = $this->getMock(
            'Magento\GiftMessage\Block\Message\Inline',
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
