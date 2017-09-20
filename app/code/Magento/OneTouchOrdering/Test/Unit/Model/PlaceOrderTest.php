<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OneTouchOrdering\Test\Unit\Model;

use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\OneTouchOrdering\Model\CustomerData;
use Magento\OneTouchOrdering\Model\PlaceOrder;
use Magento\OneTouchOrdering\Model\PrepareQuote;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class PlaceOrderTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CustomerData
     */
    private $customerData;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $cartManagementInterface;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepository;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $quote;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Product
     */
    private $product;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $shippingAddress;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $prepareQuote;
    /**
     * @var \Magento\OneTouchOrdering\Model\PlaceOrder
     */
    private $placeOrder;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->customerData = $this->createMock(\Magento\OneTouchOrdering\Model\CustomerData::class);
        $this->cartManagementInterface = $this->createMock(
            \Magento\Quote\Api\CartManagementInterface::class
        );
        $this->quoteRepository = $this->createMock(QuoteResource::class);
        $this->prepareQuote = $this->createMock(PrepareQuote::class);
        $this->quote = $this->createMock(Quote::class);
        $this->product = $this->createMock(Product::class);
        $this->shippingAddress = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->setMethods(
                ['setCollectShippingRates', 'collectShippingRates', 'getAllShippingRates', 'setShippingMethod']
            )->getMock();
        $this->placeOrder = $objectManager->getObject(PlaceOrder::class, [
            'quoteRepository' => $this->quoteRepository,
            'cartManagementInterface' => $this->cartManagementInterface,
            'prepareQuote' => $this->prepareQuote
        ]);
    }

    public function testPlaceOrderNoShippingRates()
    {
        $this->prepareQuote->expects($this->once())->method('prepare')->willReturn($this->quote);
        $this->quote
            ->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddress);
        $this->shippingAddress
            ->expects($this->once())
            ->method('setCollectShippingRates')
            ->with(true)->willReturnSelf();
        $this->shippingAddress->expects($this->once())->method('collectShippingRates')->willReturnSelf();
        $this->shippingAddress->expects($this->once())->method('getAllShippingRates')->willReturn([]);
        $this->expectException(LocalizedException::class);

        $this->placeOrder->placeOrder($this->product, $this->customerData, ['qty' => 1]);
    }

    public function testPlaceOrder()
    {
        $shippingRates = [
            ['code' => 'expensive_rate', 'price' => 100],
            ['code' => 'cheap_rate', 'price' => 10]
        ];
        $params = ['qty' => 1];
        $quoteId = 123;
        $orderId = 321;

        $paramsObject = new DataObject($params);

        $this->prepareQuote->expects($this->once())->method('prepare')->willReturn($this->quote);
        $this->quote->expects($this->once())
            ->method('addProduct')
            ->with($this->product, $paramsObject);
        $this->quote
            ->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddress);
        $this->shippingAddress
            ->expects($this->once())
            ->method('setCollectShippingRates')
            ->with(true)
            ->willReturnSelf();
        $this->shippingAddress
            ->expects($this->once())
            ->method('collectShippingRates')
            ->willReturnSelf();
        $this->shippingAddress
            ->expects($this->once())
            ->method('getAllShippingRates')
            ->willReturn($shippingRates);
        $this->shippingAddress
            ->expects($this->once())
            ->method('setShippingMethod')
            ->with('cheap_rate');
        $this->prepareQuote
            ->expects($this->once())
            ->method('preparePayment')
            ->with($this->quote);
        $this->quoteRepository->expects($this->once())->method('save')->with($this->quote);
        $this->quote->method('getId')->willReturn($quoteId);
        $this->cartManagementInterface
            ->expects($this->once())
            ->method('placeOrder')
            ->with($quoteId)
            ->willReturn($orderId);
        $result = $this->placeOrder->placeOrder($this->product, $this->customerData, $params);
        $this->assertSame($result, $orderId);
    }
}
