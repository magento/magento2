<?php

namespace Magento\InstantPurchase\Test\Unit\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Customer\Model\Customer;
use Magento\Framework\Exception\LocalizedException;
use Magento\InstantPurchase\Model\InstantPurchaseOption;
use Magento\InstantPurchase\Model\PlaceOrder;
use Magento\InstantPurchase\Model\QuoteManagement\PaymentConfiguration;
use Magento\InstantPurchase\Model\QuoteManagement\Purchase;
use Magento\InstantPurchase\Model\QuoteManagement\QuoteFilling;
use Magento\InstantPurchase\Model\QuoteManagement\ShippingConfiguration;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\Store\Api\Data\StoreInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\InstantPurchase\Model\QuoteManagement\QuoteCreation;
use Magento\Customer\Model\Address;

class PlaceOrderTest extends TestCase
{
    /**
     * @var PlaceOrder
     */
    private $placeOrder;

    /**
     * @var QuoteCreation|MockObject
     */
    private $quoteCreateMock;

    /**
     * @var QuoteFilling|MockObject
     */
    private $quoteFillingMock;

    /**
     * @var CartRepositoryInterface|MockObject
     */
    private $quoteRepositoryMock;

    /**
     * @var CartInterface|MockObject
     */
    private $cartMock;

    /**
     * @var Quote|MockObject
     */
    private $quoteMock;

    /**
     * @var ShippingConfiguration|MockObject
     */
    private $shippingConfigurationMock;

    /**
     * @var PaymentConfiguration|MockObject
     */
    private $paymentConfigurationMock;

    /**
     * @var Purchase|MockObject
     */
    private $purchaseMock;

    /**
     * @var StoreInterface|MockObject
     */
    private $storeMock;

    /**
     * @var Customer|MockObject
     */
    private $customerMock;

    /**
     * @var InstantPurchaseOption|MockObject
     */
    private $instantPurchaseOptionMock;

    /**
     * @var ProductInterface|MockObject
     */
    private $productMock;

    /**
     * @var Address|MockObject
     */
    private $addressMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->quoteCreateMock = $this->getMockBuilder(QuoteCreation::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteFillingMock = $this->getMockBuilder(QuoteFilling::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteRepositoryMock = $this->getMockBuilder(CartRepositoryInterface::class)
            ->getMock();
        $this->shippingConfigurationMock = $this->getMockBuilder(ShippingConfiguration::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentConfigurationMock = $this->getMockBuilder(PaymentConfiguration::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->purchaseMock = $this->getMockBuilder(Purchase::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeMock = $this->getMockBuilder(StoreInterface::class)
            ->getMock();
        $this->customerMock = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->instantPurchaseOptionMock = $this->getMockBuilder(InstantPurchaseOption::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productMock = $this->getMockBuilder(ProductInterface::class)
            ->getMock();
        $this->quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cartMock = $this->getMockBuilder(CartInterface::class)
            ->getMock();
        $this->addressMock = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->placeOrder = $objectManager->getObject(
            PlaceOrder::class,
            [
                'quoteRepository' => $this->quoteRepositoryMock,
                'quoteCreation' => $this->quoteCreateMock,
                'quoteFilling' => $this->quoteFillingMock,
                'shippingConfiguration' => $this->shippingConfigurationMock,
                'paymentConfiguration' => $this->paymentConfigurationMock,
                'purchase' => $this->purchaseMock,
            ]
        );
    }

    public function testPlaceOrder()
    {
        $orderId = 1;

        $this->instantPurchaseOptionMock->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn($this->addressMock);
        $this->instantPurchaseOptionMock->expects($this->once())
            ->method('getBillingAddress')
            ->willReturn($this->addressMock);

        $this->quoteCreateMock->expects($this->once())
            ->method('createQuote')
            ->with(
                $this->storeMock,
                $this->customerMock,
                $this->addressMock,
                $this->addressMock
            )->willReturn($this->quoteMock);

        $this->quoteFillingMock->expects($this->once())
            ->method('fillQuote')
            ->with($this->quoteMock, $this->productMock, [])
            ->willReturn($this->quoteMock);

        $this->quoteMock->expects($this->once())
            ->method('collectTotals')
            ->willReturnSelf();

        $this->quoteRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->quoteMock);

        $this->quoteMock->expects($this->once())
            ->method('getId');

        $this->quoteRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($this->cartMock);

        $this->purchaseMock->expects($this->once())
            ->method('purchase')
            ->willReturn($orderId);

        $this->assertEquals(
            $orderId,
            $this->placeOrder->placeOrder(
                $this->storeMock,
                $this->customerMock,
                $this->instantPurchaseOptionMock,
                $this->productMock,
                []
            )
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testPlaceOrderException()
    {
        $this->instantPurchaseOptionMock->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn($this->addressMock);
        $this->instantPurchaseOptionMock->expects($this->once())
            ->method('getBillingAddress')
            ->willReturn($this->addressMock);

        $this->quoteCreateMock->expects($this->once())
            ->method('createQuote')
            ->with(
                $this->storeMock,
                $this->customerMock,
                $this->addressMock,
                $this->addressMock
            )->willReturn($this->quoteMock);

        $this->quoteFillingMock->expects($this->once())
            ->method('fillQuote')
            ->with($this->quoteMock, $this->productMock, [])
            ->willReturn($this->quoteMock);

        $this->quoteMock->expects($this->once())
            ->method('collectTotals')
            ->willReturnSelf();

        $this->quoteRepositoryMock->expects($this->at(0))
            ->method('save')
            ->with($this->quoteMock);

        $this->quoteMock->expects($this->once())
            ->method('getId');

        $this->quoteRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($this->cartMock);

        $this->shippingConfigurationMock->expects($this->once())
            ->method('configureShippingMethod')
            ->willReturn($this->cartMock);
        
        $this->paymentConfigurationMock->expects($this->once())
            ->method('configurePayment')
            ->willReturn($this->cartMock);

        $this->purchaseMock->expects($this->once())
            ->method('purchase')
            ->willThrowException(new LocalizedException(__('Could not place order.')));

        $this->cartMock->expects($this->once())
            ->method('setIsActive')
            ->with(false);

        $this->quoteRepositoryMock->expects($this->at(2))
            ->method('save')
            ->with($this->cartMock);

        $this->placeOrder->placeOrder(
            $this->storeMock,
            $this->customerMock,
            $this->instantPurchaseOptionMock,
            $this->productMock,
            []
        );
    }
}
