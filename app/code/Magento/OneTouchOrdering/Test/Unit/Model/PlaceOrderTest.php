<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OneTouchOrdering\Test\Unit\Model;

use Magento\OneTouchOrdering\Model\CustomerBrainTreeManager;
use Magento\OneTouchOrdering\Model\PlaceOrder;
use Magento\Quote\Api\CartManagementInterface;
use PHPUnit\Framework\TestCase;

class PlaceOrderTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $cartManagementInterface;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $customerBrainTreeManager;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepository;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $quote;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
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
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->cartManagementInterface = $this->createMock(CartManagementInterface::class);
        $this->customerBrainTreeManager = $this->createMock(CustomerBrainTreeManager::class);
        $this->quoteRepository = $this->createMock(\Magento\Quote\Model\ResourceModel\Quote::class);
        $this->prepareQuote = $this->createMock(\Magento\OneTouchOrdering\Model\PrepareQuote::class);
        $this->quote = $this->createMock(\Magento\Quote\Model\Quote::class);
        $this->product = $this->createMock(\Magento\Catalog\Model\Product::class);
        $this->shippingAddress = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address::class)
            ->disableOriginalConstructor()
            ->setMethods(
                ['setCollectShippingRates', 'collectShippingRates', 'getAllShippingRates', 'setShippingMethod']
            )->getMock();
        $this->placeOrder = $objectManager->getObject(PlaceOrder::class, [
            'quoteRepository' => $this->quoteRepository,
            'cartManagementInterface' => $this->cartManagementInterface,
            'customerBrainTreeManager' => $this->customerBrainTreeManager,
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
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);

        $this->placeOrder->placeOrder($this->product, ['qty' => 1]);
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

        $paramsObject = new \Magento\Framework\DataObject($params);

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
        $result = $this->placeOrder->placeOrder($this->product, $params);
        $this->assertSame($result, $orderId);
    }
}
