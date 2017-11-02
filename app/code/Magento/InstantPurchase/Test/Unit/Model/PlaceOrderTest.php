<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InstantPurchase\Test\Unit\Model;

use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;
use Magento\InstantPurchase\Model\CustomerDataGetter;
use Magento\InstantPurchase\Model\PaymentPreparer;
use Magento\InstantPurchase\Model\PlaceOrder;
use Magento\InstantPurchase\Model\QuotePreparer;
use Magento\InstantPurchase\Model\ShippingRateChooser;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class PlaceOrderTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $shippingRateChooser;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CustomerDataGetter
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
    private $prepareQuote;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentPreparer;
    /**
     * @var \Magento\InstantPurchase\Model\PlaceOrder
     */
    private $placeOrder;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->customerData = $this->createMock(CustomerDataGetter::class);
        $this->cartManagementInterface = $this->createMock(
            CartManagementInterface::class
        );
        $this->quoteRepository = $this->createMock(CartRepositoryInterface::class);
        $this->prepareQuote = $this->createMock(QuotePreparer::class);
        $this->paymentPreparer = $this->createMock(PaymentPreparer::class);
        $this->quote = $this->createMock(Quote::class);
        $this->product = $this->createMock(Product::class);
        $this->shippingRateChooser = $this->createMock(ShippingRateChooser::class);
        $this->placeOrder = $objectManager->getObject(PlaceOrder::class, [
            'quoteRepository' => $this->quoteRepository,
            'cartManagementInterface' => $this->cartManagementInterface,
            'prepareQuote' => $this->prepareQuote,
            'shippingRateChooser' => $this->shippingRateChooser,
            'paymentPreparer' => $this->paymentPreparer
        ]);
    }

    public function testPlaceOrder()
    {
        $params = [
            'qty' => 1,
            'customer_cc' => '1'
        ];
        $quoteId = 123;
        $orderId = 321;

        $paramsObject = new DataObject($params);
        $this->prepareQuote->expects($this->once())->method('prepare')->willReturn($this->quote);
        $this->quote->expects($this->once())
            ->method('addProduct')
            ->with($this->product, $paramsObject);
        $this->shippingRateChooser->expects($this->once())->method('choose')->with($this->quote);
        $this->paymentPreparer
            ->expects($this->once())
            ->method('prepare')
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
