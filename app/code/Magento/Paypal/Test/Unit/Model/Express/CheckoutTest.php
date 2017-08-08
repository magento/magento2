<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Paypal\Test\Unit\Model\Express;

use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Shipping;
use Magento\Quote\Model\ShippingAssignment;
use Magento\Quote\Api\Data\CartExtensionInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CheckoutTest extends \PHPUnit\Framework\TestCase
{
    const SHIPPING_METHOD = 'new_shipping_method';
    /**
     * @var \Magento\Paypal\Model\Express\Checkout | \Magento\Paypal\Model\Express\Checkout
     */
    protected $checkoutModel;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \'Magento\Quote\Model\Quote
     */
    protected $quoteMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Api\AccountManagementInterface
     */
    protected $customerAccountManagementMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\DataObject\Copy
     */
    protected $objectCopyServiceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Model\Session
     */
    protected $customerSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Customer\Model\Customer
     */
    protected $customerMock;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->customerMock = $this->createMock(\Magento\Customer\Model\Customer::class);
        $this->quoteMock = $this->createPartialMock(\Magento\Quote\Model\Quote::class, [
                'getId', 'assignCustomer', 'assignCustomerWithAddressChange', 'getBillingAddress',
                'getShippingAddress', 'isVirtual', 'addCustomerAddress', 'collectTotals', '__wakeup',
                'save', 'getCustomerData', 'getIsVirtual', 'getExtensionAttributes'
            ]);
        $this->customerAccountManagementMock = $this->createMock(\Magento\Customer\Model\AccountManagement::class);
        $this->objectCopyServiceMock = $this->getMockBuilder(\Magento\Framework\DataObject\Copy::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerSessionMock = $this->getMockBuilder(\Magento\Customer\Model\Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $paypalConfigMock = $this->createMock(\Magento\Paypal\Model\Config::class);
        $this->checkoutModel = $this->objectManager->getObject(
            \Magento\Paypal\Model\Express\Checkout::class,
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
        $customerDataMock = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        $this->quoteMock->expects($this->once())->method('assignCustomer')->with($customerDataMock);
        $customerDataMock->expects($this->once())
            ->method('getId');
        $this->checkoutModel->setCustomerData($customerDataMock);
    }

    public function testSetCustomerWithAddressChange()
    {
        /** @var \Magento\Customer\Api\Data\CustomerInterface $customerDataMock */
        $customerDataMock = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        /** @var \Magento\Quote\Model\Quote\Address $customerDataMock */
        $quoteAddressMock = $this->createMock(\Magento\Quote\Model\Quote\Address::class);
        $this->quoteMock
            ->expects($this->once())
            ->method('assignCustomerWithAddressChange')
            ->with($customerDataMock, $quoteAddressMock, $quoteAddressMock);
        $customerDataMock->expects($this->once())->method('getId');
        $this->checkoutModel->setCustomerWithAddressChange($customerDataMock, $quoteAddressMock, $quoteAddressMock);
    }

    public function testUpdateShippingMethod()
    {
        $shippingAddressMock = $this->getMockBuilder(Quote\Address::class)
            ->setMethods(['setCollectShippingRates', 'getShippingMethod', 'setShippingMethod'])
            ->disableOriginalConstructor()
            ->getMock();
        $billingAddressMock = $this->getMockBuilder(Quote\Address::class)
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
