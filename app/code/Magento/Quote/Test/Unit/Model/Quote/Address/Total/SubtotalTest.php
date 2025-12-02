<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\Quote\Address\Total;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Catalog\Api\Data\ProductExtensionInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type\Price;
use Magento\CatalogInventory\Model\StockRegistry;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Api\Data\ShippingInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Item as AddressItem;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\Subtotal;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Quote\Test\Unit\Helper\TotalTestHelper;
use Magento\Quote\Test\Unit\Helper\ProductExtensionForSubtotalTestHelper;

/**
 * Test address total collector model.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SubtotalTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Subtotal
     */
    protected $subtotalModel;

    /**
     * @var MockObject
     */
    protected $stockItemMock;

    /**
     * @var MockObject
     */
    protected $stockRegistry;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->subtotalModel = $this->objectManager->getObject(
            Subtotal::class
        );

        $this->stockRegistry = $this->getMockBuilder(StockRegistry::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getStockItem'])
            ->getMock();
        $this->stockItemMock = $this->getMockBuilder(\Magento\CatalogInventory\Model\Stock\Item::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getIsInStock', '__wakeup'])
            ->getMock();
    }

    /**
     * @return array
     */
    public static function collectDataProvider(): array
    {
        return [
            [12, 10, false, 12, 10],
            [12, 0, false, 12, 12],
            [0, 10, false, 0, 10],
            [12, 10, true, null, null],
            [12, 10, false, 12, 10]
        ];
    }

    /**
     * @param int $price
     * @param int $originalPrice
     * @param bool $itemHasParent
     * @param int|null $expectedPrice
     * @param int|null $expectedOriginalPrice
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    #[DataProvider('collectDataProvider')]
    public function testCollect(
        int $price,
        int $originalPrice,
        bool $itemHasParent,
        ?int $expectedPrice,
        ?int $expectedOriginalPrice
    ): void {
        $this->stockRegistry->method('getStockItem')->willReturn($this->stockItemMock);

        $priceCurrency = $this->getMockBuilder(PriceCurrencyInterface::class)->getMock();
        $convertedPrice = 1231313;
        // @TODO this is a wrong test and it does not check methods. Any digital value will be correct
        $priceCurrency->method('convert')->willReturn(1231313);

        /** @var Item|MockObject $quoteItem */
        $quoteItem = $this->objectManager->getObject(
            Item::class,
            [
                'stockRegistry' => $this->stockRegistry,
                'priceCurrency' => $priceCurrency
            ]
        );
        /** @var Address|MockObject $address */
        $address = $this->getMockBuilder(\Magento\Quote\Test\Unit\Helper\AddressShippingInfoTestHelper::class)
            ->onlyMethods(['removeItem', 'getQuote'])
            ->getMock();

        /** @var Product|MockObject $product */
        $product = $this->createMock(Product::class);
        $product->expects($this->any())->method('getPrice')->willReturn($originalPrice);

        /** @var Quote|MockObject $quote */
        $quote = $this->createMock(Quote::class);
        $store = $this->objectManager->getObject(Store::class);
        $store->setCurrentCurrency('');

        $store = $this->createPartialMock(Store::class, ['getWebsiteId']);
        $store->method('getWebsiteId')->willReturn(10);
        $product->method('getStore')->willReturn($store);
        $product->expects($this->any())->method('isVisibleInCatalog')->willReturn(true);
        $extensionAttribute = new ProductExtensionForSubtotalTestHelper($this->stockItemMock);
        $product->expects($this->atLeastOnce())->method('getExtensionAttributes')->willReturn($extensionAttribute);
        $quote->expects($this->any())->method('getStore')->willReturn($store);
        $quoteItem->setProduct($product)->setQuote($quote);

        $parentQuoteItem = false;
        if ($itemHasParent) {
            $parentQuoteItem = $this->createMock(Item::class);
            $parentQuoteItem->expects($this->any())->method('getProduct')->willReturn($product);
        }
        $quoteItem->setParentItem($parentQuoteItem);
        //This value will be overwritten
        $quoteItem->setConvertedPrice(10);

        $priceModel = $this->createMock(Price::class);
        $priceModel->method('getChildFinalPrice')->willReturn($price);
        $product->method('getPriceModel')->willReturn($priceModel);
        $product->method('getFinalPrice')->willReturn($price);

        $shipping = $this->createMock(ShippingInterface::class);
        $shipping->expects($this->exactly(2))->method('getAddress')->willReturn($address);
        // setTotalQty/getTotalQty are real on helper, don't stub
        $shippingAssignmentMock = $this->createMock(ShippingAssignmentInterface::class);
        $shippingAssignmentMock->expects($this->exactly(2))->method('getShipping')->willReturn($shipping);
        $shippingAssignmentMock->expects($this->once())->method('getItems')->willReturn([$quoteItem]);

        $total = new class extends Total
        {
            public function __construct()
            {
            }
        };

        $this->subtotalModel->collect($quote, $shippingAssignmentMock, $total);

        $this->assertEquals($expectedPrice, $quoteItem->getPrice());
        $this->assertEquals($expectedOriginalPrice, $quoteItem->getBaseOriginalPrice());
        $this->assertEquals($convertedPrice, $quoteItem->getCalculationPrice());
        $this->assertEquals($convertedPrice, $quoteItem->getConvertedPrice());
    }

    /**
     * @return void
     */
    public function testFetch(): void
    {
        $expectedResult = [
            'code' => null,
            'title' => __('Subtotal'),
            'value' => 100
        ];

        $quoteMock = $this->createMock(Quote::class);
        $totalMock = new TotalTestHelper();
        $totalMock->setSubtotal(100);
        $this->assertEquals($expectedResult, $this->subtotalModel->fetch($quoteMock, $totalMock));
    }

    /**
     * Test that invalid items are not collected
     *
     * @return void
     */
    public function testCollectWithInvalidItems(): void
    {
        $addressItemId = 38203;
        $addressQuoteItemId = 7643;
        $storeId = 1;
        $quote = $this->createPartialMock(
            Quote::class,
            [
                'getItemsCollection'
            ]
        );
        $quote->setData(
            [
                'store_id' => $storeId
            ]
        );
        $quoteItem = $this->createPartialMock(
            Item::class,
            []
        );
        $quoteItem->setQuote($quote);
        $quote->method('getItemsCollection')
            ->willReturn([$quoteItem]);
        $address = $this->createPartialMock(
            Address::class,
            [
                'removeItem',
                'getQuote'
            ]
        );
        $address->method('getQuote')
            ->willReturn($quote);
        $address->expects($this->once())
            ->method('removeItem')
            ->with($addressItemId);
        $addressItem = $this->getMockBuilder(AddressItem::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId'])
            ->getMock();
        $addressItem->setAddress($address);
        $addressItem->method('getId')
            ->willReturn($addressItemId);
        $addressItem->setData('quote_item_id', $addressQuoteItemId);
        $shipping = $this->createMock(ShippingInterface::class);
        $shipping->method('getAddress')
            ->willReturn($address);
        $shippingAssignmentMock = $this->createMock(ShippingAssignmentInterface::class);
        $shippingAssignmentMock->method('getShipping')
            ->willReturn($shipping);
        $shippingAssignmentMock->method('getItems')
            ->willReturn([$addressItem]);
        $total = $this->createPartialMock(
            Total::class,
            []
        );
        $this->subtotalModel->collect($quote, $shippingAssignmentMock, $total);
    }
}
