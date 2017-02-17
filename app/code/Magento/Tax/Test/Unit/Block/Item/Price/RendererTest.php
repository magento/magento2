<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Unit\Block\Item\Price;

use Magento\Framework\Pricing\Render;

class RendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tax\Block\Item\Price\Renderer
     */
    protected $renderer;

    /**
     * @var \Magento\Tax\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $taxHelper;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceCurrency;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->priceCurrency = $this->getMockBuilder(
            \Magento\Framework\Pricing\PriceCurrencyInterface::class
        )->getMock();
        $this->taxHelper = $this->getMockBuilder(\Magento\Tax\Helper\Data::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'displayCartPriceExclTax',
                'displayCartBothPrices',
                'displayCartPriceInclTax',
                'displaySalesPriceExclTax',
                'displaySalesBothPrices',
                'displaySalesPriceInclTax',
            ])
            ->getMock();

        $this->renderer = $objectManager->getObject(
            \Magento\Tax\Block\Item\Price\Renderer::class,
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
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Sales\Model\Order\Item
     */
    protected function getItemMockWithStoreId($storeId)
    {
        $itemMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStoreId', '__wakeup'])
            ->getMock();

        $itemMock->expects($this->once())
            ->method('getStoreId')
            ->will($this->returnValue($storeId));

        return $itemMock;
    }

    /**
     * Test displayPriceInclTax
     *
     * @param string $zone
     * @param string $methodName
     * @dataProvider testDisplayPriceInclTaxDataProvider
     */
    public function testDisplayPriceInclTax($zone, $methodName)
    {
        $storeId = 1;
        $flag = true;

        $itemMock = $this->getItemMockWithStoreId($storeId);
        $this->renderer->setItem($itemMock);
        $this->renderer->setZone($zone);
        $this->taxHelper->expects($this->once())
            ->method($methodName)
            ->with($storeId)
            ->will($this->returnValue($flag));

        $this->assertEquals($flag, $this->renderer->displayPriceInclTax());
    }

    public function testDisplayPriceInclTaxDataProvider()
    {
        $data = [
            'cart' => [
                'zone' => Render::ZONE_CART,
                'method_name' => 'displayCartPriceInclTax',
            ],
            'anythingelse' => [
                'zone' => 'anythingelse',
                'method_name' => 'displayCartPriceInclTax',
            ],
            'sale' => [
                'zone' => Render::ZONE_SALES,
                'method_name' => 'displaySalesPriceInclTax',
            ],
            'email' => [
                'zone' => Render::ZONE_EMAIL,
                'method_name' => 'displaySalesPriceInclTax',
            ],
        ];

        return $data;
    }

    /**
     * Test displayPriceExclTax
     *
     * @param string $zone
     * @param string $methodName
     * @dataProvider testDisplayPriceExclTaxDataProvider
     */
    public function testDisplayPriceExclTax($zone, $methodName)
    {
        $storeId = 1;
        $flag = true;

        $itemMock = $this->getItemMockWithStoreId($storeId);
        $this->renderer->setItem($itemMock);
        $this->renderer->setZone($zone);
        $this->taxHelper->expects($this->once())
            ->method($methodName)
            ->with($storeId)
            ->will($this->returnValue($flag));

        $this->assertEquals($flag, $this->renderer->displayPriceExclTax());
    }

    public function testDisplayPriceExclTaxDataProvider()
    {
        $data = [
            'cart' => [
                'zone' => Render::ZONE_CART,
                'method_name' => 'displayCartPriceExclTax',
            ],
            'anythingelse' => [
                'zone' => 'anythingelse',
                'method_name' => 'displayCartPriceExclTax',
            ],
            'sale' => [
                'zone' => Render::ZONE_SALES,
                'method_name' => 'displaySalesPriceExclTax',
            ],
            'email' => [
                'zone' => Render::ZONE_EMAIL,
                'method_name' => 'displaySalesPriceExclTax',
            ],
        ];

        return $data;
    }

    /**
     * Test displayBothPrices
     *
     * @param string $zone
     * @param string $methodName
     * @dataProvider testDisplayBothPricesDataProvider
     */
    public function testDisplayBothPrices($zone, $methodName)
    {
        $storeId = 1;
        $flag = true;

        $itemMock = $this->getItemMockWithStoreId($storeId);
        $this->renderer->setItem($itemMock);
        $this->renderer->setZone($zone);
        $this->taxHelper->expects($this->once())
            ->method($methodName)
            ->with($storeId)
            ->will($this->returnValue($flag));

        $this->assertEquals($flag, $this->renderer->displayBothPrices());
    }

    public function testDisplayBothPricesDataProvider()
    {
        $data = [
            'cart' => [
                'zone' => Render::ZONE_CART,
                'method_name' => 'displayCartBothPrices',
            ],
            'anythingelse' => [
                'zone' => 'anythingelse',
                'method_name' => 'displayCartBothPrices',
            ],
            'sale' => [
                'zone' => Render::ZONE_SALES,
                'method_name' => 'displaySalesBothPrices',
            ],
            'email' => [
                'zone' => Render::ZONE_EMAIL,
                'method_name' => 'displaySalesBothPrices',
            ],
        ];

        return $data;
    }

    public function testFormatPriceQuoteItem()
    {
        $price = 3.554;
        $formattedPrice = "$3.55";

        $storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->setMethods(['formatPrice', '__wakeup'])
            ->getMock();

        $this->priceCurrency->expects($this->once())
            ->method('format')
            ->with($price, true)
            ->will($this->returnValue($formattedPrice));

        $itemMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStore', '__wakeup'])
            ->getMock();

        $itemMock->expects($this->once())
            ->method('getStore')
            ->will($this->returnValue($storeMock));

        $this->renderer->setItem($itemMock);
        $this->assertEquals($formattedPrice, $this->renderer->formatPrice($price));
    }

    public function testFormatPriceOrderItem()
    {
        $price = 3.554;
        $formattedPrice = "$3.55";

        $orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orderMock->expects($this->once())
            ->method('formatPrice')
            ->with($price, false)
            ->will($this->returnValue($formattedPrice));

        $itemMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOrder', '__wakeup'])
            ->getMock();

        $itemMock->expects($this->once())
            ->method('getOrder')
            ->will($this->returnValue($orderMock));

        $this->renderer->setItem($itemMock);
        $this->assertEquals($formattedPrice, $this->renderer->formatPrice($price));
    }

    public function testFormatPriceInvoiceItem()
    {
        $price = 3.554;
        $formattedPrice = "$3.55";

        $orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['formatPrice', '__wakeup'])
            ->getMock();

        $orderMock->expects($this->once())
            ->method('formatPrice')
            ->with($price, false)
            ->will($this->returnValue($formattedPrice));

        $orderItemMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOrder', '__wakeup'])
            ->getMock();

        $orderItemMock->expects($this->once())
            ->method('getOrder')
            ->will($this->returnValue($orderMock));

        $invoiceItemMock = $this->getMockBuilder(\Magento\Sales\Model\Invoice\Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOrderItem', '__wakeup', 'getStoreId'])
            ->getMock();

        $invoiceItemMock->expects($this->once())
            ->method('getOrderItem')
            ->will($this->returnValue($orderItemMock));

        $this->renderer->setItem($invoiceItemMock);
        $this->assertEquals($formattedPrice, $this->renderer->formatPrice($price));
    }

    public function testGetZone()
    {
        $this->assertEquals(Render::ZONE_CART, $this->renderer->getZone());
    }

    public function testGetStoreId()
    {
        $storeId = 'default';

        $itemMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStoreId', '__wakeup'])
            ->getMock();

        $itemMock->expects($this->once())
            ->method('getStoreId')
            ->will($this->returnValue($storeId));

        $this->renderer->setItem($itemMock);
        $this->assertEquals($storeId, $this->renderer->getStoreId());
    }

    public function testGetItemDisplayPriceExclTaxQuoteItem()
    {
        $price = 10;

        /** @var \Magento\Quote\Model\Quote\Item|\PHPUnit_Framework_MockObject_MockObject $quoteItemMock */
        $quoteItemMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCalculationPrice', '__wakeup'])
            ->getMock();

        $quoteItemMock->expects($this->once())
            ->method('getCalculationPrice')
            ->will($this->returnValue($price));

        $this->renderer->setItem($quoteItemMock);
        $this->assertEquals($price, $this->renderer->getItemDisplayPriceExclTax());
    }

    public function testGetItemDisplayPriceExclTaxOrderItem()
    {
        $price = 10;

        /** @var \Magento\Sales\Model\Order\Item|\PHPUnit_Framework_MockObject_MockObject $orderItemMock */
        $orderItemMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPrice', '__wakeup'])
            ->getMock();

        $orderItemMock->expects($this->once())
            ->method('getPrice')
            ->will($this->returnValue($price));

        $this->renderer->setItem($orderItemMock);
        $this->assertEquals($price, $this->renderer->getItemDisplayPriceExclTax());
    }

    public function testGetTotalAmount()
    {
        $rowTotal = 100;
        $taxAmount = 10;
        $discountTaxCompensationAmount = 2;
        $discountAmount = 20;

        $expectedValue = $rowTotal + $taxAmount + $discountTaxCompensationAmount - $discountAmount;

        $itemMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)
            ->disableOriginalConstructor()
            ->setMethods(
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
            ->will($this->returnValue($rowTotal));

        $itemMock->expects($this->once())
            ->method('getTaxAmount')
            ->will($this->returnValue($taxAmount));

        $itemMock->expects($this->once())
            ->method('getDiscountTaxCompensationAmount')
            ->will($this->returnValue($discountTaxCompensationAmount));

        $itemMock->expects($this->once())
            ->method('getDiscountAmount')
            ->will($this->returnValue($discountAmount));

        $this->assertEquals($expectedValue, $this->renderer->getTotalAmount($itemMock));
    }

    public function testGetBaseTotalAmount()
    {
        $baseRowTotal = 100;
        $baseTaxAmount = 10;
        $baseDiscountTaxCompensationAmount = 2;
        $baseDiscountAmount = 20;

        $expectedValue = 92;

        $itemMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)
            ->disableOriginalConstructor()
            ->setMethods(
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
            ->will($this->returnValue($baseRowTotal));

        $itemMock->expects($this->once())
            ->method('getBaseTaxAmount')
            ->will($this->returnValue($baseTaxAmount));

        $itemMock->expects($this->once())
            ->method('getBaseDiscountTaxCompensationAmount')
            ->will($this->returnValue($baseDiscountTaxCompensationAmount));

        $itemMock->expects($this->once())
            ->method('getBaseDiscountAmount')
            ->will($this->returnValue($baseDiscountAmount));

        $this->assertEquals($expectedValue, $this->renderer->getBaseTotalAmount($itemMock));
    }
}
