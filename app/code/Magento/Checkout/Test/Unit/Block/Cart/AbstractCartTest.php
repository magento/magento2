<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Block\Cart;

use Magento\Backend\Block\Template\Context;
use Magento\Checkout\Block\Cart\AbstractCart;
use Magento\Checkout\Model\Session;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\RendererList;
use Magento\Framework\View\Layout;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Sales\Block\Items\AbstractItems;
use PHPUnit\Framework\TestCase;

class AbstractCartTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $_objectManager;

    protected function setUp(): void
    {
        $this->_objectManager = new ObjectManager($this);
    }

    /**
     * @dataProvider getItemRendererDataProvider
     * @param string|null $type
     * @param string $expectedType
     */
    public function testGetItemRenderer($type, $expectedType)
    {
        $renderer = $this->createMock(RendererList::class);

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

        $layout = $this->createPartialMock(Layout::class, ['getChildName', 'getBlock']);

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

        /** @var AbstractItems $block */
        $block = $this->_objectManager->getObject(
            AbstractCart::class,
            [
                'context' => $this->_objectManager->getObject(
                    Context::class,
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

    public function testGetItemRendererThrowsExceptionForNonexistentRenderer()
    {
        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('Renderer list for block "" is not defined');
        $layout = $this->createPartialMock(Layout::class, ['getChildName', 'getBlock']);
        $layout->expects($this->once())->method('getChildName')->willReturn(null);

        /** @var \Magento\Checkout\Block\Cart\AbstractCart $block */
        $block = $this->_objectManager->getObject(
            AbstractCart::class,
            [
                'context' => $this->_objectManager->getObject(
                    Context::class,
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
        $addressMock = $this->createMock(Address::class);
        $checkoutSessionMock = $this->createMock(Session::class);
        $quoteMock = $this->createMock(Quote::class);
        $checkoutSessionMock->expects($this->once())->method('getQuote')->willReturn($quoteMock);

        $quoteMock->expects($this->once())->method('isVirtual')->willReturn($isVirtual);
        $quoteMock->expects($this->any())->method('getShippingAddress')->willReturn($addressMock);
        $quoteMock->expects($this->any())->method('getBillingAddress')->willReturn($addressMock);
        $addressMock->expects($this->once())->method('getTotals')->willReturn($totals);

        /** @var \Magento\Checkout\Block\Cart\AbstractCart $model */
        $model = $this->_objectManager->getObject(
            AbstractCart::class,
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
