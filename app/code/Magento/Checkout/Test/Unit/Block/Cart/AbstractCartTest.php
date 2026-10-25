<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Block\Cart;

use Magento\Backend\Block\Template\Context;
use Magento\Checkout\Block\Cart\AbstractCart;
use Magento\Checkout\Block\Cart\Item\Renderer as ItemRenderer;
use Magento\Checkout\Model\Session;
use Magento\Checkout\Observer\CatalogRuleSaveAfterObserver;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\RendererList;
use Magento\Framework\View\Layout;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Sales\Block\Items\AbstractItems;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
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
     * @param string|null $type
     * @param string $expectedType
     */
    #[DataProvider('getItemRendererDataProvider')]
    public function testGetItemRenderer($type, $expectedType)
    {
        $renderer = $this->createMock(RendererList::class);

        $renderer->expects(
            $this->once()
        )->method(
            'getRenderer'
        )->with(
            $expectedType,
            AbstractCart::DEFAULT_TYPE,
            $this->anything()
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
    public static function getItemRendererDataProvider()
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

    public function testGetItemHtmlReturnsRendererHtml(): void
    {
        $expectedHtml = 'item html';

        $itemMock = $this->createMock(QuoteItem::class);
        $itemMock->expects($this->once())
            ->method('getProductType')
            ->willReturn('simple');

        $rendererBlockMock = $this->createPartialMock(ItemRenderer::class, ['setItem', 'toHtml']);
        $rendererBlockMock->expects($this->once())
            ->method('setItem')
            ->with($itemMock)
            ->willReturnSelf();
        $rendererBlockMock->expects($this->once())
            ->method('toHtml')
            ->willReturn($expectedHtml);

        $rendererListMock = $this->createMock(RendererList::class);
        $getRendererArgs = [];
        $rendererListMock->expects($this->once())
            ->method('getRenderer')
            ->willReturnCallback(function (...$args) use (&$getRendererArgs, $rendererBlockMock) {
                $getRendererArgs = $args;
                return $rendererBlockMock;
            });

        $layout = $this->createPartialMock(Layout::class, ['getChildName', 'getBlock']);
        $layout->expects($this->once())->method('getChildName')->willReturn('renderer.list');
        $layout->expects($this->once())
            ->method('getBlock')
            ->with('renderer.list')
            ->willReturn($rendererListMock);

        /** @var AbstractCart $block */
        $block = $this->_objectManager->getObject(
            AbstractCart::class,
            [
                'context' => $this->_objectManager->getObject(
                    Context::class,
                    ['layout' => $layout]
                )
            ]
        );

        $this->assertSame($expectedHtml, $block->getItemHtml($itemMock));
        $this->assertSame('simple', $getRendererArgs[0] ?? null);
        $this->assertSame(AbstractCart::DEFAULT_TYPE, $getRendererArgs[1] ?? null);
    }

    /**
     * @param array $expectedResult
     * @param bool $isVirtual
     */
    #[DataProvider('getTotalsCacheDataProvider')]
    public function testGetTotalsCache($expectedResult, $isVirtual)
    {
        $totals = $isVirtual ? ['billing_totals'] : ['shipping_totals'];
        $addressMock = $this->createMock(Address::class);
        $checkoutSessionMock = $this->createMock(Session::class);
        $quoteMock = $this->createMock(Quote::class);
        $cacheMock = $this->createMock(CacheInterface::class);
        $cacheMock->method('load')->willReturn(false);
        $checkoutSessionMock->expects($this->once())->method('getQuote')->willReturn($quoteMock);

        $quoteMock->expects($this->once())->method('getId')->willReturn(null);
        $quoteMock->expects($this->once())->method('isVirtual')->willReturn($isVirtual);
        $quoteMock->method('getShippingAddress')->willReturn($addressMock);
        $quoteMock->method('getBillingAddress')->willReturn($addressMock);
        $addressMock->expects($this->once())->method('getTotals')->willReturn($totals);

        /** @var \Magento\Checkout\Block\Cart\AbstractCart $model */
        $model = $this->_objectManager->getObject(
            AbstractCart::class,
            ['checkoutSession' => $checkoutSessionMock, 'cache' => $cacheMock]
        );
        $this->assertEquals($expectedResult, $model->getTotalsCache());
    }

    public function testGetTotalsReturnsSameAsGetTotalsCache(): void
    {
        $totals = ['grand_total' => []];
        $addressMock = $this->createMock(Address::class);
        $checkoutSessionMock = $this->createMock(Session::class);
        $quoteMock = $this->createMock(Quote::class);
        $cacheMock = $this->createMock(CacheInterface::class);
        $cacheMock->method('load')->willReturn(false);
        $checkoutSessionMock->method('getQuote')->willReturn($quoteMock);
        $quoteMock->method('getId')->willReturn(null);
        $quoteMock->method('isVirtual')->willReturn(true);
        $quoteMock->method('getBillingAddress')->willReturn($addressMock);
        $addressMock->method('getTotals')->willReturn($totals);

        /** @var AbstractCart $model */
        $model = $this->_objectManager->getObject(
            AbstractCart::class,
            ['checkoutSession' => $checkoutSessionMock, 'cache' => $cacheMock]
        );
        $this->assertSame($model->getTotalsCache(), $model->getTotals());
    }

    /**
     * @return array
     */
    public static function getTotalsCacheDataProvider()
    {
        return [
            [['billing_totals'], true],
            [['shipping_totals'], false]
        ];
    }

    public function testGetQuoteRecollectsTotalsWhenCatalogRuleCacheIsNewerThanSession(): void
    {
        $cacheTimestamp = '1700000500';
        $cacheMock = $this->createMock(CacheInterface::class);
        $cacheMock->expects($this->exactly(2))
            ->method('load')
            ->with(CatalogRuleSaveAfterObserver::CACHE_KEY_CATALOG_RULES_UPDATED_AT)
            ->willReturn($cacheTimestamp);

        $checkoutSessionMock = $this->createMock(Session::class);
        $quoteMock = $this->createPartialMock(
            Quote::class,
            [
                'getId',
                'getItemsCount',
                'getItemsQty',
                'getData',
                'collectTotals',
                'setItemsCount',
                'setItemsQty',
            ]
        );

        $checkoutSessionMock->expects($this->once())
            ->method('getQuote')
            ->willReturn($quoteMock);
        $checkoutSessionMock->expects($this->once())
            ->method('getData')
            ->with('last_cart_totals_recollect_at')
            ->willReturn(1700000000);
        // Session::setData is final; recollect path still exercised via collectTotals.

        $quoteMock->expects($this->once())->method('getId')->willReturn(123);
        $quoteMock->method('getItemsCount')->willReturn(2);
        $quoteMock->method('getItemsQty')->willReturn(2.0);
        $quoteMock->method('getData')->willReturnMap([['virtual_items_qty', null, 1]]);
        $quoteMock->expects($this->once())->method('collectTotals')->willReturnSelf();
        $quoteMock->expects($this->once())->method('setItemsCount')->with(2)->willReturnSelf();
        $quoteMock->expects($this->once())->method('setItemsQty')->with(2.0)->willReturnSelf();

        /** @var AbstractCart $model */
        $model = $this->_objectManager->getObject(
            AbstractCart::class,
            [
                'checkoutSession' => $checkoutSessionMock,
                'cache' => $cacheMock,
            ]
        );

        $this->assertSame($quoteMock, $model->getQuote());
    }

    public function testGetQuoteRecollectsTotalsWhenSessionNeverRecordedRecollect(): void
    {
        $cacheTimestamp = '1700000500';
        $cacheMock = $this->createMock(CacheInterface::class);
        $cacheMock->method('load')->willReturn($cacheTimestamp);

        $checkoutSessionMock = $this->createMock(Session::class);
        $quoteMock = $this->createPartialMock(
            Quote::class,
            [
                'getId',
                'getItemsCount',
                'getItemsQty',
                'getData',
                'collectTotals',
                'setItemsCount',
                'setItemsQty',
            ]
        );

        $checkoutSessionMock->method('getQuote')->willReturn($quoteMock);
        $checkoutSessionMock->method('getData')->with('last_cart_totals_recollect_at')->willReturn(null);

        $quoteMock->method('getId')->willReturn(1);
        $quoteMock->method('getItemsCount')->willReturn(0);
        $quoteMock->method('getItemsQty')->willReturn(0.0);
        $quoteMock->method('getData')->willReturnMap([['virtual_items_qty', null, null]]);
        $quoteMock->expects($this->once())->method('collectTotals')->willReturnSelf();
        $quoteMock->expects($this->once())->method('setItemsCount')->with(0)->willReturnSelf();
        $quoteMock->expects($this->once())->method('setItemsQty')->with(0.0)->willReturnSelf();

        /** @var AbstractCart $model */
        $model = $this->_objectManager->getObject(
            AbstractCart::class,
            [
                'checkoutSession' => $checkoutSessionMock,
                'cache' => $cacheMock,
            ]
        );

        $model->getQuote();
    }

    public function testGetQuoteDoesNotRecollectTotalsWhenQuoteIsNotPersisted(): void
    {
        $checkoutSessionMock = $this->createMock(Session::class);
        $quoteMock = $this->createPartialMock(
            Quote::class,
            ['getId', 'collectTotals']
        );
        $cacheMock = $this->createMock(CacheInterface::class);
        $cacheMock->method('load')->willReturn('9999999999');

        $checkoutSessionMock->expects($this->once())
            ->method('getQuote')
            ->willReturn($quoteMock);

        $quoteMock->expects($this->once())
            ->method('getId')
            ->willReturn(null);
        $quoteMock->expects($this->never())->method('collectTotals');

        /** @var AbstractCart $model */
        $model = $this->_objectManager->getObject(
            AbstractCart::class,
            ['checkoutSession' => $checkoutSessionMock, 'cache' => $cacheMock]
        );

        $this->assertSame($quoteMock, $model->getQuote());
    }

    public function testGetQuoteDoesNotRecollectTotalsWhenCatalogRuleCacheIsEmpty(): void
    {
        $checkoutSessionMock = $this->createMock(Session::class);
        $quoteMock = $this->createPartialMock(Quote::class, ['getId', 'collectTotals']);
        $cacheMock = $this->createMock(CacheInterface::class);
        $cacheMock->method('load')->willReturn(false);

        $checkoutSessionMock->method('getQuote')->willReturn($quoteMock);
        $quoteMock->expects($this->once())->method('getId')->willReturn(99);
        $quoteMock->expects($this->never())->method('collectTotals');
        $checkoutSessionMock->expects($this->never())->method('getData');

        /** @var AbstractCart $model */
        $model = $this->_objectManager->getObject(
            AbstractCart::class,
            ['checkoutSession' => $checkoutSessionMock, 'cache' => $cacheMock]
        );

        $this->assertSame($quoteMock, $model->getQuote());
    }

    public function testGetQuoteDoesNotRecollectTotalsWhenSessionAlreadySyncedWithCache(): void
    {
        $checkoutSessionMock = $this->createMock(Session::class);
        $quoteMock = $this->createPartialMock(Quote::class, ['getId', 'collectTotals']);
        $cacheMock = $this->createMock(CacheInterface::class);
        $cacheMock->method('load')->willReturn('1700000000');

        $checkoutSessionMock->method('getQuote')->willReturn($quoteMock);
        $checkoutSessionMock->method('getData')->with('last_cart_totals_recollect_at')->willReturn('1700000000');

        $quoteMock->expects($this->once())->method('getId')->willReturn(123);
        $quoteMock->expects($this->never())->method('collectTotals');

        /** @var AbstractCart $model */
        $model = $this->_objectManager->getObject(
            AbstractCart::class,
            [
                'checkoutSession' => $checkoutSessionMock,
                'cache' => $cacheMock,
            ]
        );

        $this->assertSame($quoteMock, $model->getQuote());
    }

    public function testGetItemsReturnsVisibleQuoteItems(): void
    {
        $items = [$this->createMock(QuoteItem::class)];
        $quoteMock = $this->createMock(Quote::class);
        $quoteMock->method('getId')->willReturn(null);
        $quoteMock->expects($this->once())->method('getAllVisibleItems')->willReturn($items);

        $checkoutSessionMock = $this->createMock(Session::class);
        $checkoutSessionMock->method('getQuote')->willReturn($quoteMock);

        $cacheMock = $this->createMock(CacheInterface::class);
        $cacheMock->method('load')->willReturn(false);

        /** @var AbstractCart $model */
        $model = $this->_objectManager->getObject(
            AbstractCart::class,
            ['checkoutSession' => $checkoutSessionMock, 'cache' => $cacheMock]
        );

        $this->assertSame($items, $model->getItems());
    }
}
