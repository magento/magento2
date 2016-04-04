<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Block\Cart;

use \Magento\Checkout\Block\Cart\AbstractCart;

class AbstractCartTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
    }

    /**
     * @dataProvider getItemRendererDataProvider
     * @param string|null $type
     * @param string $expectedType
     */
    public function testGetItemRenderer($type, $expectedType)
    {
        $renderer = $this->getMock('Magento\Framework\View\Element\RendererList', [], [], '', false);

        $renderer->expects(
            $this->once()
        )->method(
            'getRenderer'
        )->with(
            $expectedType,
            AbstractCart::DEFAULT_TYPE
        )->will(
            $this->returnValue('rendererObject')
        );

        $layout = $this->getMock(
            'Magento\Framework\View\Layout',
            ['getChildName', 'getBlock'],
            [],
            '',
            false
        );

        $layout->expects($this->once())->method('getChildName')->will($this->returnValue('renderer.list'));

        $layout->expects(
            $this->once()
        )->method(
            'getBlock'
        )->with(
            'renderer.list'
        )->will(
            $this->returnValue($renderer)
        );

        /** @var $block \Magento\Sales\Block\Items\AbstractItems */
        $block = $this->_objectManager->getObject(
            'Magento\Checkout\Block\Cart\AbstractCart',
            [
                'context' => $this->_objectManager->getObject(
                    'Magento\Backend\Block\Template\Context',
                    ['layout' => $layout]
                )
            ]
        );

        $this->assertSame('rendererObject', $block->getItemRenderer($type));
    }

    /**
     * @return array
     */
    public function getItemRendererDataProvider()
    {
        return [[null, AbstractCart::DEFAULT_TYPE], ['some-type', 'some-type']];
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Renderer list for block "" is not defined
     */
    public function testGetItemRendererThrowsExceptionForNonexistentRenderer()
    {
        $layout = $this->getMock(
            'Magento\Framework\View\Layout',
            ['getChildName', 'getBlock'],
            [],
            '',
            false
        );
        $layout->expects($this->once())->method('getChildName')->will($this->returnValue(null));

        /** @var $block \Magento\Checkout\Block\Cart\AbstractCart */
        $block = $this->_objectManager->getObject(
            'Magento\Checkout\Block\Cart\AbstractCart',
            [
                'context' => $this->_objectManager->getObject(
                    'Magento\Backend\Block\Template\Context',
                    ['layout' => $layout]
                )
            ]
        );

        $block->getItemRenderer('some-type');
    }

    /**
     * @param array $expectedResult
     * @param bool $isVirtual
     * @dataProvider getTotalsCacheDataProvider
     */
    public function testGetTotalsCache($expectedResult, $isVirtual)
    {
        $totals = $isVirtual ? ['billing_totals'] : ['shipping_totals'];
        $addressMock = $this->getMock('Magento\Quote\Model\Quote\Address', [], [], '', false);
        $checkoutSessionMock = $this->getMock('Magento\Checkout\Model\Session', [], [], '', false);
        $quoteMock = $this->getMock('Magento\Quote\Model\Quote', [], [], '', false);
        $checkoutSessionMock->expects($this->once())->method('getQuote')->willReturn($quoteMock);

        $quoteMock->expects($this->once())->method('isVirtual')->willReturn($isVirtual);
        $quoteMock->expects($this->any())->method('getShippingAddress')->willReturn($addressMock);
        $quoteMock->expects($this->any())->method('getBillingAddress')->willReturn($addressMock);
        $addressMock->expects($this->once())->method('getTotals')->willReturn($totals);

        /** @var \Magento\Checkout\Block\Cart\AbstractCart $model */
        $model = $this->_objectManager->getObject(
            'Magento\Checkout\Block\Cart\AbstractCart',
            ['checkoutSession' => $checkoutSessionMock]
        );
        $this->assertEquals($expectedResult, $model->getTotalsCache());
    }

    public function getTotalsCacheDataProvider()
    {
        return [
            [['billing_totals'], true],
            [['shipping_totals'], false]
        ];
    }
}
