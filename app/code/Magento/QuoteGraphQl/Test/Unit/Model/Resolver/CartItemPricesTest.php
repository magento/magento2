<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Test\Unit\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\Context;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\QuoteGraphQl\Model\Cart\TotalsCollector;
use Magento\QuoteGraphQl\Model\Resolver\CartItemPrices;
use Magento\QuoteGraphQl\Model\GetDiscounts;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\QuoteGraphQl\Model\GetOptionsRegularPrice;
use Magento\Framework\Api\ExtensionAttributesInterface;
use Magento\Catalog\Model\Product;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @see CartItemPrices
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CartItemPricesTest extends TestCase
{
    /**
     * @var CartItemPrices
     */
    private CartItemPrices $cartItemPrices;

    /**
     * @var TotalsCollector|MockObject
     */
    private TotalsCollector $totalsCollectorMock;

    /**
     * @var GetDiscounts |MockObject
     */
    private GetDiscounts $getDiscountsMock;

    /**
     * @var PriceCurrencyInterface |MockObject
     */
    private PriceCurrencyInterface $priceCurrencyMock;

    /**
     * @var GetOptionsRegularPrice |MockObject
     */
    private GetOptionsRegularPrice $getOptionsRegularPriceMock;

    /**
     * @var Field|MockObject
     */
    private Field $fieldMock;

    /**
     * @var ResolveInfo|MockObject
     */
    private ResolveInfo $resolveInfoMock;

    /**
     * @var Context|MockObject
     */
    private Context $contextMock;

    /**
     * @var Quote|MockObject
     */
    private Quote $quoteMock;

    /**
     * @var Item|MockObject
     */
    private Item $itemMock;

    /**
     * @var Product|MockObject
     */
    private Product $productMock;

    /**
     * @var ExtensionAttributesInterface|MockObject
     */
    private ExtensionAttributesInterface $itemExtensionMock;

    /**
     * @var array
     */
    private array $valueMock = [];

    protected function setUp(): void
    {
        $this->totalsCollectorMock = $this->createMock(TotalsCollector::class);
        $this->getDiscountsMock = $this->createMock(GetDiscounts::class);
        $this->priceCurrencyMock = $this->createMock(PriceCurrencyInterface::class);
        $this->getOptionsRegularPriceMock = $this->createMock(GetOptionsRegularPrice::class);
        $this->productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCustomOption'])
            ->getMock();
        $this->fieldMock = $this->createMock(Field::class);
        $this->resolveInfoMock = $this->createMock(ResolveInfo::class);
        $this->contextMock = $this->createMock(Context::class);
        $this->quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->addMethods(['getQuoteCurrencyCode'])
            ->getMock();
        $this->itemMock = $this->getMockBuilder(Item::class)
            ->addMethods([
                'getPriceInclTax', 'getRowTotal',
                'getRowTotalInclTax', 'getDiscountAmount'
            ])
            ->onlyMethods([
                'getCalculationPrice', 'getQuote', 'getExtensionAttributes',
                'getProduct', 'getOriginalPrice'
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $this->itemExtensionMock = $this->getMockBuilder(
            ExtensionAttributesInterface::class
        )->addMethods(['getDiscounts'])->getMockForAbstractClass();

        $this->cartItemPrices = new CartItemPrices(
            $this->totalsCollectorMock,
            $this->getDiscountsMock,
            $this->priceCurrencyMock,
            $this->getOptionsRegularPriceMock
        );
    }

    public function testResolve(): void
    {
        $this->valueMock = ['model' => $this->itemMock];

        $this->resolveInfoMock->expects($this->once())
            ->method('getFieldSelection')
            ->with(1)
            ->willReturn([]);

        $this->itemMock
            ->expects($this->exactly(2))
            ->method('getQuote')
            ->willReturn($this->quoteMock);

        $this->quoteMock
            ->expects($this->once())
            ->method('getQuoteCurrencyCode')
            ->willReturn('USD');

        $this->itemMock
            ->expects($this->once())
            ->method('getDiscountAmount');

        $this->itemMock
            ->expects($this->once())
            ->method('getCalculationPrice');

        $this->itemMock
            ->expects($this->once())
            ->method('getPriceInclTax');

        $this->itemMock
            ->expects($this->any())
            ->method('getOriginalPrice')
            ->willReturn(0);

        $this->itemMock
            ->expects($this->once())
            ->method('getRowTotal');

        $this->itemMock
            ->expects($this->once())
            ->method('getRowTotalInclTax');

        $this->itemMock
            ->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->itemExtensionMock);

        $this->itemMock
            ->expects($this->any())
            ->method('getProduct')
            ->willReturn($this->productMock);

        $this->productMock
            ->expects($this->exactly(2))
            ->method('getCustomOption')
            ->willReturn(null);

        $this->itemExtensionMock
            ->expects($this->once())
            ->method('getDiscounts')
            ->willReturn([]);

        $this->getDiscountsMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->quoteMock, []);

        $this->cartItemPrices->resolve($this->fieldMock, $this->contextMock, $this->resolveInfoMock, $this->valueMock);
    }

    public function testResolveWithoutModelInValueParameter(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('"model" value should be specified');
        $this->cartItemPrices->resolve($this->fieldMock, $this->contextMock, $this->resolveInfoMock, $this->valueMock);
    }
}
