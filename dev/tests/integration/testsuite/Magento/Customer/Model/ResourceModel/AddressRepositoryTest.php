<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\ResourceModel;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Config\CacheInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Class with integration tests for AddressRepository.
 *
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddressRepositoryTest extends TestCase
{
    /** @var AddressRepositoryInterface */
    private $repository;

    /** @var \Magento\Framework\ObjectManagerInterface */
    private $objectManager;

    /** @var \Magento\Customer\Model\Data\Address[] */
    private $expectedAddresses;

    /** @var AddressInterfaceFactory */
    private $addressFactory;

    /** @var  DataObjectHelper */
    private $dataObjectHelper;

    /** @var CustomerRegistry */
    private $customerRegistry;

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var RegionInterfaceFactory */
    private $regionFactory;

    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        /* @var CacheInterface $cache */
        $cache = $this->objectManager->get(CacheInterface::class);
        $cache->remove('extension_attributes_config');
        $this->repository = $this->objectManager->get(AddressRepositoryInterface::class);
        $this->addressFactory = $this->objectManager->get(AddressInterfaceFactory::class);
        $this->dataObjectHelper = $this->objectManager->get(DataObjectHelper::class);
        $this->customerRegistry = $this->objectManager->get(CustomerRegistry::class);
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $this->regionFactory = $this->objectManager->get(RegionInterfaceFactory::class);
        $this->customerRepository = $this->objectManager->get(CustomerRepositoryInterface::class);
        $region = $this->regionFactory->create()
            ->setRegionCode('AL')
            ->setRegion('Alabama')
            ->setRegionId(1);
        $address = $this->addressFactory->create()
            ->setId('1')
            ->setCountryId('US')
            ->setCustomerId('1')
            ->setPostcode('75477')
            ->setRegion($region)
            ->setRegionId(1)
            ->setStreet(['Green str, 67'])
            ->setTelephone('3468676')
            ->setCity('CityM')
            ->setFirstname('John')
            ->setLastname('Smith')
            ->setCompany('CompanyName');
        $address2 = $this->addressFactory->create()
            ->setId('2')
            ->setCountryId('US')
            ->setCustomerId('1')
            ->setPostcode('47676')
            ->setRegion($region)
            ->setRegionId(1)
            ->setStreet(['Black str, 48'])
            ->setCity('CityX')
            ->setTelephone('3234676')
            ->setFirstname('John')
            ->setLastname('Smith');
        $this->expectedAddresses = [$address, $address2];
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->customerRegistry->remove(1);
    }

    /**
     * Test for save address changes.
     *
     * @magentoDataFixture  Magento/Customer/_files/customer.php
     * @magentoDataFixture  Magento/Customer/_files/customer_address.php
     * @magentoDataFixture  Magento/Customer/_files/customer_two_addresses.php
     * @magentoAppIsolation enabled
     * @return void
     */
    public function testSaveAddressChanges(): void
    {
        $address = $this->repository->getById(2);

        $proposedAddressObject = $address;
        $proposedAddressObject->setRegion($address->getRegion());
        // change phone #
        $proposedAddressObject->setTelephone('555' . $address->getTelephone());
        $proposedAddress = $this->repository->save($proposedAddressObject);
        $this->assertEquals(2, $proposedAddress->getId());

        $savedAddress = $this->repository->getById(2);
        $this->assertNotEquals($this->expectedAddresses[1]->getTelephone(), $savedAddress->getTelephone());
    }

    /**
     * Test for method save address with new id.
     *
     * @magentoDataFixture  Magento/Customer/_files/customer.php
     * @magentoDataFixture  Magento/Customer/_files/customer_address.php
     * @magentoDataFixture  Magento/Customer/_files/customer_two_addresses.php
     * @magentoAppIsolation enabled
     * @return void
     */
    public function testSaveAddressesIdSetButNotAlreadyExisting(): void
    {
        $message = (string)__(
            'No such entity with %fieldName = %fieldValue',
            [
                'fieldName' => 'addressId',
                'fieldValue' => 4200,
            ]
        );
        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage($message);

        $proposedAddress = $this->_createSecondAddress()->setId(4200);
        $this->repository->save($proposedAddress);
    }

    /**
     * Test for method get address by id.
     *
     * @magentoDataFixture  Magento/Customer/_files/customer.php
     * @magentoDataFixture  Magento/Customer/_files/customer_address.php
     * @magentoDataFixture  Magento/Customer/_files/customer_two_addresses.php
     * @magentoAppIsolation enabled
     * @return void
     */
    public function testGetAddressById(): void
    {
        $addressId = 2;
        $address = $this->repository->getById($addressId);
        $this->assertEquals($this->expectedAddresses[1], $address);
    }

    /**
     * Test for method get address by id with incorrect id.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @return void
     */
    public function testGetAddressByIdBadAddressId(): void
    {
        $message = (string)__(
            'No such entity with %fieldName = %fieldValue',
            [
                'fieldName' => 'addressId',
                'fieldValue' => 12345,
            ]
        );
        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage($message);

        $this->repository->getById(12345);
    }

    /**
     * Test for method save new address.
     *
     * @magentoDataFixture  Magento/Customer/_files/customer.php
     * @magentoDataFixture  Magento/Customer/_files/customer_address.php
     * @magentoAppIsolation enabled
     * @return void
     */
    public function testSaveNewAddress(): void
    {
        $proposedAddress = $this->_createSecondAddress()->setCustomerId(1);

        $returnedAddress = $this->repository->save($proposedAddress);
        $this->assertNotNull($returnedAddress->getId());

        $savedAddress = $this->repository->getById($returnedAddress->getId());

        $expectedNewAddress = $this->expectedAddresses[1];
        $expectedNewAddress->setId($savedAddress->getId());
        $expectedNewAddress->setRegion($this->expectedAddresses[1]->getRegion());

        $this->assertEquals($expectedNewAddress->getExtensionAttributes(), $savedAddress->getExtensionAttributes());
        $this->assertEquals(
            $expectedNewAddress->getRegion()->getExtensionAttributes(),
            $savedAddress->getRegion()->getExtensionAttributes()
        );
        $this->assertEquals($expectedNewAddress, $savedAddress);
    }

    /**
     * Test for method saaveNewAddress with new attributes.
     *
     * @magentoDataFixture  Magento/Customer/_files/customer.php
     * @magentoDataFixture  Magento/Customer/_files/customer_address.php
     * @magentoAppIsolation enabled
     * @return void
     */
    public function testSaveNewAddressWithAttributes(): void
    {
        $proposedAddress = $this->_createFirstAddress()
            ->setCustomAttribute('firstname', 'Jane')
            ->setCustomAttribute('id', 4200)
            ->setCustomAttribute('weird', 'something_strange_with_hair')
            ->setId(null)
            ->setCustomerId(1);

        $returnedAddress = $this->repository->save($proposedAddress);

        $savedAddress = $this->repository->getById($returnedAddress->getId());
        $this->assertNotEquals($proposedAddress, $savedAddress);
        $this->assertArrayNotHasKey(
            'weird',
            $savedAddress->getCustomAttributes(),
            'Only valid attributes should be available.'
        );
    }

    /**
     * Test for saving address with invalid address.
     *
     * @magentoDataFixture  Magento/Customer/_files/customer.php
     * @magentoDataFixture  Magento/Customer/_files/customer_address.php
     * @magentoAppIsolation enabled
     * @return void
     */
    public function testSaveNewInvalidAddress(): void
    {
        $address = $this->_createFirstAddress()
            ->setCustomAttribute('firstname', null)
            ->setId(null)
            ->setFirstname(null)
            ->setLastname(null)
            ->setCustomerId(1);
        try {
            $this->repository->save($address);
        } catch (InputException $exception) {
            $this->assertEquals('One or more input exceptions have occurred.', $exception->getMessage());
            $errors = $exception->getErrors();
            $this->assertCount(2, $errors);
            $this->assertEquals('"firstname" is required. Enter and try again.', $errors[0]->getLogMessage());
            $this->assertEquals('"lastname" is required. Enter and try again.', $errors[1]->getLogMessage());
        }
    }

    /**
     * Test for saving address without existing customer.
     *
     * @return void
     */
    public function testSaveAddressesCustomerIdNotExist(): void
    {
        $message = (string)__(
            'No such entity with %fieldName = %fieldValue',
            [
                'fieldName' => 'customerId',
                'fieldValue' => 4200,
            ]
        );
        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage($message);
        $proposedAddress = $this->_createSecondAddress()->setCustomerId(4200);
        $this->repository->save($proposedAddress);
    }

    /**
     * Test for saving addresses with invalid customer id.
     *
     * @return void
     */
    public function testSaveAddressesCustomerIdInvalid(): void
    {
        $message = (string)__(
            'No such entity with %fieldName = %fieldValue',
            [
                'fieldName' => 'customerId',
                'fieldValue' => 'this_is_not_a_valid_id',
            ]
        );
        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage($message);
        $proposedAddress = $this->_createSecondAddress()->setCustomerId('this_is_not_a_valid_id');
        $this->repository->save($proposedAddress);
    }

    /**
     * Test for delete method.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     * @return void
     */
    public function testDeleteAddress(): void
    {
        $addressId = 1;
        // See that customer already has an address with expected addressId
        $addressDataObject = $this->repository->getById($addressId);
        $this->assertEquals($addressDataObject->getId(), $addressId);
        // Delete the address from the customer
        $this->repository->delete($addressDataObject);
        $message = (string)__(
            'No such entity with %fieldName = %fieldValue',
            [
                'fieldName' => 'addressId',
                'fieldValue' => 1,
            ]
        );
        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage($message);
        $this->repository->getById($addressId);
    }

    /**
     * Test for deleteAddressById.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     * @return void
     */
    public function testDeleteAddressById(): void
    {
        $addressId = 1;
        // See that customer already has an address with expected addressId
        $addressDataObject = $this->repository->getById($addressId);
        $this->assertEquals($addressDataObject->getId(), $addressId);

        // Delete the address from the customer
        $this->repository->deleteById($addressId);
        $message = (string)__(
            'No such entity with %fieldName = %fieldValue',
            [
                'fieldName' => 'addressId',
                'fieldValue' => 1,
            ]
        );
        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage($message);
        $this->repository->getById($addressId);
    }

    /**
     * Test delete address from customer with incorrect address id.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @return void
     */
    public function testDeleteAddressFromCustomerBadAddressId(): void
    {
        $message = (string)__(
            'No such entity with %fieldName = %fieldValue',
            [
                'fieldName' => 'addressId',
                'fieldValue' => 12345,
            ]
        );
        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage($message);
        $this->repository->deleteById(12345);
    }

    /**
     * Test for searching addressed.
     *
     * @param Filter[] $filters
     * @param Filter[] $filterGroup
     * @param SortOrder[] $filterOrders
     * @param array $expectedResult array of expected results indexed by ID
     * @param int $currentPage current page for search criteria
     *
     * @return void
     * @dataProvider searchAddressDataProvider
     *
     * @magentoDataFixture  Magento/Customer/_files/customer.php
     * @magentoDataFixture  Magento/Customer/_files/customer_two_addresses.php
     * @magentoAppIsolation enabled
     */
    public function testSearchAddresses(
        $filters,
        $filterGroup,
        $filterOrders,
        array $expectedResult,
        int $currentPage
    ): void {
        /** @var SearchCriteriaBuilder $searchBuilder */
        $searchBuilder = $this->objectManager->create(SearchCriteriaBuilder::class);
        foreach ($filters as $filter) {
            $searchBuilder->addFilters([$filter]);
        }
        if ($filterGroup !== null) {
            $searchBuilder->addFilters($filterGroup);
        }
        if ($filterOrders !== null) {
            foreach ($filterOrders as $order) {
                $searchBuilder->addSortOrder($order);
            }
        }

        $searchBuilder->setPageSize(1);
        $searchBuilder->setCurrentPage($currentPage);

        $searchCriteria = $searchBuilder->create();
        $searchResults = $this->repository->getList($searchCriteria);

        $items = array_values($searchResults->getItems());

        $this->assertEquals(count($expectedResult), $searchResults->getTotalCount());
        $this->assertCount(1, $items);

        $expectedResultIndex = count($expectedResult) - 1;

        $this->assertEquals($expectedResult[$expectedResultIndex]['id'], $items[0]->getId());
        $this->assertEquals($expectedResult[$expectedResultIndex]['city'], $items[0]->getCity());
        $this->assertEquals($expectedResult[$expectedResultIndex]['postcode'], $items[0]->getPostcode());
        $this->assertEquals($expectedResult[$expectedResultIndex]['firstname'], $items[0]->getFirstname());
    }

    /**
     * Data provider for searchAddresses.
     *
     * @return array
     */
    public static function searchAddressDataProvider(): array
    {
        /**
         * @var FilterBuilder $filterBuilder
         */
        $filterBuilder = Bootstrap::getObjectManager()
            ->create(FilterBuilder::class);
        /**
         * @var SortOrderBuilder $orderBuilder
         */
        $orderBuilder = Bootstrap::getObjectManager()
            ->create(SortOrderBuilder::class);
        return [
            'Address with postcode 75477' => [
                [$filterBuilder->setField('postcode')->setValue('75477')->create()],
                null,
                null,
                [
                    ['id' => 1, 'city' => 'CityM', 'postcode' => 75477, 'firstname' => 'John'],
                ],
                1
            ],
            'Address with city CityM' => [
                [$filterBuilder->setField('city')->setValue('CityM')->create()],
                null,
                null,
                [
                    ['id' => 1, 'city' => 'CityM', 'postcode' => 75477, 'firstname' => 'John'],
                ],
                1
            ],
            'Addresses with firstname John sorted by firstname desc, city asc' => [
                [$filterBuilder->setField('firstname')->setValue('John')->create()],
                null,
                [
                    $orderBuilder->setField('firstname')->setDirection(SortOrder::SORT_DESC)->create(),
                    $orderBuilder->setField('city')->setDirection(SortOrder::SORT_ASC)->create(),
                ],
                [
                    ['id' => 1, 'city' => 'CityM', 'postcode' => 75477, 'firstname' => 'John'],
                    ['id' => 2, 'city' => 'CityX', 'postcode' => 47676, 'firstname' => 'John'],
                ],
                2
            ],
            'Addresses with postcode of either 75477 or 47676 sorted by city desc' => [
                [],
                [
                    $filterBuilder->setField('postcode')->setValue('75477')->create(),
                    $filterBuilder->setField('postcode')->setValue('47676')->create(),
                ],
                [
                    $orderBuilder->setField('city')->setDirection(SortOrder::SORT_DESC)->create(),
                ],
                [
                    ['id' => 2, 'city' => 'CityX', 'postcode' => 47676, 'firstname' => 'John'],
                    ['id' => 1, 'city' => 'CityM', 'postcode' => 75477, 'firstname' => 'John'],
                ],
                2
            ],
            'Addresses with postcode greater than 0 sorted by firstname asc, postcode desc' => [
                [$filterBuilder->setField('postcode')->setValue('0')->setConditionType('gt')->create()],
                null,
                [
                    $orderBuilder->setField('firstname')->setDirection(SortOrder::SORT_ASC)->create(),
                    $orderBuilder->setField('postcode')->setDirection(SortOrder::SORT_ASC)->create(),
                ],
                [
                    ['id' => 2, 'city' => 'CityX', 'postcode' => 47676, 'firstname' => 'John'],
                    ['id' => 1, 'city' => 'CityM', 'postcode' => 75477, 'firstname' => 'John'],
                ],
                2
            ],
        ];
    }

    /**
     * Test for save addresses with restricted countries.
     *
     * @magentoDataFixture Magento/Customer/Fixtures/customer_sec_website.php
     * @return void
     */
    public function testSaveAddressWithRestrictedCountries(): void
    {
        $website = $this->storeManager->getWebsite('test');
        $customer = $this->customerRepository->get('customer.web@example.com', (int)$website->getId());
        $region = $this->regionFactory->create()
            ->setRegionCode('CA')
            ->setRegion('California')
            ->setRegionId(12);
        $addressData = [
            'customer_id' => $customer->getId(),
            'firstname' => 'John',
            'lastname' => 'Doe',
            'street' => ['6161 Main Street'],
            'city' => 'Culver City',
            'country_id' => 'US',
            'region' => $region,
            'postcode' => 90230,
            'telephone' => '555655431',
        ];
        $address = $this->addressFactory->create(['data' => $addressData]);
        $saved = $this->repository->save($address);
        self::assertNotEmpty($saved->getId());
    }

    /**
     * Test for saving address with extra spaces in phone.
     *
     * @magentoDataFixture  Magento/Customer/_files/customer.php
     * @magentoDataFixture  Magento/Customer/_files/customer_address.php
     * @return void
     */
    public function testSaveNewAddressWithExtraSpacesInPhone(): void
    {
        $proposedAddress = $this->_createSecondAddress()
            ->setCustomerId(1)
            ->setTelephone(' 123456 ');
        $returnedAddress = $this->repository->save($proposedAddress);
        $savedAddress = $this->repository->getById($returnedAddress->getId());
        $this->assertEquals('123456', $savedAddress->getTelephone());
    }

    /**
     * Scenario for customer's default shipping and billing address saving and rollback.
     *
     * @magentoDataFixture Magento/Customer/_files/customer_without_addresses.php
     * @return void
     */
    public function testCustomerAddressRelationSynchronisation(): void
    {
        /**
         * Creating new address which is default shipping and billing for existing customer.
         */
        $address = $this->expectedAddresses[0];
        $address->setId(null);
        $address->setCustomerId(1);
        $address->setIsDefaultShipping(true);
        $address->setIsDefaultBilling(true);
        $savedAddress = $this->repository->save($address);

        /**
         * Customer registry should be updated with default shipping and billing addresses.
         */
        $customer = $this->customerRepository->get('customer@example.com', 1);
        $this->assertEquals($savedAddress->getId(), $customer->getDefaultShipping());
        $this->assertEquals($savedAddress->getId(), $customer->getDefaultBilling());

        /**
         * Registry should be clean up for reading data from DB.
         */
        $this->repository->deleteById($savedAddress->getId());
        $this->customerRegistry->removeByEmail('customer@example.com');

        /**
         * Customer's default shipping and billing addresses should be updated.
         */
        $customer = $this->customerRepository->get('customer@example.com', 1);
        $this->assertNull($customer->getDefaultShipping());
        $this->assertNull($customer->getDefaultBilling());
    }

    /**
     * Update Customer Address, with Alphanumeric Zip Code
     *
     * @magentoDataFixture Magento/Customer/_files/customer_one_address.php
     * @return void
     */
    public function testUpdateWithAlphanumericZipCode(): void
    {
        $region = $this->regionFactory->create()
            ->setRegionCode('PH')
            ->setRegion('Pinminnoch')
            ->setRegionId(1);
        $websiteId = (int)$this->storeManager->getWebsite('base')->getId();
        $customer = $this->customerRepository->get('customer_one_address@test.com', $websiteId);
        $defaultBillingAddress = $customer->getDefaultBilling();
        $addressData = [
            AddressInterface::FIRSTNAME => 'Doe',
            AddressInterface::LASTNAME => 'Doe',
            AddressInterface::MIDDLENAME => 'Middle Name',
            AddressInterface::SUFFIX => '_Suffix',
            AddressInterface::PREFIX => 'Prefix',
            AddressInterface::COMPANY => 'Company',
            AddressInterface::STREET => ['Northgate Street, 39'],
            AddressInterface::CITY => 'BICKTON',
            AddressInterface::COUNTRY_ID => 'GB',
            AddressInterface::REGION => $region,
            AddressInterface::POSTCODE => 'KA26 1PF',
            AddressInterface::TELEPHONE => '999-777-111-2345',
            AddressInterface::VAT_ID => '987654321',
        ];
        $customerAddress = $this->repository->getById((int)$defaultBillingAddress);
        foreach ($addressData as $key => $value) {
            $customerAddress->setData($key, $value);
        }
        $savedAddress = $this->repository->save($customerAddress);
        $customerData = $savedAddress->__toArray();
        foreach ($addressData as $key => $value) {
            if ($key === AddressInterface::REGION) {
                $this->assertEquals($customerData[$key][AddressInterface::REGION], $value->getRegion());
            } else {
                $this->assertEquals($value, $customerData[$key]);
            }
        }
    }

    /**
     * Helper function that returns an Address Data Object that matches the data from customer_address fixture
     *
     * @return AddressInterface
     */
    private function _createFirstAddress(): AddressInterface
    {
        $address = $this->addressFactory->create();
        $this->dataObjectHelper->mergeDataObjects(
            AddressInterface::class,
            $address,
            $this->expectedAddresses[0]
        );
        $address->setId(null);
        $address->setRegion($this->expectedAddresses[0]->getRegion());
        return $address;
    }

    /**
     * Helper function that returns an Address Data Object that matches the data from customer_two_address fixture
     *
     * @return AddressInterface
     */
    private function _createSecondAddress(): AddressInterface
    {
        $address = $this->addressFactory->create();
        $this->dataObjectHelper->mergeDataObjects(
            AddressInterface::class,
            $address,
            $this->expectedAddresses[1]
        );
        $address->setId(null);
        $address->setRegion($this->expectedAddresses[1]->getRegion());
        return $address;
    }
}
