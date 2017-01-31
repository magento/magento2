<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model\Quote\Address\Total;

/**
 * Class SubtotalTest
 * @package Magento\Quote\Model\Quote\Address\Total
 * TODO refactor me
 */
class SubtotalTest extends \PHPUnit_Framework_TestCase
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
        $this->subtotalModel = $this->objectManager->getObject('Magento\Quote\Model\Quote\Address\Total\Subtotal');

        $this->stockRegistry = $this->getMock(
            'Magento\CatalogInventory\Model\StockRegistry',
            ['getStockItem', '__wakeup'],
            [],
            '',
            false
        );
        $this->stockItemMock = $this->getMock(
            'Magento\CatalogInventory\Model\Stock\Item',
            ['getIsInStock', '__wakeup'],
            [],
            '',
            false
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

        $priceCurrency = $this->getMockBuilder('Magento\Framework\Pricing\PriceCurrencyInterface')->getMock();
        $convertedPrice = 1231313;
        // @TODO this is a wrong test and it does not check methods. Any digital value will be correct
        $priceCurrency->expects($this->any())->method('convert')->willReturn(1231313);


        /** @var \Magento\Quote\Model\Quote\Item|\PHPUnit_Framework_MockObject_MockObject $quoteItem */
        $quoteItem = $this->objectManager->getObject(
            'Magento\Quote\Model\Quote\Item',
            [
                'stockRegistry' => $this->stockRegistry,
                'priceCurrency' => $priceCurrency,
            ]
        );
        /** @var \Magento\Quote\Model\Quote\Address|\PHPUnit_Framework_MockObject_MockObject $address */
        $address = $this->getMock(
            'Magento\Quote\Model\Quote\Address',
            [],
            [],
            '',
            false
        );

        /** @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject $product */
        $product = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $product->expects($this->any())->method('getPrice')->will($this->returnValue($originalPrice));

        /** @var \Magento\Quote\Model\Quote|\PHPUnit_Framework_MockObject_MockObject $quote */
        $quote = $this->getMock('Magento\Quote\Model\Quote', [], [], '', false);
        $store = $this->objectManager->getObject('Magento\Store\Model\Store');
        $store->setCurrentCurrency('');

        $store = $this->getMock('Magento\Store\Model\Store', ['getWebsiteId'], [], '', false);
        $store->expects($this->any())->method('getWebsiteId')->willReturn(10);
        $product->expects($this->any())->method('getStore')->willReturn($store);
        $product->expects($this->any())->method('isVisibleInCatalog')->will($this->returnValue(true));

        $quote->expects($this->any())->method('getStore')->will($this->returnValue($store));
        $quoteItem->setProduct($product)->setQuote($quote);

        $parentQuoteItem = false;
        if ($itemHasParent) {
            $parentQuoteItem = $this->getMock('Magento\Quote\Model\Quote\Item', [], [], '', false);
            $parentQuoteItem->expects($this->any())->method('getProduct')->will($this->returnValue($product));
        }
        $quoteItem->setParentItem($parentQuoteItem);
        //This value will be overwritten
        $quoteItem->setConvertedPrice(10);

        $priceModel = $this->getMock('\Magento\Catalog\Model\Product\Type\Price', [], [], '', false);
        $priceModel->expects($this->any())->method('getChildFinalPrice')->willReturn($price);
        $product->expects($this->any())->method('getPriceModel')->willReturn($priceModel);
        $product->expects($this->any())->method('getFinalPrice')->willReturn($price);

        $shipping = $this->getMock('\Magento\Quote\Api\Data\ShippingInterface');
        $shipping->expects($this->exactly(2))->method('getAddress')->willReturn($address);
        $shippingAssignmentMock = $this->getMock('\Magento\Quote\Api\Data\ShippingAssignmentInterface');
        $shippingAssignmentMock->expects($this->exactly(2))->method('getShipping')->willReturn($shipping);
        $shippingAssignmentMock->expects($this->once())->method('getItems')->willReturn([$quoteItem]);

        $total = $this->getMock(
            '\Magento\Quote\Model\Quote\Address\Total',
            ['setBaseVirtualAmount', 'setVirtualAmount', 'setTotalQty'],
            [],
            '',
            false
        );
        $total->expects($this->once())->method('setBaseVirtualAmount')->willReturnSelf();
        $total->expects($this->once())->method('setVirtualAmount')->willReturnSelf();
        $total->expects($this->once())->method('setTotalQty')->with(0)->willReturnSelf();

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

        $quoteMock = $this->getMock('\Magento\Quote\Model\Quote', [], [], '', false);
        $totalMock = $this->getMock('\Magento\Quote\Model\Quote\Address\Total', ['getSubtotal'], [], '', false);
        $totalMock->expects($this->once())->method('getSubtotal')->willReturn(100);

        $this->assertEquals($expectedResult, $this->subtotalModel->fetch($quoteMock, $totalMock));
    }
}
