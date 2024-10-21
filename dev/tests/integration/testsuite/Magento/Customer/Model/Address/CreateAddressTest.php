<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Address;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Model\AddressRegistry;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\ResourceModel\Address;
use Magento\Customer\Model\Vat;
use Magento\Customer\Observer\AfterAddressSaveObserver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\InputException;
use Magento\TestFramework\Directory\Model\GetRegionIdByName;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface as PsrLogger;

/**
 * Assert that address was created as expected or address create throws expected error.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @magentoDbIsolation enabled
 */
class CreateAddressTest extends TestCase
{
    /**
     * Static customer address data.
     */
    private const STATIC_CUSTOMER_ADDRESS_DATA = [
        AddressInterface::TELEPHONE => 3468676,
        AddressInterface::POSTCODE => 75477,
        AddressInterface::COUNTRY_ID => 'US',
        'custom_region_name' => 'Alabama',
        AddressInterface::CITY => 'CityM',
        AddressInterface::STREET => ['Green str, 67'],
        AddressInterface::LASTNAME => 'Smith',
        AddressInterface::FIRSTNAME => 'John',
    ];

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var AddressInterfaceFactory
     */
    protected $addressFactory;

    /**
     * @var CustomerRegistry
     */
    protected $customerRegistry;

    /**
     * @var AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @var GetRegionIdByName
     */
    protected $getRegionIdByName;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var AddressRegistry
     */
    private $addressRegistry;

    /**
     * @var Address
     */
    private $addressResource;

    /**
     * @var int[]
     */
    private $createdAddressesIds = [];

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->addressFactory = $this->objectManager->get(AddressInterfaceFactory::class);
        $this->customerRegistry = $this->objectManager->get(CustomerRegistry::class);
        $this->addressRepository = $this->objectManager->get(AddressRepositoryInterface::class);
        $this->getRegionIdByName = $this->objectManager->get(GetRegionIdByName::class);
        $this->customerRepository = $this->objectManager->get(CustomerRepositoryInterface::class);
        $this->addressRegistry = $this->objectManager->get(AddressRegistry::class);
        $this->addressResource = $this->objectManager->get(Address::class);
        $this->dataObjectFactory = $this->objectManager->get(DataObjectFactory::class);
        parent::setUp();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        foreach ($this->createdAddressesIds as $createdAddressesId) {
            $this->addressRegistry->remove($createdAddressesId);
        }
        $this->objectManager->removeSharedInstance(AfterAddressSaveObserver::class);
        parent::tearDown();
    }

    /**
     * Assert that default addresses properly created for customer.
     *
     * @magentoDataFixture Magento/Customer/_files/customer_no_address.php
     *
     * @dataProvider createDefaultAddressesDataProvider
     *
     * @param array $addressData
     * @param bool $isShippingDefault
     * @param bool $isBillingDefault
     * @return void
     */
    public function testCreateDefaultAddress(
        array $addressData,
        bool $isShippingDefault,
        bool $isBillingDefault
    ): void {
        $customer = $this->customerRepository->get('customer5@example.com');
        $this->assertNull($customer->getDefaultShipping(), 'Customer already has default shipping address');
        $this->assertNull($customer->getDefaultBilling(), 'Customer already has default billing address');
        $address = $this->createAddress(
            (int)$customer->getId(),
            $addressData,
            $isShippingDefault,
            $isBillingDefault
        );
        $expectedShipping = $isShippingDefault ? $address->getId() : null;
        $expectedBilling = $isBillingDefault ? $address->getId() : null;
        $customer = $this->customerRepository->get('customer5@example.com');
        $this->assertEquals($expectedShipping, $customer->getDefaultShipping());
        $this->assertEquals($expectedBilling, $customer->getDefaultBilling());
    }

    /**
     * Data provider for create default or not default address.
     *
     * @return array
     */
    public static function createDefaultAddressesDataProvider(): array
    {
        return [
            'any_addresses_are_default' => [self::STATIC_CUSTOMER_ADDRESS_DATA, false, false],
            'shipping_address_is_default' => [self::STATIC_CUSTOMER_ADDRESS_DATA, true, false],
            'billing_address_is_default' => [self::STATIC_CUSTOMER_ADDRESS_DATA, false, true],
            'all_addresses_are_default' => [self::STATIC_CUSTOMER_ADDRESS_DATA, true, true],
        ];
    }

    /**
     * Assert that address created successfully.
     *
     * @magentoDataFixture Magento/Customer/_files/customer_no_address.php
     *
     * @dataProvider createAddressesDataProvider
     *
     * @param array $addressData
     * @param array $expectedData
     * @return void
     */
    public function testAddressCreatedWithProperData(array $addressData, array $expectedData): void
    {
        if (isset($expectedData['custom_region_name'])) {
            $expectedData[AddressInterface::REGION_ID] = $this->getRegionIdByName->execute(
                $expectedData['custom_region_name'],
                $expectedData[AddressInterface::COUNTRY_ID]
            );
            unset($expectedData['custom_region_name']);
        }
        $customer = $this->customerRepository->get('customer5@example.com');
        $createdAddressData = $this->createAddress((int)$customer->getId(), $addressData)->__toArray();
        foreach ($expectedData as $fieldCode => $expectedValue) {
            $this->assertTrue(isset($createdAddressData[$fieldCode]), "Field $fieldCode wasn't found.");
            $this->assertEquals($createdAddressData[$fieldCode], $expectedValue);
        }
    }

    /**
     * Data provider for create address with proper data.
     *
     * @return array
     */
    public static function createAddressesDataProvider(): array
    {
        return [
            'required_fields_valid_data' => [
                self::STATIC_CUSTOMER_ADDRESS_DATA,
                [
                    AddressInterface::TELEPHONE => 3468676,
                    AddressInterface::COUNTRY_ID => 'US',
                    AddressInterface::POSTCODE => 75477,
                    'custom_region_name' => 'Alabama',
                    AddressInterface::FIRSTNAME => 'John',
                    AddressInterface::LASTNAME => 'Smith',
                    AddressInterface::STREET => ['Green str, 67'],
                    AddressInterface::CITY => 'CityM',
                ],
            ],
            'required_field_empty_postcode_for_uk' => [
                array_replace(
                    self::STATIC_CUSTOMER_ADDRESS_DATA,
                    [AddressInterface::POSTCODE => '', AddressInterface::COUNTRY_ID => 'GB']
                ),
                [
                    AddressInterface::COUNTRY_ID => 'GB',
                    AddressInterface::POSTCODE => null,
                ],
            ],
            'required_field_empty_region_id_for_ua' => [
                array_replace(
                    self::STATIC_CUSTOMER_ADDRESS_DATA,
                    [AddressInterface::REGION_ID => '', AddressInterface::COUNTRY_ID => 'UA']
                ),
                [
                    AddressInterface::COUNTRY_ID => 'UA',
                    AddressInterface::REGION => [
                        'region' => null,
                        'region_code' => null,
                        'region_id' => 0,
                    ],
                ],
            ],
            'required_field_street_as_array' => [
                array_replace(self::STATIC_CUSTOMER_ADDRESS_DATA, [AddressInterface::STREET => ['', 'Green str, 67']]),
                [AddressInterface::STREET => ['Green str, 67']],
            ],
            'field_name_prefix' => [
                array_merge(self::STATIC_CUSTOMER_ADDRESS_DATA, [AddressInterface::PREFIX => 'My prefix']),
                [AddressInterface::PREFIX => 'My prefix'],
            ],
            'field_middle_name_initial' => [
                array_merge(self::STATIC_CUSTOMER_ADDRESS_DATA, [AddressInterface::MIDDLENAME => 'My middle name']),
                [AddressInterface::MIDDLENAME => 'My middle name'],
            ],
            'field_name_suffix' => [
                array_merge(self::STATIC_CUSTOMER_ADDRESS_DATA, [AddressInterface::SUFFIX => 'My suffix']),
                [AddressInterface::SUFFIX => 'My suffix'],
            ],
            'field_company_name' => [
                array_merge(self::STATIC_CUSTOMER_ADDRESS_DATA, [AddressInterface::COMPANY => 'My company']),
                [AddressInterface::COMPANY => 'My company'],
            ],
            'field_vat_number' => [
                array_merge(self::STATIC_CUSTOMER_ADDRESS_DATA, [AddressInterface::VAT_ID => 'My VAT number']),
                [AddressInterface::VAT_ID => 'My VAT number'],
            ],
        ];
    }

    /**
     * Assert that proper error message has thrown if address creating with wrong data.
     *
     * @magentoDataFixture Magento/Customer/_files/customer_no_address.php
     *
     * @dataProvider createWrongAddressesDataProvider
     *
     * @param array $addressData
     * @param \Exception $expectException
     * @return void
     */
    public function testExceptionThrownDuringCreateAddress(array $addressData, \Exception $expectException): void
    {
        $customer = $this->customerRepository->get('customer5@example.com');
        $this->expectExceptionObject($expectException);
        $this->createAddress((int)$customer->getId(), $addressData);
    }

    /**
     * Data provider for create address with wrong data.
     *
     * @return array
     */
    public static function createWrongAddressesDataProvider(): array
    {
        return [
            'required_field_empty_telephone' => [
                array_replace(self::STATIC_CUSTOMER_ADDRESS_DATA, [AddressInterface::TELEPHONE => '']),
                InputException::requiredField('telephone'),
            ],
            'required_field_empty_postcode_for_us' => [
                array_replace(self::STATIC_CUSTOMER_ADDRESS_DATA, [AddressInterface::POSTCODE => '']),
                InputException::requiredField('postcode'),
            ],
// TODO: Uncomment this variation after fix issue https://jira.corp.magento.com/browse/MC-31031
//            'required_field_empty_region_id_for_us' => [
//                array_replace(self::STATIC_CUSTOMER_ADDRESS_DATA, [AddressInterface::REGION_ID => '']),
//                InputException::requiredField('regionId'),
//            ],
            'required_field_empty_firstname' => [
                array_replace(self::STATIC_CUSTOMER_ADDRESS_DATA, [AddressInterface::FIRSTNAME => '']),
                InputException::requiredField('firstname'),
            ],
            'required_field_empty_lastname' => [
                array_replace(self::STATIC_CUSTOMER_ADDRESS_DATA, [AddressInterface::LASTNAME => '']),
                InputException::requiredField('lastname'),
            ],
            'required_field_empty_street_as_array' => [
                array_replace(self::STATIC_CUSTOMER_ADDRESS_DATA, [AddressInterface::STREET => []]),
                InputException::requiredField('street'),
            ],
            'required_field_empty_city' => [
                array_replace(self::STATIC_CUSTOMER_ADDRESS_DATA, [AddressInterface::CITY => '']),
                InputException::requiredField('city'),
            ],
// TODO: Uncomment this variation after fix issue https://jira.corp.magento.com/browse/MC-31031
//            'field_invalid_vat_number' => [
//                array_merge(self::STATIC_CUSTOMER_ADDRESS_DATA, [AddressInterface::VAT_ID => '/>.<*']),
//                null// It need to create some error but currently magento doesn't has validation for this field.,
//            ],
        ];
    }

    /**
     * Assert that after address creation customer group is Group for Valid VAT ID - Domestic.
     *
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Customer/_files/customer_no_address.php
     * @magentoConfigFixture current_store general/store_information/country_id AT
     * @magentoConfigFixture current_store customer/create_account/auto_group_assign 1
     * @magentoConfigFixture current_store customer/create_account/viv_domestic_group 2
     * @return void
     */
    public function testAddressCreatedWithGroupAssignByDomesticVatId(): void
    {
        $this->createVatMock(true, true);
        $addressData = array_merge(
            self::STATIC_CUSTOMER_ADDRESS_DATA,
            [AddressInterface::VAT_ID => '111', AddressInterface::COUNTRY_ID => 'AT']
        );
        $customer = $this->customerRepository->get('customer5@example.com');
        $this->createAddress((int)$customer->getId(), $addressData, false, true);
        $this->assertEquals(2, $this->getCustomerGroupId('customer5@example.com'));
    }

    /**
     * Assert that after address creation customer group is Group for Valid VAT ID - Intra-Union.
     *
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Customer/_files/customer_no_address.php
     * @magentoConfigFixture current_store general/store_information/country_id GR
     * @magentoConfigFixture current_store customer/create_account/auto_group_assign 1
     * @magentoConfigFixture current_store customer/create_account/viv_intra_union_group 2
     * @return void
     */
    public function testAddressCreatedWithGroupAssignByIntraUnionVatId(): void
    {
        $this->createVatMock(true, true);
        $addressData = array_merge(
            self::STATIC_CUSTOMER_ADDRESS_DATA,
            [AddressInterface::VAT_ID => '111', AddressInterface::COUNTRY_ID => 'AT']
        );
        $customer = $this->customerRepository->get('customer5@example.com');
        $this->createAddress((int)$customer->getId(), $addressData, false, true);
        $this->assertEquals(2, $this->getCustomerGroupId('customer5@example.com'));
    }

    /**
     * Assert that after address creation customer group is Group for Invalid VAT ID.
     *
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Customer/_files/customer_no_address.php
     * @magentoConfigFixture current_store customer/create_account/auto_group_assign 1
     * @magentoConfigFixture current_store customer/create_account/viv_invalid_group 2
     * @return void
     */
    public function testAddressCreatedWithGroupAssignByInvalidVatId(): void
    {
        $this->createVatMock(false, true);
        $addressData = array_merge(
            self::STATIC_CUSTOMER_ADDRESS_DATA,
            [AddressInterface::VAT_ID => '111', AddressInterface::COUNTRY_ID => 'AT']
        );
        $customer = $this->customerRepository->get('customer5@example.com');
        $this->createAddress((int)$customer->getId(), $addressData, false, true);
        $this->assertEquals(2, $this->getCustomerGroupId('customer5@example.com'));
    }

    /**
     * Assert that after address creation customer group is Validation Error Group.
     *
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Customer/_files/customer_no_address.php
     * @magentoConfigFixture current_store customer/create_account/auto_group_assign 1
     * @magentoConfigFixture current_store customer/create_account/viv_error_group 2
     * @return void
     */
    public function testAddressCreatedWithGroupAssignByVatIdWithError(): void
    {
        $this->createVatMock(false, false);
        $addressData = array_merge(
            self::STATIC_CUSTOMER_ADDRESS_DATA,
            [AddressInterface::VAT_ID => '111', AddressInterface::COUNTRY_ID => 'AT']
        );
        $customer = $this->customerRepository->get('customer5@example.com');
        $this->createAddress((int)$customer->getId(), $addressData, false, true);
        $this->assertEquals(2, $this->getCustomerGroupId('customer5@example.com'));
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_no_address.php
     * @magentoDataFixture Magento/Store/_files/second_website_with_store_group_and_store.php
     * @magentoConfigFixture default_store general/country/allow BD,BB,AF
     * @magentoConfigFixture fixture_second_store_store general/country/allow AS,BM
     *
     * @return void
     */
    public function testCreateAvailableAddress(): void
    {
        $countryId = 'BB';
        $addressData = array_merge(self::STATIC_CUSTOMER_ADDRESS_DATA, [AddressInterface::COUNTRY_ID => $countryId]);
        $customer = $this->customerRepository->get('customer5@example.com');
        $address = $this->createAddress((int)$customer->getId(), $addressData);
        $this->assertSame($countryId, $address->getCountryId());
    }

    /**
     * Create customer address with provided address data.
     *
     * @param int $customerId
     * @param array $addressData
     * @param bool $isDefaultShipping
     * @param bool $isDefaultBilling
     * @return AddressInterface
     */
    protected function createAddress(
        int $customerId,
        array $addressData,
        bool $isDefaultShipping = false,
        bool $isDefaultBilling = false
    ): AddressInterface {
        if (isset($addressData['custom_region_name'])) {
            $addressData[AddressInterface::REGION_ID] = $this->getRegionIdByName->execute(
                $addressData['custom_region_name'],
                $addressData[AddressInterface::COUNTRY_ID]
            );
            unset($addressData['custom_region_name']);
        }

        $addressData['attribute_set_id'] = $this->addressResource->getEntityType()->getDefaultAttributeSetId();
        $address = $this->addressFactory->create(['data' => $addressData]);
        $address->setCustomerId($customerId);
        $address->setIsDefaultShipping($isDefaultShipping);
        $address->setIsDefaultBilling($isDefaultBilling);
        $address = $this->addressRepository->save($address);
        $this->customerRegistry->remove($customerId);
        $this->addressRegistry->remove($address->getId());
        $this->createdAddressesIds[] = (int)$address->getId();

        return $address;
    }

    /**
     * Creates mock for vat id validation.
     *
     * @param bool $isValid
     * @param bool $isRequestSuccess
     * @return void
     */
    private function createVatMock(bool $isValid = false, bool $isRequestSuccess = false): void
    {
        $gatewayResponse = $this->dataObjectFactory->create(
            [
                'data' => [
                    'is_valid' => $isValid,
                    'request_date' => '',
                    'request_identifier' => '123123123',
                    'request_success' => $isRequestSuccess,
                    'request_message' => '',
                ],
            ]
        );
        $customerVat = $this->getMockBuilder(Vat::class)
            ->setConstructorArgs(
                [
                    $this->objectManager->get(ScopeConfigInterface::class),
                    $this->objectManager->get(PsrLogger::class)
                ]
            )
            ->onlyMethods(['checkVatNumber'])
            ->getMock();
        $customerVat->method('checkVatNumber')->willReturn($gatewayResponse);
        $this->objectManager->removeSharedInstance(Vat::class);
        $this->objectManager->addSharedInstance($customerVat, Vat::class);
    }

    /**
     * Returns customer group id by email.
     *
     * @param string $email
     * @return int
     */
    private function getCustomerGroupId(string $email): int
    {
        return (int)$this->customerRepository->get($email)->getGroupId();
    }
}
