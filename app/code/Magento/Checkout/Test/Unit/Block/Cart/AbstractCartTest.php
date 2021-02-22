<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Block\Cart;

use \Magento\Checkout\Block\Cart\AbstractCart;

class AbstractCartTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;

    protected function setUp(): void
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
        $renderer = $this->createMock(\Magento\Framework\View\Element\RendererList::class);

        $renderer->expects(
            $this->once()
        )->method(
            'getRenderer'
        )->with(
            $expectedType,
            AbstractCart::DEFAULT_TYPE
        )->willReturn(
            'rendererObject'
        );

        $layout = $this->createPartialMock(\Magento\Framework\View\Layout::class, ['getChildName', 'getBlock']);

        $layout->expects($this->once())->method('getChildName')->willReturn('renderer.list');

        $layout->expects(
            $this->once()
        )->method(
            'getBlock'
        )->with(
            'renderer.list'
        )->willReturn(
            $renderer
        );

        /** @var $block \Magento\Sales\Block\Items\AbstractItems */
        $block = $this->_objectManager->getObject(
            \Magento\Checkout\Block\Cart\AbstractCart::class,
            [
                'context' => $this->_objectManager->getObject(
                    \Magento\Backend\Block\Template\Context::class,
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
     */
    public function testGetItemRendererThrowsExceptionForNonexistentRenderer()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Renderer list for block "" is not defined');

        $layout = $this->createPartialMock(\Magento\Framework\View\Layout::class, ['getChildName', 'getBlock']);
        $layout->expects($this->once())->method('getChildName')->willReturn(null);

        /** @var $block \Magento\Checkout\Block\Cart\AbstractCart */
        $block = $this->_objectManager->getObject(
            \Magento\Checkout\Block\Cart\AbstractCart::class,
            [
                'context' => $this->_objectManager->getObject(
                    \Magento\Backend\Block\Template\Context::class,
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
        $addressMock = $this->createMock(\Magento\Quote\Model\Quote\Address::class);
        $checkoutSessionMock = $this->createMock(\Magento\Checkout\Model\Session::class);
        $quoteMock = $this->createMock(\Magento\Quote\Model\Quote::class);
        $checkoutSessionMock->expects($this->once())->method('getQuote')->willReturn($quoteMock);

        $quoteMock->expects($this->once())->method('isVirtual')->willReturn($isVirtual);
        $quoteMock->expects($this->any())->method('getShippingAddress')->willReturn($addressMock);
        $quoteMock->expects($this->any())->method('getBillingAddress')->willReturn($addressMock);
        $addressMock->expects($this->once())->method('getTotals')->willReturn($totals);

        /** @var \Magento\Checkout\Block\Cart\AbstractCart $model */
        $model = $this->_objectManager->getObject(
            \Magento\Checkout\Block\Cart\AbstractCart::class,
            ['checkoutSession' => $checkoutSessionMock]
        );
        $this->assertEquals($expectedResult, $model->getTotalsCache());
    }

    /**
     * @return array
     */
    public function getTotalsCacheDataProvider()
    {
        return [
            [['billing_totals'], true],
            [['shipping_totals'], false]
        ];
    }
}
