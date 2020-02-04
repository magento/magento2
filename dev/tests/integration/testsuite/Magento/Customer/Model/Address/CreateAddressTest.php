<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Address;

use Exception;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Model\AddressRegistry;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\ResourceModel\Address;
use Magento\Framework\Exception\InputException;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Assert that address was created as expected or address create throws expected error.
 *
 * @magentoDbIsolation enabled
 */
class CreateAddressTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var AddressInterfaceFactory
     */
    private $addressFactory;

    /**
     * @var AddressRegistry
     */
    private $addressRegistry;

    /**
     * @var Address
     */
    private $addressResource;

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var int[]
     */
    private $createdCustomerIds = [];

    /**
     * @var int[]
     */
    private $createdAddressesIds = [];

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->addressFactory = $this->objectManager->get(AddressInterfaceFactory::class);
        $this->addressRegistry = $this->objectManager->get(AddressRegistry::class);
        $this->addressResource = $this->objectManager->get(Address::class);
        $this->customerRegistry = $this->objectManager->get(CustomerRegistry::class);
        $this->addressRepository = $this->objectManager->get(AddressRepositoryInterface::class);
        $this->customerRepository = $this->objectManager->get(CustomerRepositoryInterface::class);
        parent::setUp();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        foreach ($this->createdCustomerIds as $createdCustomerId) {
            $this->customerRegistry->remove($createdCustomerId);
        }

        foreach ($this->createdAddressesIds as $createdAddressesId) {
            $this->addressRegistry->remove($createdAddressesId);
        }
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
        $this->createdCustomerIds[] = (int)$customer->getId();
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
    public function createDefaultAddressesDataProvider(): array
    {
        $staticAddressData = $this->getStaticAddressData();

        return [
            'any_addresses_are_default' => [$staticAddressData, false, false],
            'shipping_address_is_default' => [$staticAddressData, true, false],
            'billing_address_is_default' => [$staticAddressData, false, true],
            'all_addresses_are_default' => [$staticAddressData, true, true],
        ];
    }

    /**
     * Assert that address created successfully or proper error message has thrown.
     *
     * @magentoDataFixture Magento/Customer/_files/customer_no_address.php
     *
     * @dataProvider createAddressesDataProvider
     *
     * @param array $addressData
     * @param array $expectedData
     * @param Exception|null $expectException
     * @return void
     */
    public function testAddressCreatedWithProperData(
        array $addressData,
        array $expectedData,
        ?Exception $expectException = null
    ): void {
        $customer = $this->customerRepository->get('customer5@example.com');
        $this->createdCustomerIds[] = (int)$customer->getId();
        if (null !== $expectException) {
            $this->expectExceptionObject($expectException);
        }
        $createdAddress = $this->createAddress((int)$customer->getId(), $addressData);
        foreach ($expectedData as $getMethodName => $expectedValue) {
            $this->assertEquals($createdAddress->$getMethodName(), $expectedValue);
        }
    }

    /**
     * Data provider for create address with proper data or with error.
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     *
     * @return array
     */
    public function createAddressesDataProvider(): array
    {
        $addressStaticData = $this->getStaticAddressData();

        return [
            'required_fields_valid_data' => [
                $addressStaticData,
                [
                    'getTelephone' => 3468676,
                    'getCountryId' => 'US',
                    'getPostcode' => 75477,
                    'getRegionId' => 1,
                    'getFirstname' => 'John',
                    'getLastname' => 'Smith',
                    'getStreet' => ['Green str, 67'],
                    'getCity' => 'CityM',
                    'getPrefix' => null,
                    'getMiddlename' => null,
                    'getSuffix' => null,
                    'getCompany' => null,
                    'getVatId' => null,
                ],
                null,
            ],
            'required_field_empty_telephone' => [
                array_replace($addressStaticData, [AddressInterface::TELEPHONE => '']),
                [],
                $this->createInputException('"telephone" is required. Enter and try again.'),
            ],
            'required_field_empty_postcode_for_us' => [
                array_replace($addressStaticData, [AddressInterface::POSTCODE => '']),
                [],
                $this->createInputException('"postcode" is required. Enter and try again.'),
            ],
            'required_field_empty_postcode_for_uk' => [
                array_replace(
                    $addressStaticData,
                    [AddressInterface::POSTCODE => '', AddressInterface::COUNTRY_ID => 'GB']
                ),
                [
                    'getCountryId' => 'GB',
                    'getPostcode' => null,
                ],
                null,
            ],
// TODO: Uncomment this variation after fix issue https://jira.corp.magento.com/browse/MC-31031
//            'required_field_empty_region_id_for_us' => [
//                array_replace($addressStaticData, [AddressInterface::REGION_ID => '']),
//                [],
//                $this->createInputException('"regionId" is required. Enter and try again.'),
//            ],
            'required_field_empty_region_id_for_ua' => [
                array_replace(
                    $addressStaticData,
                    [AddressInterface::REGION_ID => '', AddressInterface::COUNTRY_ID => 'UA']
                ),
                [
                    'getCountryId' => 'UA',
                    'getRegionId' => null,
                ],
                null,
            ],
            'required_field_empty_firstname' => [
                array_replace($addressStaticData, [AddressInterface::FIRSTNAME => '']),
                [],
                $this->createInputException('"firstname" is required. Enter and try again.'),
            ],
            'required_field_empty_lastname' => [
                array_replace($addressStaticData, [AddressInterface::LASTNAME => '']),
                [],
                $this->createInputException('"lastname" is required. Enter and try again.'),
            ],
            'required_field_empty_street_as_string' => [
                array_replace($addressStaticData, [AddressInterface::STREET => '']),
                [],
                $this->createInputException('"street" is required. Enter and try again.'),
            ],
            'required_field_empty_street_as_array' => [
                array_replace($addressStaticData, [AddressInterface::STREET => []]),
                [],
                $this->createInputException('"street" is required. Enter and try again.'),
            ],
            'required_field_street_as_array' => [
                array_replace($addressStaticData, [AddressInterface::STREET => ['', 'Green str, 67']]),
                ['getStreet' => ['Green str, 67']],
                null
            ],
            'required_field_empty_city' => [
                array_replace($addressStaticData, [AddressInterface::CITY => '']),
                [],
                $this->createInputException('"city" is required. Enter and try again.'),
            ],
            'field_name_prefix' => [
                array_merge($addressStaticData, [AddressInterface::PREFIX => 'My prefix']),
                ['getPrefix' => 'My prefix'],
                null,
            ],
            'field_middle_name_initial' => [
                array_merge($addressStaticData, [AddressInterface::MIDDLENAME => 'My middle name']),
                ['getMiddlename' => 'My middle name'],
                null,
            ],
            'field_name_suffix' => [
                array_merge($addressStaticData, [AddressInterface::SUFFIX => 'My suffix']),
                ['getSuffix' => 'My suffix'],
                null,
            ],
            'field_company_name' => [
                array_merge($addressStaticData, [AddressInterface::COMPANY => 'My company']),
                ['getCompany' => 'My company'],
                null,
            ],
            'field_vat_number' => [
                array_merge($addressStaticData, [AddressInterface::VAT_ID => 'My VAT number']),
                ['getVatId' => 'My VAT number'],
                null,
            ],
// TODO: Uncomment this variation after fix issue https://jira.corp.magento.com/browse/MC-31031
//            'field_invalid_vat_number' => [
//                array_merge($addressStaticData, [AddressInterface::VAT_ID => '/>.<*']),
//                [],
//                null// It need to create some error but currently magento doesn't has validation for this field.,
//            ],
        ];
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
    private function createAddress(
        int $customerId,
        array $addressData,
        bool $isDefaultShipping = false,
        bool $isDefaultBilling = false
    ): AddressInterface {
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
     * Create InputException with provided error text.
     *
     * @param string $string
     * @return InputException
     */
    private function createInputException(string $string): InputException
    {
        $inputException = new InputException();
        $inputException->addError(__($string));

        return $inputException;
    }

    /**
     * Get static address data.
     *
     * @return array
     */
    private function getStaticAddressData(): array
    {
        return [
            AddressInterface::TELEPHONE => 3468676,
            AddressInterface::POSTCODE => 75477,
            AddressInterface::COUNTRY_ID => 'US',
            AddressInterface::REGION_ID => 1,
            AddressInterface::CITY => 'CityM',
            AddressInterface::STREET => 'Green str, 67',
            AddressInterface::LASTNAME => 'Smith',
            AddressInterface::FIRSTNAME => 'John',
        ];
    }
}
