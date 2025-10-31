<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\AddressFactory;
use Magento\Framework\Validator;
use Magento\Framework\Validator\Exception as ValidatorException;
use Magento\Framework\Validator\Factory;
use Magento\Quote\Model\CustomerManagement;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerManagementTest extends TestCase
{
    /**
     * @var CustomerManagement
     */
    protected $customerManagement;

    /**
     * @var CustomerRepositoryInterface|MockObject
     */
    protected $customerRepositoryMock;

    /**
     * @var AccountManagementInterface|MockObject
     */
    protected $accountManagementMock;

    /**
     * @var AddressRepositoryInterface|MockObject
     */
    protected $customerAddressRepositoryMock;

    /**
     * @var Quote|MockObject
     */
    protected $quoteMock;

    /**
     * @var Address|MockObject
     */
    protected $quoteAddressMock;

    /**
     * @var CustomerInterface|MockObject
     */
    protected $customerMock;

    /**
     * @var AddressInterface|MockObject
     */
    protected $customerAddressMock;

    /**
     * @var MockObject
     */
    private $validatorFactoryMock;

    /**
     * @var MockObject
     */
    private $addressFactoryMock;

    /**
     * @var MockObject
     */
    private $customerAddressFactoryMock;

    /**
     * @var MockObject
     */
    private $regionFactoryMock;

    protected function setUp(): void
    {
        $this->customerRepositoryMock = $this->getMockForAbstractClass(
            CustomerRepositoryInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getById']
        );
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
        $this->quoteMock = $this->getMockBuilder(Quote::class)
            ->addMethods(['getPasswordHash'])
            ->onlyMethods(['getId', 'getCustomer', 'getBillingAddress', 'getShippingAddress', 'setCustomer',
                'getCustomerIsGuest'])
            ->disableOriginalConstructor()
            ->getMock();
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
            ->onlyMethods(['create'])
            ->getMock();
        $this->validatorFactoryMock = $this->getMockBuilder(Factory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerAddressFactoryMock = $this->getMockForAbstractClass(
            AddressInterfaceFactory::class,
            [],
            '',
            false,
            true,
            true,
            ['create']
        );
        $this->regionFactoryMock = $this->getMockBuilder(RegionInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->customerManagement = new CustomerManagement(
            $this->customerRepositoryMock,
            $this->customerAddressRepositoryMock,
            $this->accountManagementMock,
            $this->customerAddressFactoryMock,
            $this->regionFactoryMock,
            $this->validatorFactoryMock,
            $this->addressFactoryMock
        );
    }

    public function testPopulateCustomerInfo()
    {
        $this->quoteMock->expects($this->atLeastOnce())
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
        $this->customerManagement->validateAddresses($this->quoteMock);
    }

    public function testValidateAddressesNotSavedInAddressBook()
    {
        $this->expectException(ValidatorException::class);

        $regionData = [
            'region' => 'California',
            'region_code' => 'CA',
            'region_id' => 12,
        ];

        $this->quoteMock->method('getCustomerIsGuest')->willReturn(true);
        $this->quoteMock->method('getBillingAddress')->willReturn($this->quoteAddressMock);
        $this->quoteMock->method('getShippingAddress')->willReturn($this->quoteAddressMock);
        $this->quoteAddressMock->method('getCustomerAddressId')->willReturn(null);

        // Set up billing address data
        $this->quoteAddressMock->method('getPrefix')->willReturn('Mr');
        $this->quoteAddressMock->method('getFirstname')->willReturn('John');
        $this->quoteAddressMock->method('getMiddlename')->willReturn('Q');
        $this->quoteAddressMock->method('getLastname')->willReturn('Public');
        $this->quoteAddressMock->method('getSuffix')->willReturn('Jr');
        $this->quoteAddressMock->method('getCompany')->willReturn('Acme Inc.');
        $this->quoteAddressMock->method('getStreet')->willReturn(['123 Main St']);
        $this->quoteAddressMock->method('getCountryId')->willReturn('US');
        $this->quoteAddressMock->method('getCity')->willReturn('Los Angeles');
        $this->quoteAddressMock->method('getPostcode')->willReturn('90001');
        $this->quoteAddressMock->method('getTelephone')->willReturn('1234567890');
        $this->quoteAddressMock->method('getFax')->willReturn('9876543210');
        $this->quoteAddressMock->method('getVatId')->willReturn('US123456789');
        $this->quoteAddressMock->method('getRegion')->willReturn($regionData);
        $this->quoteAddressMock->method('getCustomAttributes')->willReturn(['custom_attr' => 'value']);

        // Region setup
        $regionMock = $this->createMock(RegionInterface::class);
        $this->regionFactoryMock->method('create')->willReturn($regionMock);
        $regionMock->expects($this->once())->method('setRegion')->with('California')->willReturnSelf();
        $regionMock->expects($this->once())->method('setRegionCode')->with('CA')->willReturnSelf();
        $regionMock->expects($this->once())->method('setRegionId')->with(12)->willReturnSelf();

        // Customer address object to be created
        $this->customerAddressFactoryMock->method('create')->willReturn($this->customerAddressMock);
        $this->customerAddressMock->expects($this->once())->method('setPrefix')->with('Mr');
        $this->customerAddressMock->expects($this->once())->method('setFirstname')->with('John');
        $this->customerAddressMock->expects($this->once())->method('setMiddlename')->with('Q');
        $this->customerAddressMock->expects($this->once())->method('setLastname')->with('Public');
        $this->customerAddressMock->expects($this->once())->method('setSuffix')->with('Jr');
        $this->customerAddressMock->expects($this->once())->method('setCompany')->with('Acme Inc.');
        $this->customerAddressMock->expects($this->once())->method('setStreet')->with(['123 Main St']);
        $this->customerAddressMock->expects($this->once())->method('setCountryId')->with('US');
        $this->customerAddressMock->expects($this->once())->method('setCity')->with('Los Angeles');
        $this->customerAddressMock->expects($this->once())->method('setPostcode')->with('90001');
        $this->customerAddressMock->expects($this->once())->method('setTelephone')->with('1234567890');
        $this->customerAddressMock->expects($this->once())->method('setFax')->with('9876543210');
        $this->customerAddressMock->expects($this->once())->method('setVatId')->with('US123456789');
        $this->customerAddressMock->expects($this->once())->method('setRegion')->with($regionMock);
        $this->customerAddressMock
            ->expects($this->once())
            ->method('setCustomAttributes')
            ->with(['custom_attr' => 'value']);

        // Validator to fail
        $validatorMock = $this->createMock(Validator::class);
        $this->validatorFactoryMock->method('createValidator')->willReturn($validatorMock);
        $validatorMock->expects($this->once())->method('isValid')->willReturn(false);
        $validatorMock->expects($this->once())->method('getMessages')->willReturn([]);

        $addressModelMock = $this->createMock(\Magento\Customer\Model\Address::class);
        $this->addressFactoryMock->method('create')->willReturn($addressModelMock);

        $this->customerManagement->validateAddresses($this->quoteMock);
    }
}
