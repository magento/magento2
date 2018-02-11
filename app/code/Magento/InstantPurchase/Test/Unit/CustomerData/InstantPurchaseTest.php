<?php

namespace Magento\InstantPurchase\Test\Unit\CustomerData;

use Magento\InstantPurchase\CustomerData\InstantPurchase;
use Magento\InstantPurchase\Model\Ui\CustomerAddressesFormatter;
use Magento\InstantPurchase\Model\Ui\PaymentTokenFormatter;
use Magento\InstantPurchase\Model\Ui\ShippingMethodFormatter;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Customer;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Store;
use Magento\InstantPurchase\Model\InstantPurchaseInterface;
use Magento\InstantPurchase\Model\InstantPurchaseOption;

class InstantPurchaseTest extends TestCase
{
    /**
     * @var InstantPurchase
     */
    private $instantPurchase;

    /**
     * @var Session|MockObject
     */
    private $sessionMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var Store|MockObject
     */
    private $storeMock;

    /**
     * @var Customer|MockObject
     */
    private $customerMock;

    /**
     * @var InstantPurchaseInterface|MockObject
     */
    private $instantPurchaseMock;
    
    /**
     * @var InstantPurchaseOption|MockObject
     */
    private $instantPurchaseOptionMock;
    
    protected function setUp()
    {
        $this->sessionMock = $this->createMock(Session::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->storeMock = $this->createMock(Store::class);
        $this->customerMock = $this->createMock(Customer::class);
        $this->instantPurchaseMock = $this->createMock(InstantPurchaseInterface::class);
        $this->instantPurchaseOptionMock = $this->createMock(InstantPurchaseOption::class);

        $this->sessionMock->method('getCustomer')
            ->willReturn($this->customerMock);

        $this->storeManagerMock->method('getStore')
            ->willReturn($this->storeMock);

        $this->instantPurchaseMock->method('getOption')
            ->willReturn($this->instantPurchaseOptionMock);

        $this->instantPurchase = new InstantPurchase(
            $this->sessionMock,
            $this->storeManagerMock,
            $this->instantPurchaseMock,
            $this->createMock(PaymentTokenFormatter::class),
            $this->createMock(CustomerAddressesFormatter::class),
            $this->createMock(ShippingMethodFormatter::class)
        );
    }

    public function testGetSectionDataCustomerLoggedOut()
    {
        $this->sessionMock->method('isLoggedIn')
            ->willReturn(false);

        $this->assertArrayHasKey('available', $this->instantPurchase->getSectionData());
    }

    public function testGetSectionDataLoggedInAndUnavailable()
    {
        $this->sessionMock->method('isLoggedIn')
            ->willReturn(true);

        $this->instantPurchaseOptionMock->method('isAvailable')
            ->willReturn(false);

        $this->assertArrayHasKey('available', $this->instantPurchase->getSectionData());
    }

    public function testGetSectionData()
    {
        $this->sessionMock->method('isLoggedIn')
            ->willReturn(true);

        $this->instantPurchaseOptionMock->method('isAvailable')
            ->willReturn(true);

        $result = $this->instantPurchase->getSectionData();
        $this->assertArrayHasKey('available', $result);
        $this->assertArrayHasKey('paymentToken', $result);
        $this->assertArrayHasKey('publicHash', $result['paymentToken']);
        $this->assertArrayHasKey('summary', $result['paymentToken']);
        $this->assertArrayHasKey('shippingAddress', $result);
        $this->assertArrayHasKey('id', $result['shippingAddress']);
        $this->assertArrayHasKey('summary', $result['shippingAddress']);
        $this->assertArrayHasKey('billingAddress', $result);
        $this->assertArrayHasKey('id', $result['billingAddress']);
        $this->assertArrayHasKey('summary', $result['billingAddress']);
        $this->assertArrayHasKey('shippingMethod', $result);
        $this->assertArrayHasKey('carrier', $result['shippingMethod']);
        $this->assertArrayHasKey('method', $result['shippingMethod']);
        $this->assertArrayHasKey('summary', $result['shippingMethod']);
    }
}
