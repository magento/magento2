<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model\Quote\Address\Total;

/**
 * Class SubtotalTest
 * @package Magento\Quote\Model\Quote\Address\Total
 * TODO refactor me
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SubtotalTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Quote\Model\Quote\Address\Total\Subtotal
     */
    protected $subtotalModel;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $stockItemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockRegistry;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->subtotalModel = $this->objectManager->getObject(
            \Magento\Quote\Model\Quote\Address\Total\Subtotal::class
        );

        $this->stockRegistry = $this->createPartialMock(
            \Magento\CatalogInventory\Model\StockRegistry::class,
            ['getStockItem', '__wakeup']
        );
        $this->stockItemMock = $this->createPartialMock(
            \Magento\CatalogInventory\Model\Stock\Item::class,
            ['getIsInStock', '__wakeup']
        );
    }

    public function collectDataProvider()
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
     * @dataProvider collectDataProvider
     *
     * @param int $price
     * @param int $originalPrice
     * @param bool $itemHasParent
     * @param int $expectedPrice
     * @param int $expectedOriginalPrice
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCollect($price, $originalPrice, $itemHasParent, $expectedPrice, $expectedOriginalPrice)
    {
        $this->stockRegistry->expects($this->any())->method('getStockItem')->willReturn($this->stockItemMock);

        $priceCurrency = $this->getMockBuilder(\Magento\Framework\Pricing\PriceCurrencyInterface::class)->getMock();
        $convertedPrice = 1231313;
        // @TODO this is a wrong test and it does not check methods. Any digital value will be correct
        $priceCurrency->expects($this->any())->method('convert')->willReturn(1231313);

        /** @var \Magento\Quote\Model\Quote\Item|\PHPUnit_Framework_MockObject_MockObject $quoteItem */
        $quoteItem = $this->objectManager->getObject(
            \Magento\Quote\Model\Quote\Item::class,
            [
                'stockRegistry' => $this->stockRegistry,
                'priceCurrency' => $priceCurrency,
            ]
        );
        /** @var \Magento\Quote\Model\Quote\Address|\PHPUnit_Framework_MockObject_MockObject $address */
        $address = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Address::class,
            ['setTotalQty', 'getTotalQty', 'removeItem', 'getQuote']
        );

        /** @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject $product */
        $product = $this->createMock(\Magento\Catalog\Model\Product::class);
        $product->expects($this->any())->method('getPrice')->will($this->returnValue($originalPrice));

        /** @var \Magento\Quote\Model\Quote|\PHPUnit_Framework_MockObject_MockObject $quote */
        $quote = $this->createMock(\Magento\Quote\Model\Quote::class);
        $store = $this->objectManager->getObject(\Magento\Store\Model\Store::class);
        $store->setCurrentCurrency('');

        $store = $this->createPartialMock(\Magento\Store\Model\Store::class, ['getWebsiteId']);
        $store->expects($this->any())->method('getWebsiteId')->willReturn(10);
        $product->expects($this->any())->method('getStore')->willReturn($store);
        $product->expects($this->any())->method('isVisibleInCatalog')->will($this->returnValue(true));
        $extensionAttribute = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductExtensionInterface::class)
            ->setMethods(['getStockItem'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $extensionAttribute->expects($this->atLeastOnce())
            ->method('getStockItem')
            ->will($this->returnValue($this->stockItemMock));
        $product->expects($this->atLeastOnce())->method('getExtensionAttributes')->willReturn($extensionAttribute);
        $quote->expects($this->any())->method('getStore')->will($this->returnValue($store));
        $quoteItem->setProduct($product)->setQuote($quote);

        $parentQuoteItem = false;
        if ($itemHasParent) {
            $parentQuoteItem = $this->createMock(\Magento\Quote\Model\Quote\Item::class);
            $parentQuoteItem->expects($this->any())->method('getProduct')->will($this->returnValue($product));
        }
        $quoteItem->setParentItem($parentQuoteItem);
        //This value will be overwritten
        $quoteItem->setConvertedPrice(10);

        $priceModel = $this->createMock(\Magento\Catalog\Model\Product\Type\Price::class);
        $priceModel->expects($this->any())->method('getChildFinalPrice')->willReturn($price);
        $product->expects($this->any())->method('getPriceModel')->willReturn($priceModel);
        $product->expects($this->any())->method('getFinalPrice')->willReturn($price);

        $shipping = $this->createMock(\Magento\Quote\Api\Data\ShippingInterface::class);
        $shipping->expects($this->exactly(2))->method('getAddress')->willReturn($address);
        $address->expects($this->at(0))->method('setTotalQty')->with(0);
        $address->expects($this->any())->method('getTotalQty')->willReturn(0);
        $shippingAssignmentMock = $this->createMock(\Magento\Quote\Api\Data\ShippingAssignmentInterface::class);
        $shippingAssignmentMock->expects($this->exactly(2))->method('getShipping')->willReturn($shipping);
        $shippingAssignmentMock->expects($this->once())->method('getItems')->willReturn([$quoteItem]);

        $total = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Address\Total::class,
            ['setBaseVirtualAmount', 'setVirtualAmount']
        );
        $total->expects($this->once())->method('setBaseVirtualAmount')->willReturnSelf();
        $total->expects($this->once())->method('setVirtualAmount')->willReturnSelf();

        $this->subtotalModel->collect($quote, $shippingAssignmentMock, $total);

        $this->assertEquals($expectedPrice, $quoteItem->getPrice());
        $this->assertEquals($expectedOriginalPrice, $quoteItem->getBaseOriginalPrice());
        $this->assertEquals($convertedPrice, $quoteItem->getCalculationPrice());
        $this->assertEquals($convertedPrice, $quoteItem->getConvertedPrice());
    }

    public function testFetch()
    {
        $expectedResult = [
            'code' => null,
            'title' => __('Subtotal'),
            'value' => 100
        ];

        $quoteMock = $this->createMock(\Magento\Quote\Model\Quote::class);
        $totalMock = $this->createPartialMock(\Magento\Quote\Model\Quote\Address\Total::class, ['getSubtotal']);
        $totalMock->expects($this->once())->method('getSubtotal')->willReturn(100);

        $this->assertEquals($expectedResult, $this->subtotalModel->fetch($quoteMock, $totalMock));
    }
}
