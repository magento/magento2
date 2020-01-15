<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\AddressFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Validator;
use Magento\Framework\Validator\Factory;
use Magento\Quote\Model\CustomerManagement;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerManagementTest extends TestCase
{
    /**
     * @var \Magento\Quote\Model\CustomerManagement
     */
    private $model;

    /**
     * @var AccountManagementInterface|MockObject
     */
    private $accountManagementMock;

    /**
     * @var AddressRepositoryInterface|MockObject
     */
    private $customerAddressRepositoryMock;

    /**
     * @var Quote|MockObject
     */
    private $quoteMock;

    /**
     * @var Address|MockObject
     */
    private $quoteAddressMock;

    /**
     * @var CustomerInterface|MockObject
     */
    private $customerMock;

    /**
     * @var AddressInterface|MockObject
     */
    private $customerAddressMock;

    /**
     * @var MockObject
     */
    private $validatorFactoryMock;

    /**
     * @var MockObject
     */
    private $addressFactoryMock;

    protected function setUp()
    {
        $this->customerAddressRepositoryMock = $this->getMockForAbstractClass(
            AddressRepositoryInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getById']
        );
        $this->accountManagementMock = $this->getMockForAbstractClass(
            AccountManagementInterface::class,
            [],
            '',
            false,
            true,
            true,
            []
        );
        $this->quoteMock = $this->createPartialMock(
            Quote::class,
            ['getId', 'getCustomer', 'getBillingAddress', 'getShippingAddress', 'setCustomer', 'getPasswordHash']
        );
        $this->quoteAddressMock = $this->createMock(Address::class);
        $this->customerMock = $this->getMockForAbstractClass(
            CustomerInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getId', 'getDefaultBilling']
        );
        $this->customerAddressMock = $this->getMockForAbstractClass(
            AddressInterface::class,
            [],
            '',
            false,
            true,
            true,
            []
        );
        $this->addressFactoryMock = $this->getMockBuilder(AddressFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->validatorFactoryMock = $this->getMockBuilder(Factory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            CustomerManagement::class,
            [
                'customerAddressRepository' => $this->customerAddressRepositoryMock,
                'accountManagement' => $this->accountManagementMock,
                'validatorFactory' => $this->validatorFactoryMock,
                'addressFactory' => $this->addressFactoryMock
            ]
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
        $this->model->populateCustomerInfo($this->quoteMock);
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
        $this->model->populateCustomerInfo($this->quoteMock);
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
        $this->quoteAddressMock->method('getCustomerAddressId')->willReturn(2);
        $this->customerAddressRepositoryMock->method('getById')
            ->willReturn($this->customerAddressMock);
        $validatorMock = $this->getMockBuilder(Validator::class)
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
        $this->model->validateAddresses($this->quoteMock);
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
        $this->quoteAddressMock->method('getCustomerAddressId')->willReturn(null);
        $this->validatorFactoryMock
            ->expects($this->never())
            ->method('createValidator')
            ->with('customer_address', 'save', null);
        $this->model->validateAddresses($this->quoteMock);
    }
}
