<?php

namespace Magento\InstantPurchase\Test\Unit\Model;

use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Customer;
use Magento\Framework\Exception\LocalizedException;
use Magento\InstantPurchase\Model\InstantPurchaseOption;
use Magento\InstantPurchase\Model\PlaceOrder;
use Magento\InstantPurchase\Model\QuoteManagement\PaymentConfiguration;
use Magento\InstantPurchase\Model\QuoteManagement\Purchase;
use Magento\InstantPurchase\Model\QuoteManagement\QuoteFilling;
use Magento\InstantPurchase\Model\QuoteManagement\ShippingConfiguration;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\Store;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
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
     * @var Store|MockObject
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
     * @var Product|MockObject
     */
    private $productMock;

    /**
     * @var Address|MockObject
     */
    private $addressMock;

    protected function setUp()
    {
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
        $this->storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerMock = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->instantPurchaseOptionMock = $this->getMockBuilder(InstantPurchaseOption::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->addressMock = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Test for ensuring quote made inactive on error.
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testPlaceOrderException()
    {
        $placeOrder = new PlaceOrder(
            $this->quoteRepositoryMock,
            $this->quoteCreateMock,
            $this->quoteFillingMock,
            $this->shippingConfigurationMock,
            $this->paymentConfigurationMock,
            $this->purchaseMock
        );

        $this->instantPurchaseOptionMock->method('getShippingAddress')
            ->willReturn($this->addressMock);
        $this->instantPurchaseOptionMock->method('getBillingAddress')
            ->willReturn($this->addressMock);

        $this->quoteCreateMock->method('createQuote')
            ->with(
                $this->storeMock,
                $this->customerMock,
                $this->addressMock,
                $this->addressMock
            )->willReturn($this->quoteMock);

        $this->quoteFillingMock->method('fillQuote')
            ->with($this->quoteMock, $this->productMock, [])
            ->willReturn($this->quoteMock);

        $this->quoteMock->method('collectTotals')
            ->willReturnSelf();

        $this->quoteRepositoryMock->method('save')
            ->with($this->quoteMock);

        $this->quoteMock->method('getId');

        $this->quoteRepositoryMock->method('get')
            ->willReturn($this->quoteMock);

        $this->shippingConfigurationMock->method('configureShippingMethod')
            ->willReturn($this->quoteMock);
        
        $this->paymentConfigurationMock->method('configurePayment')
            ->willReturn($this->quoteMock);

        $this->purchaseMock->method('purchase')
            ->willThrowException(new LocalizedException(__('Could not place order.')));

        $this->quoteMock->expects($this->once())
            ->method('setIsActive')
            ->with(false);

        $placeOrder->placeOrder(
            $this->storeMock,
            $this->customerMock,
            $this->instantPurchaseOptionMock,
            $this->productMock,
            []
        );
    }
}
