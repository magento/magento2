<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Model\Express;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Magento\Framework\DataObject\Copy;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Paypal\Model\Config;
use Magento\Paypal\Model\Express\Checkout;
use Magento\Quote\Api\Data\CartExtensionInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Shipping;
use Magento\Quote\Model\ShippingAssignment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CheckoutTest extends TestCase
{
    const SHIPPING_METHOD = 'new_shipping_method';
    /**
     * @var Checkout|Checkout
     */
    protected $checkoutModel;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var MockObject|Quote
     */
    protected $quoteMock;

    /**
     * @var MockObject|AccountManagementInterface
     */
    protected $customerAccountManagementMock;

    /**
     * @var MockObject|Copy
     */
    protected $objectCopyServiceMock;

    /**
     * @var MockObject|Session
     */
    protected $customerSessionMock;

    /**
     * @var MockObject|Customer
     */
    protected $customerMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->customerMock = $this->createMock(Customer::class);
        $this->quoteMock = $this->getMockBuilder(Quote::class)
            ->addMethods(['getCustomerData'])
            ->onlyMethods(
                [
                    'getId',
                    'assignCustomer',
                    'assignCustomerWithAddressChange',
                    'getBillingAddress',
                    'getShippingAddress',
                    'isVirtual',
                    'addCustomerAddress',
                    'collectTotals',
                    '__wakeup',
                    'save',
                    'getIsVirtual',
                    'getExtensionAttributes'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerAccountManagementMock = $this->createMock(AccountManagement::class);
        $this->objectCopyServiceMock = $this->getMockBuilder(Copy::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerSessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $paypalConfigMock = $this->createMock(Config::class);
        $this->checkoutModel = $this->objectManager->getObject(
            Checkout::class,
            [
                'params'                 => [
                    'quote' => $this->quoteMock,
                    'config' => $paypalConfigMock,
                    'session' => $this->customerSessionMock,
                ],
                'accountManagement' => $this->customerAccountManagementMock,
                'objectCopyService' => $this->objectCopyServiceMock
            ]
        );
        parent::setUp();
    }

    public function testSetCustomerData()
    {
        $customerDataMock = $this->getMockForAbstractClass(CustomerInterface::class);
        $this->quoteMock->expects($this->once())->method('assignCustomer')->with($customerDataMock);
        $customerDataMock->expects($this->once())
            ->method('getId');
        $this->checkoutModel->setCustomerData($customerDataMock);
    }

    public function testSetCustomerWithAddressChange()
    {
        /** @var CustomerInterface $customerDataMock */
        $customerDataMock = $this->getMockForAbstractClass(CustomerInterface::class);
        /** @var Address $customerDataMock */
        $quoteAddressMock = $this->createMock(Address::class);
        $this->quoteMock
            ->expects($this->once())
            ->method('assignCustomerWithAddressChange')
            ->with($customerDataMock, $quoteAddressMock, $quoteAddressMock);
        $customerDataMock->expects($this->once())->method('getId');
        $this->checkoutModel->setCustomerWithAddressChange($customerDataMock, $quoteAddressMock, $quoteAddressMock);
    }

    public function testUpdateShippingMethod()
    {
        $shippingAddressMock = $this->getMockBuilder(Address::class)
            ->setMethods(['setCollectShippingRates', 'getShippingMethod', 'setShippingMethod'])
            ->disableOriginalConstructor()
            ->getMock();
        $billingAddressMock = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->getMock();
        $shippingAddressMock->expects(static::once())
            ->method('getShippingMethod')
            ->willReturn('old_method');
        $shippingAddressMock->expects(static::once())
            ->method('setShippingMethod')
            ->with(self::SHIPPING_METHOD)
            ->willReturnSelf();

        $shippingMock = $this->getMockBuilder(Shipping::class)
            ->disableOriginalConstructor()
            ->getMock();
        $shippingMock->expects(static::once())
            ->method('setMethod')
            ->with(self::SHIPPING_METHOD);

        $shippingAssignmentMock = $this->getMockBuilder(ShippingAssignment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $shippingAssignmentMock->expects(static::once())
            ->method('getShipping')
            ->willReturn($shippingMock);

        $cartExtensionMock = $this->getMockBuilder(CartExtensionInterface::class)
            ->setMethods(['getShippingAssignments'])
            ->getMockForAbstractClass();
        $cartExtensionMock->expects(static::exactly(2))
            ->method('getShippingAssignments')
            ->willReturn([$shippingAssignmentMock]);

        $this->quoteMock->expects(static::exactly(2))
            ->method('getShippingAddress')
            ->willReturn($shippingAddressMock);
        $this->quoteMock->expects(static::exactly(2))
            ->method('getIsVirtual')
            ->willReturn(false);
        $this->quoteMock->expects(static::any())
            ->method('getBillingAddress')
            ->willReturn($billingAddressMock);
        $this->quoteMock->expects(static::once())
            ->method('getExtensionAttributes')
            ->willReturn($cartExtensionMock);

        $this->checkoutModel->updateShippingMethod(self::SHIPPING_METHOD);
    }
}
