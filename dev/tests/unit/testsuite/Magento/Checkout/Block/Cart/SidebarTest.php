<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block\Cart;

class SidebarTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\TestFramework\Helper\ObjectManager */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
    }

    public function testDeserializeRenders()
    {
        $childBlock = $this->getMock('Magento\Framework\View\Element\AbstractBlock', [], [], '', false);
        /** @var $layout \Magento\Framework\View\LayoutInterface */
        $layout = $this->getMock(
            'Magento\Framework\View\Layout',
            ['createBlock', 'getChildName', 'setChild'],
            [],
            '',
            false
        );

        $rendererList = $this->_objectManager->getObject(
            'Magento\Checkout\Block\Cart\Sidebar',
            [
                'context' => $this->_objectManager->getObject(
                    'Magento\Backend\Block\Template\Context',
                    ['layout' => $layout]
                )
            ]
        );
        $layout->expects(
            $this->at(0)
        )->method(
            'createBlock'
        )->with(
            'Magento\Framework\View\Element\RendererList'
        )->will(
            $this->returnValue($rendererList)
        );
        $layout->expects(
            $this->at(4)
        )->method(
            'createBlock'
        )->with(
            'some-block',
            '.some-template',
            ['data' => ['template' => 'some-type']]
        )->will(
            $this->returnValue($childBlock)
        );
        $layout->expects(
            $this->at(5)
        )->method(
            'getChildName'
        )->with(
            null,
            'some-template'
        )->will(
            $this->returnValue(false)
        );
        $layout->expects($this->at(6))->method('setChild')->with(null, null, 'some-template');

        /** @var $block \Magento\Checkout\Block\Cart\Sidebar */
        $block = $this->_objectManager->getObject(
            'Magento\Checkout\Block\Cart\Sidebar',
            [
                'context' => $this->_objectManager->getObject(
                    'Magento\Backend\Block\Template\Context',
                    ['layout' => $layout]
                )
            ]
        );

        $block->deserializeRenders('some-template|some-block|some-type');
    }

    public function testGetIdentities()
    {
        /** @var $block \Magento\Checkout\Block\Cart\Sidebar */
        $block = $this->_objectManager->getObject('Magento\Checkout\Block\Cart\Sidebar');

        /** @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject $product */
        $product = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['__wakeup', 'getIdentities'],
            [],
            '',
            false
        );
        $identities = [0 => 1, 1 => 2, 2 => 3];
        $product->expects($this->exactly(2))
            ->method('getIdentities')
            ->will($this->returnValue($identities));

        /** @var \Magento\Sales\Model\Quote\Item|\PHPUnit_Framework_MockObject_MockObject $item */
        $item = $this->getMock('Magento\Sales\Model\Quote\Item', [], [], '', false);
        $item->expects($this->once())->method('getProduct')->will($this->returnValue($product));

        /** @var \Magento\Sales\Model\Quote|\PHPUnit_Framework_MockObject_MockObject $quote */
        $quote = $this->getMock('Magento\Sales\Model\Quote', [], [], '', false);
        $quote->expects($this->once())->method('getAllVisibleItems')->will($this->returnValue([$item]));

        $block->setData('custom_quote', $quote);
        $this->assertEquals($product->getIdentities(), $block->getIdentities());
    }

    public function testGetTotalsHtml()
    {
        $totalsHtml = "$134.36";
        $totalsBlockMock = $this->getMockBuilder('\Magento\Checkout\Block\Shipping\Price')
            ->disableOriginalConstructor()
            ->setMethods(['toHtml'])
            ->getMock();

        $totalsBlockMock->expects($this->once())
            ->method('toHtml')
            ->will($this->returnValue($totalsHtml));

        $layoutMock = $this->getMockBuilder('\Magento\Framework\View\Layout')
            ->disableOriginalConstructor()
            ->getMock();

        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('checkout.cart.minicart.totals')
            ->will($this->returnValue($totalsBlockMock));

        $contextMock = $this->getMockBuilder('\Magento\Framework\View\Element\Template\Context')
            ->disableOriginalConstructor()
            ->setMethods(['getLayout'])
            ->getMock();

        $contextMock->expects($this->once())
            ->method('getLayout')
            ->will($this->returnValue($layoutMock));

        /** @var \Magento\Checkout\Block\Cart\SideBar $sidebarBlock */
        $sidebarBlock = $this->_objectManager->getObject(
            'Magento\Checkout\Block\Cart\SideBar',
            ['context' => $contextMock]
        );

        $this->assertEquals($totalsHtml, $sidebarBlock->getTotalsHtml());
    }
}
