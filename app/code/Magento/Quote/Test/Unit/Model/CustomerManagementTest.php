<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model;

/**
 * Class CustomerManagementTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Quote\Model\CustomerManagement
     */
    protected $customerManagement;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerRepositoryMock;

    /**
     * @var \Magento\Customer\Api\AccountManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $accountManagementMock;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerAddressRepositoryMock;

    /**
     * @var \Magento\Quote\Model\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    /**
     * @var \Magento\Quote\Model\Quote\Address|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteAddressMock;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerMock;

    /**
     * @var \Magento\Customer\Api\Data\AddressInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerAddressMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $validatorFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $addressFactoryMock;

    protected function setUp()
    {
        $this->customerRepositoryMock = $this->getMockForAbstractClass(
            \Magento\Customer\Api\CustomerRepositoryInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getById']
        );
        $this->customerAddressRepositoryMock = $this->getMockForAbstractClass(
            \Magento\Customer\Api\AddressRepositoryInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getById']
        );
        $this->accountManagementMock = $this->getMockForAbstractClass(
            \Magento\Customer\Api\AccountManagementInterface::class,
            [],
            '',
            false,
            true,
            true,
            []
        );
        $this->quoteMock = $this->getMock(
            \Magento\Quote\Model\Quote::class,
            ['getId', 'getCustomer', 'getBillingAddress', 'getShippingAddress', 'setCustomer', 'getPasswordHash'],
            [],
            '',
            false
        );
        $this->quoteAddressMock = $this->getMock(
            \Magento\Quote\Model\Quote\Address::class,
            [],
            [],
            '',
            false
        );
        $this->customerMock = $this->getMockForAbstractClass(
            \Magento\Customer\Api\Data\CustomerInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getId', 'getDefaultBilling']
        );
        $this->customerAddressMock = $this->getMockForAbstractClass(
            \Magento\Customer\Api\Data\AddressInterface::class,
            [],
            '',
            false,
            true,
            true,
            []
        );
        $this->addressFactoryMock = $this->getMockBuilder(\Magento\Customer\Model\AddressFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->validatorFactoryMock = $this->getMockBuilder(\Magento\Framework\Validator\Factory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerManagement = new \Magento\Quote\Model\CustomerManagement(
            $this->customerRepositoryMock,
            $this->customerAddressRepositoryMock,
            $this->accountManagementMock,
            $this->validatorFactoryMock,
            $this->addressFactoryMock
        );
    }

    public function testPopulateCustomerInfo()
    {
        $this->quoteMock->expects($this->once())
            ->method('getCustomer')
            ->willReturn($this->customerMock);
        $this->customerMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(null);
        $this->customerMock->expects($this->atLeastOnce())
            ->method('getDefaultBilling')
            ->willReturn(100500);
        $this->quoteMock->expects($this->atLeastOnce())
            ->method('getBillingAddress')
            ->willReturn($this->quoteAddressMock);
        $this->quoteMock->expects($this->atLeastOnce())
            ->method('getShippingAddress')
            ->willReturn($this->quoteAddressMock);
        $this->quoteMock->expects($this->atLeastOnce())
            ->method('setCustomer')
            ->with($this->customerMock)
            ->willReturnSelf();
        $this->quoteMock->expects($this->once())
            ->method('getPasswordHash')
            ->willReturn('password hash');
        $this->quoteAddressMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(null);
        $this->customerAddressRepositoryMock->expects($this->atLeastOnce())
            ->method('getById')
            ->with(100500)
            ->willReturn($this->customerAddressMock);
        $this->quoteAddressMock->expects($this->atLeastOnce())
            ->method('importCustomerAddressData')
            ->willReturnSelf();
        $this->accountManagementMock->expects($this->once())
            ->method('createAccountWithPasswordHash')
            ->with($this->customerMock, 'password hash')
            ->willReturn($this->customerMock);
        $this->customerManagement->populateCustomerInfo($this->quoteMock);
    }

    public function testPopulateCustomerInfoForExistingCustomer()
    {
        $this->quoteMock->expects($this->once())
            ->method('getCustomer')
            ->willReturn($this->customerMock);
        $this->customerMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(1);
        $this->customerMock->expects($this->atLeastOnce())
            ->method('getDefaultBilling')
            ->willReturn(100500);
        $this->quoteMock->expects($this->atLeastOnce())
            ->method('getBillingAddress')
            ->willReturn($this->quoteAddressMock);
        $this->quoteMock->expects($this->atLeastOnce())
            ->method('getShippingAddress')
            ->willReturn($this->quoteAddressMock);
        $this->quoteAddressMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(null);
        $this->customerAddressRepositoryMock->expects($this->atLeastOnce())
            ->method('getById')
            ->with(100500)
            ->willReturn($this->customerAddressMock);
        $this->quoteAddressMock->expects($this->atLeastOnce())
            ->method('importCustomerAddressData')
            ->willReturnSelf();
        $this->customerManagement->populateCustomerInfo($this->quoteMock);
    }

    public function testValidateAddresses()
    {
        $this->quoteMock
            ->expects($this->exactly(2))
            ->method('getBillingAddress')
            ->willReturn($this->quoteAddressMock);
        $this->quoteMock
            ->expects($this->exactly(2))
            ->method('getShippingAddress')
            ->willReturn($this->quoteAddressMock);
        $this->quoteAddressMock->expects($this->any())->method('getCustomerAddressId')->willReturn(2);
        $this->customerAddressRepositoryMock
            ->expects($this->any())
            ->method('getById')
            ->willReturn($this->customerAddressMock);
        $validatorMock = $this->getMockBuilder(\Magento\Framework\Validator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $addressMock = $this->getMockBuilder(\Magento\Customer\Model\Address::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->addressFactoryMock->expects($this->exactly(2))->method('create')->willReturn($addressMock);
        $this->validatorFactoryMock
            ->expects($this->exactly(2))
            ->method('createValidator')
            ->with('customer_address', 'save', null)
            ->willReturn($validatorMock);
        $validatorMock->expects($this->exactly(2))->method('isValid')->with($addressMock)->willReturn(true);
        $this->customerManagement->validateAddresses($this->quoteMock);
    }

    public function testValidateAddressesNotSavedInAddressBook()
    {
        $this->quoteMock
            ->expects($this->once())
            ->method('getBillingAddress')
            ->willReturn($this->quoteAddressMock);
        $this->quoteMock
            ->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn($this->quoteAddressMock);
        $this->quoteAddressMock->expects($this->any())->method('getCustomerAddressId')->willReturn(null);
        $this->validatorFactoryMock
            ->expects($this->never())
            ->method('createValidator')
            ->with('customer_address', 'save', null);
        $this->customerManagement->validateAddresses($this->quoteMock);
    }
}
