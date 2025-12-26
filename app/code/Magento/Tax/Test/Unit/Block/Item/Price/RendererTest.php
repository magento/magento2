<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Block\Item\Price;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\Render;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice\Item as InvoiceItem;
use Magento\Sales\Model\Order\Item;
use Magento\Store\Model\Store;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Tax\Block\Item\Price\Renderer;
use Magento\Tax\Helper\Data;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RendererTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var Renderer
     */
    protected $renderer;

    /**
     * @var Data|MockObject
     */
    protected $taxHelper;

    /**
     * @var PriceCurrencyInterface|MockObject
     */
    protected $priceCurrency;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->priceCurrency = $this->getMockBuilder(
            PriceCurrencyInterface::class
        )->getMock();
        $this->taxHelper = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'displayCartPriceExclTax',
                'displayCartBothPrices',
                'displayCartPriceInclTax',
                'displaySalesPriceExclTax',
                'displaySalesBothPrices',
                'displaySalesPriceInclTax',
            ])
            ->getMock();

        $this->renderer = $objectManager->getObject(
            Renderer::class,
            [
                'taxHelper' => $this->taxHelper,
                'priceCurrency' => $this->priceCurrency,
                'data' => [
                    'zone' => Render::ZONE_CART,
                ]
            ]
        );
    }

    /**
     * @param $storeId
     * @return MockObject|Item
     */
    protected function getItemMockWithStoreId($storeId)
    {
        $itemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getStoreId', '__wakeup'])
            ->getMock();

        $itemMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);

        return $itemMock;
    }

    /**
     * Test displayPriceInclTax
     *
     * @param string $zone
     * @param string $methodName
     */
    #[DataProvider('displayPriceInclTaxDataProvider')]
    public function testDisplayPriceInclTax(string $zone, string $methodName): void
    {
        $storeId = 1;
        $flag = true;

        $itemMock = $this->getItemMockWithStoreId($storeId);
        $this->renderer->setItem($itemMock);
        $this->renderer->setZone($zone);
        $this->taxHelper->expects($this->once())
            ->method($methodName)
            ->with($storeId)
            ->willReturn($flag);

        $this->assertEquals($flag, $this->renderer->displayPriceInclTax());
    }

    /**
     * @return array
     */
    public static function displayPriceInclTaxDataProvider()
    {
        $data = [
            'cart' => [
                'zone' => Render::ZONE_CART,
                'methodName' => 'displayCartPriceInclTax',
            ],
            'anythingelse' => [
                'zone' => 'anythingelse',
                'methodName' => 'displayCartPriceInclTax',
            ],
            'sale' => [
                'zone' => Render::ZONE_SALES,
                'methodName' => 'displaySalesPriceInclTax',
            ],
            'email' => [
                'zone' => Render::ZONE_EMAIL,
                'methodName' => 'displaySalesPriceInclTax',
            ],
        ];

        return $data;
    }

    /**
     * Test displayPriceExclTax
     *
     * @param string $zone
     * @param string $methodName
     */
    #[DataProvider('displayPriceExclTaxDataProvider')]
    public function testDisplayPriceExclTax(string $zone, string $methodName): void
    {
        $storeId = 1;
        $flag = true;

        $itemMock = $this->getItemMockWithStoreId($storeId);
        $this->renderer->setItem($itemMock);
        $this->renderer->setZone($zone);
        $this->taxHelper->expects($this->once())
            ->method($methodName)
            ->with($storeId)
            ->willReturn($flag);

        $this->assertEquals($flag, $this->renderer->displayPriceExclTax());
    }

    /**
     * @return array
     */
    public static function displayPriceExclTaxDataProvider()
    {
        $data = [
            'cart' => [
                'zone' => Render::ZONE_CART,
                'methodName' => 'displayCartPriceExclTax',
            ],
            'anythingelse' => [
                'zone' => 'anythingelse',
                'methodName' => 'displayCartPriceExclTax',
            ],
            'sale' => [
                'zone' => Render::ZONE_SALES,
                'methodName' => 'displaySalesPriceExclTax',
            ],
            'email' => [
                'zone' => Render::ZONE_EMAIL,
                'methodName' => 'displaySalesPriceExclTax',
            ],
        ];

        return $data;
    }

    /**
     * Test displayBothPrices
     *
     * @param string $zone
     * @param string $methodName
     */
    #[DataProvider('displayBothPricesDataProvider')]
    public function testDisplayBothPrices(string $zone, string $methodName): void
    {
        $storeId = 1;
        $flag = true;

        $itemMock = $this->getItemMockWithStoreId($storeId);
        $this->renderer->setItem($itemMock);
        $this->renderer->setZone($zone);
        $this->taxHelper->expects($this->once())
            ->method($methodName)
            ->with($storeId)
            ->willReturn($flag);

        $this->assertEquals($flag, $this->renderer->displayBothPrices());
    }

    /**
     * @return array
     */
    public static function displayBothPricesDataProvider()
    {
        $data = [
            'cart' => [
                'zone' => Render::ZONE_CART,
                'methodName' => 'displayCartBothPrices',
            ],
            'anythingelse' => [
                'zone' => 'anythingelse',
                'methodName' => 'displayCartBothPrices',
            ],
            'sale' => [
                'zone' => Render::ZONE_SALES,
                'methodName' => 'displaySalesBothPrices',
            ],
            'email' => [
                'zone' => Render::ZONE_EMAIL,
                'methodName' => 'displaySalesBothPrices',
            ],
        ];

        return $data;
    }

    public function testFormatPriceQuoteItem(): void
    {
        $price = 3.554;
        $formattedPrice = "$3.55";

        $storeMock = $this->createPartialMockWithReflection(
            Store::class,
            ['formatPrice', '__wakeup']
        );

        $this->priceCurrency->expects($this->once())
            ->method('format')
            ->with($price, true)
            ->willReturn($formattedPrice);

        $itemMock = $this->getMockBuilder(QuoteItem::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getStore', '__wakeup'])
            ->getMock();

        $itemMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);

        $this->renderer->setItem($itemMock);
        $this->assertEquals($formattedPrice, $this->renderer->formatPrice($price));
    }

    public function testFormatPriceOrderItem(): void
    {
        $price = 3.554;
        $formattedPrice = "$3.55";

        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orderMock->expects($this->once())
            ->method('formatPrice')
            ->with($price, false)
            ->willReturn($formattedPrice);

        $itemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getOrder', '__wakeup'])
            ->getMock();

        $itemMock->expects($this->once())
            ->method('getOrder')
            ->willReturn($orderMock);

        $this->renderer->setItem($itemMock);
        $this->assertEquals($formattedPrice, $this->renderer->formatPrice($price));
    }

    public function testFormatPriceInvoiceItem(): void
    {
        $price = 3.554;
        $formattedPrice = "$3.55";

        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['formatPrice', '__wakeup'])
            ->getMock();

        $orderMock->expects($this->once())
            ->method('formatPrice')
            ->with($price, false)
            ->willReturn($formattedPrice);

        $orderItemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getOrder', '__wakeup'])
            ->getMock();

        $orderItemMock->expects($this->once())
            ->method('getOrder')
            ->willReturn($orderMock);

        $invoiceItemMock = $this->createPartialMockWithReflection(
            InvoiceItem::class,
            ['getStoreId', 'getOrderItem', '__wakeup']
        );

        $invoiceItemMock->expects($this->once())
            ->method('getOrderItem')
            ->willReturn($orderItemMock);

        $this->renderer->setItem($invoiceItemMock);
        $this->assertEquals($formattedPrice, $this->renderer->formatPrice($price));
    }

    public function testGetZone(): void
    {
        $this->assertEquals(Render::ZONE_CART, $this->renderer->getZone());
    }

    public function testGetStoreId(): void
    {
        $storeId = 'default';

        $itemMock = $this->createPartialMockWithReflection(
            QuoteItem::class,
            ['getStoreId', '__wakeup']
        );

        $itemMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);

        $this->renderer->setItem($itemMock);
        $this->assertEquals($storeId, $this->renderer->getStoreId());
    }

    public function testGetItemDisplayPriceExclTaxQuoteItem(): void
    {
        $price = 10;

        /** @var QuoteItem|MockObject $quoteItemMock */
        $quoteItemMock = $this->getMockBuilder(QuoteItem::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPrice', '__wakeup'])
            ->getMock();

        $quoteItemMock->expects($this->once())
            ->method('getPrice')
            ->willReturn($price);

        $this->renderer->setItem($quoteItemMock);
        $this->assertEquals($price, $this->renderer->getItemDisplayPriceExclTax());
    }

    public function testGetItemDisplayPriceExclTaxOrderItem(): void
    {
        $price = 10;

        /** @var Item|MockObject $orderItemMock */
        $orderItemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPrice', '__wakeup'])
            ->getMock();

        $orderItemMock->expects($this->once())
            ->method('getPrice')
            ->willReturn($price);

        $this->renderer->setItem($orderItemMock);
        $this->assertEquals($price, $this->renderer->getItemDisplayPriceExclTax());
    }

    public function testGetTotalAmount(): void
    {
        $rowTotal = 100;
        $taxAmount = 10;
        $discountTaxCompensationAmount = 2;
        $discountAmount = 20;

        $expectedValue = $rowTotal + $taxAmount + $discountTaxCompensationAmount - $discountAmount;

        $itemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'getRowTotal',
                    'getTaxAmount',
                    'getDiscountTaxCompensationAmount',
                    'getDiscountAmount',
                    '__wakeup'
                ]
            )
            ->getMock();

        $itemMock->expects($this->once())
            ->method('getRowTotal')
            ->willReturn($rowTotal);

        $itemMock->expects($this->once())
            ->method('getTaxAmount')
            ->willReturn($taxAmount);

        $itemMock->expects($this->once())
            ->method('getDiscountTaxCompensationAmount')
            ->willReturn($discountTaxCompensationAmount);

        $itemMock->expects($this->once())
            ->method('getDiscountAmount')
            ->willReturn($discountAmount);

        $this->assertEquals($expectedValue, $this->renderer->getTotalAmount($itemMock));
    }

    public function testGetBaseTotalAmount(): void
    {
        $baseRowTotal = 100;
        $baseTaxAmount = 10;
        $baseDiscountTaxCompensationAmount = 2;
        $baseDiscountAmount = 20;

        $expectedValue = 92;

        $itemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'getBaseRowTotal',
                    'getBaseTaxAmount',
                    'getBaseDiscountTaxCompensationAmount',
                    'getBaseDiscountAmount',
                    '__wakeup'
                ]
            )
            ->getMock();

        $itemMock->expects($this->once())
            ->method('getBaseRowTotal')
            ->willReturn($baseRowTotal);

        $itemMock->expects($this->once())
            ->method('getBaseTaxAmount')
            ->willReturn($baseTaxAmount);

        $itemMock->expects($this->once())
            ->method('getBaseDiscountTaxCompensationAmount')
            ->willReturn($baseDiscountTaxCompensationAmount);

        $itemMock->expects($this->once())
            ->method('getBaseDiscountAmount')
            ->willReturn($baseDiscountAmount);

        $this->assertEquals($expectedValue, $this->renderer->getBaseTotalAmount($itemMock));
    }
}
