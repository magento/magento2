<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\ResourceModel;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;

/**
 * Class with integration tests for AddressRepository.
 *
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddressRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /** @var AddressRepositoryInterface */
    private $repository;

    /** @var \Magento\Framework\ObjectManagerInterface */
    private $objectManager;

    /** @var \Magento\Customer\Model\Data\Address[] */
    private $expectedAddresses;

    /** @var \Magento\Customer\Api\Data\AddressInterfaceFactory */
    private $addressFactory;

    /** @var  \Magento\Framework\Api\DataObjectHelper */
    private $dataObjectHelper;

    /**
     * Set up.
     */
    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /* @var \Magento\Framework\Config\CacheInterface $cache */
        $cache = $this->objectManager->create(\Magento\Framework\Config\CacheInterface::class);
        $cache->remove('extension_attributes_config');

        $this->repository = $this->objectManager->create(\Magento\Customer\Api\AddressRepositoryInterface::class);
        $this->addressFactory = $this->objectManager->create(
            \Magento\Customer\Api\Data\AddressInterfaceFactory::class
        );
        $this->dataObjectHelper = $this->objectManager->create(\Magento\Framework\Api\DataObjectHelper::class);

        $regionFactory = $this->objectManager->get(RegionInterfaceFactory::class);
        $region = $regionFactory->create()
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
     * Tear down.
     */
    protected function tearDown()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Customer\Model\CustomerRegistry $customerRegistry */
        $customerRegistry = $objectManager->get(\Magento\Customer\Model\CustomerRegistry::class);
        $customerRegistry->remove(1);
    }

    /**
     * Test for save address changes.
     *
     * @magentoDataFixture  Magento/Customer/_files/customer.php
     * @magentoDataFixture  Magento/Customer/_files/customer_address.php
     * @magentoDataFixture  Magento/Customer/_files/customer_two_addresses.php
     * @magentoAppIsolation enabled
     */
    public function testSaveAddressChanges()
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
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with addressId = 4200
     */
    public function testSaveAddressesIdSetButNotAlreadyExisting()
    {
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
     */
    public function testGetAddressById()
    {
        $addressId = 2;
        $address = $this->repository->getById($addressId);
        $this->assertEquals($this->expectedAddresses[1], $address);
    }

    /**
     * Test for method get address by id with incorrect id.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with addressId = 12345
     */
    public function testGetAddressByIdBadAddressId()
    {
        $this->repository->getById(12345);
    }

    /**
     * Test for method save new address.
     *
     * @magentoDataFixture  Magento/Customer/_files/customer.php
     * @magentoDataFixture  Magento/Customer/_files/customer_address.php
     * @magentoAppIsolation enabled
     */
    public function testSaveNewAddress()
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
     */
    public function testSaveNewAddressWithAttributes()
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
     */
    public function testSaveNewInvalidAddress()
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
    public function testSaveAddressesCustomerIdNotExist()
    {
        $proposedAddress = $this->_createSecondAddress()->setCustomerId(4200);
        try {
            $this->repository->save($proposedAddress);
            $this->fail('Expected exception not thrown');
        } catch (NoSuchEntityException $nsee) {
            $this->assertEquals('No such entity with customerId = 4200', $nsee->getMessage());
        }
    }

    /**
     * Test for saving addresses with invalid customer id.
     *
     * @return void
     */
    public function testSaveAddressesCustomerIdInvalid()
    {
        $proposedAddress = $this->_createSecondAddress()->setCustomerId('this_is_not_a_valid_id');
        try {
            $this->repository->save($proposedAddress);
            $this->fail('Expected exception not thrown');
        } catch (NoSuchEntityException $nsee) {
            $this->assertEquals('No such entity with customerId = this_is_not_a_valid_id', $nsee->getMessage());
        }
    }

    /**
     * Test for delete method.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     */
    public function testDeleteAddress()
    {
        $addressId = 1;
        // See that customer already has an address with expected addressId
        $addressDataObject = $this->repository->getById($addressId);
        $this->assertEquals($addressDataObject->getId(), $addressId);

        // Delete the address from the customer
        $this->repository->delete($addressDataObject);

        // See that address is deleted
        try {
            $addressDataObject = $this->repository->getById($addressId);
            $this->fail("Expected NoSuchEntityException not caught");
        } catch (NoSuchEntityException $exception) {
            $this->assertEquals('No such entity with addressId = 1', $exception->getMessage());
        }
    }

    /**
     * Test for deleteAddressById.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     */
    public function testDeleteAddressById()
    {
        $addressId = 1;
        // See that customer already has an address with expected addressId
        $addressDataObject = $this->repository->getById($addressId);
        $this->assertEquals($addressDataObject->getId(), $addressId);

        // Delete the address from the customer
        $this->repository->deleteById($addressId);

        // See that address is deleted
        try {
            $addressDataObject = $this->repository->getById($addressId);
            $this->fail("Expected NoSuchEntityException not caught");
        } catch (NoSuchEntityException $exception) {
            $this->assertEquals('No such entity with addressId = 1', $exception->getMessage());
        }
    }

    /**
     * Test delete address from customer with incorrect address id.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testDeleteAddressFromCustomerBadAddressId()
    {
        try {
            $this->repository->deleteById(12345);
            $this->fail("Expected NoSuchEntityException not caught");
        } catch (NoSuchEntityException $exception) {
            $this->assertEquals('No such entity with addressId = 12345', $exception->getMessage());
        }
    }

    /**
     * Test for searching addressed.
     *
     * @param \Magento\Framework\Api\Filter[] $filters
     * @param \Magento\Framework\Api\Filter[] $filterGroup
     * @param \Magento\Framework\Api\SortOrder[] $filterOrders
     * @param array $expectedResult array of expected results indexed by ID
     * @param int $currentPage current page for search criteria
     *
     * @dataProvider searchAddressDataProvider
     *
     * @magentoDataFixture  Magento/Customer/_files/customer.php
     * @magentoDataFixture  Magento/Customer/_files/customer_two_addresses.php
     * @magentoAppIsolation enabled
     */
    public function testSearchAddresses($filters, $filterGroup, $filterOrders, $expectedResult, $currentPage)
    {
        /** @var \Magento\Framework\Api\SearchCriteriaBuilder $searchBuilder */
        $searchBuilder = $this->objectManager->create(\Magento\Framework\Api\SearchCriteriaBuilder::class);
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
        $this->assertEquals(1, count($items));

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
    public function searchAddressDataProvider()
    {
        /**
         * @var \Magento\Framework\Api\FilterBuilder $filterBuilder
         */
        $filterBuilder = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Framework\Api\FilterBuilder::class);
        /**
         * @var \Magento\Framework\Api\SortOrderBuilder $orderBuilder
         */
        $orderBuilder = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Framework\Api\SortOrderBuilder::class);
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
     */
    public function testSaveAddressWithRestrictedCountries()
    {
        $website = $this->getWebsite('test');
        $customer = $this->getCustomer('customer.web@example.com', (int)$website->getId());
        $regionFactory = $this->objectManager->get(RegionInterfaceFactory::class);
        $region = $regionFactory->create()
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
            'telephone' => '555655431'
        ];
        $address = $this->addressFactory->create(['data' => $addressData]);
        $saved = $this->repository->save($address);
        self::assertNotEmpty($saved->getId());
    }

    /**
     * Helper function that returns an Address Data Object that matches the data from customer_address fixture
     *
     * @return \Magento\Customer\Api\Data\AddressInterface
     */
    private function _createFirstAddress()
    {
        $address = $this->addressFactory->create();
        $this->dataObjectHelper->mergeDataObjects(
            \Magento\Customer\Api\Data\AddressInterface::class,
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
     * @return \Magento\Customer\Api\Data\AddressInterface
     */
    private function _createSecondAddress()
    {
        $address = $this->addressFactory->create();
        $this->dataObjectHelper->mergeDataObjects(
            \Magento\Customer\Api\Data\AddressInterface::class,
            $address,
            $this->expectedAddresses[1]
        );
        $address->setId(null);
        $address->setRegion($this->expectedAddresses[1]->getRegion());
        return $address;
    }

    /**
     * Gets customer entity.
     *
     * @param string $email
     * @param int $websiteId
     * @return CustomerInterface
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getCustomer(string $email, int $websiteId): CustomerInterface
    {
        /** @var CustomerRepositoryInterface $repository */
        $repository = $this->objectManager->get(CustomerRepositoryInterface::class);
        return $repository->get($email, $websiteId);
    }

    /**
     * Gets website entity.
     *
     * @param string $code
     * @return WebsiteInterface
     * @throws NoSuchEntityException
     */
    private function getWebsite(string $code): WebsiteInterface
    {
        /** @var WebsiteRepositoryInterface $repository */
        $repository = $this->objectManager->get(WebsiteRepositoryInterface::class);
        return $repository->get($code);
    }

    /**
     * Test for saving address with extra spaces in phone.
     *
     * @magentoDataFixture  Magento/Customer/_files/customer.php
     * @magentoDataFixture  Magento/Customer/_files/customer_address.php
     */
    public function testSaveNewAddressWithExtraSpacesInPhone()
    {
        $proposedAddress = $this->_createSecondAddress()
            ->setCustomerId(1)
            ->setTelephone(' 123456 ');
        $returnedAddress = $this->repository->save($proposedAddress);
        $savedAddress = $this->repository->getById($returnedAddress->getId());
        $this->assertEquals('123456', $savedAddress->getTelephone());
    }
}
