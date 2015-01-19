<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\Resource;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Integration test for \Magento\Customer\Model\Resource\AddressRepository
 *
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class AddressRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var AddressRepositoryInterface */
    private $repository;

    /** @var \Magento\Framework\ObjectManagerInterface */
    private $_objectManager;

    /** @var \Magento\Customer\Model\Data\Address[] */
    private $_expectedAddresses;

    /** @var \Magento\Customer\Api\Data\AddressDataBuilder */
    private $_addressBuilder;

    protected function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->repository = $this->_objectManager->create('Magento\Customer\Api\AddressRepositoryInterface');
        $this->_addressBuilder = $this->_objectManager->create('Magento\Customer\Api\Data\AddressDataBuilder');

        $builder = $this->_objectManager->create('Magento\Customer\Api\Data\RegionDataBuilder');
        $region = $builder
            ->setRegionCode('AL')
            ->setRegion('Alabama')
            ->setRegionId(1)
            ->create();
        $this->_addressBuilder
            ->setId('1')
            ->setCountryId('US')
            ->setCustomerId('1')
            ->setPostcode('75477')
            ->setRegion($region)
            ->setStreet(['Green str, 67'])
            ->setTelephone('3468676')
            ->setCity('CityM')
            ->setFirstname('John')
            ->setLastname('Smith')
            ->setCompany('CompanyName');
        $address = $this->_addressBuilder->create();
        $this->_addressBuilder
            ->setId('2')
            ->setCountryId('US')
            ->setCustomerId('1')
            ->setPostcode('47676')
            ->setRegion($region)
            ->setStreet(['Black str, 48'])
            ->setCity('CityX')
            ->setTelephone('3234676')
            ->setFirstname('John')
            ->setLastname('Smith');
        $address2 = $this->_addressBuilder->create();

        $this->_expectedAddresses = [$address, $address2];
    }

    protected function tearDown()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Customer\Model\CustomerRegistry $customerRegistry */
        $customerRegistry = $objectManager->get('Magento\Customer\Model\CustomerRegistry');
        $customerRegistry->remove(1);
    }

    /**
     * @magentoDataFixture  Magento/Customer/_files/customer.php
     * @magentoDataFixture  Magento/Customer/_files/customer_address.php
     * @magentoDataFixture  Magento/Customer/_files/customer_two_addresses.php
     * @magentoAppIsolation enabled
     */
    public function testSaveAddressChanges()
    {
        $address = $this->repository->getById(2);

        $proposedAddressBuilder = $this->_addressBuilder->populate($address);
        $proposedAddressBuilder->setRegion($address->getRegion());
        // change phone #
        $proposedAddressBuilder->setTelephone('555' . $address->getTelephone());
        $proposedAddressObject = $proposedAddressBuilder->create();
        $proposedAddress = $this->repository->save($proposedAddressObject);
        $this->assertEquals(2, $proposedAddress->getId());

        $savedAddress = $this->repository->getById(2);
        $this->assertNotEquals($this->_expectedAddresses[1]->getTelephone(), $savedAddress->getTelephone());
    }

    /**
     * @magentoDataFixture  Magento/Customer/_files/customer.php
     * @magentoDataFixture  Magento/Customer/_files/customer_address.php
     * @magentoDataFixture  Magento/Customer/_files/customer_two_addresses.php
     * @magentoAppIsolation enabled
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with addressId = 4200
     */
    public function testSaveAddressesIdSetButNotAlreadyExisting()
    {
        $proposedAddressBuilder = $this->_createSecondAddressBuilder()->setId(4200);
        $proposedAddress = $proposedAddressBuilder->create();
        $this->repository->save($proposedAddress);
    }

    /**
     * @magentoDataFixture  Magento/Customer/_files/customer.php
     * @magentoDataFixture  Magento/Customer/_files/customer_address.php
     * @magentoDataFixture  Magento/Customer/_files/customer_two_addresses.php
     * @magentoAppIsolation enabled
     */
    public function testGetAddressById()
    {
        $addressId = 2;
        $address = $this->repository->getById($addressId);
        $this->assertEquals($this->_expectedAddresses[1], $address);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with addressId = 12345
     */
    public function testGetAddressByIdBadAddressId()
    {
        $this->repository->getById(12345);
    }

    /**
     * @magentoDataFixture  Magento/Customer/_files/customer.php
     * @magentoDataFixture  Magento/Customer/_files/customer_address.php
     * @magentoAppIsolation enabled
     */
    public function testSaveNewAddress()
    {
        $proposedAddress = $this->_createSecondAddressBuilder()->setCustomerId(1)->create();

        $returnedAddress = $this->repository->save($proposedAddress);
        $this->assertNotNull($returnedAddress->getId());

        $savedAddress = $this->repository->getById($returnedAddress->getId());

        $expectedNewAddressBuilder = $this->_addressBuilder->populate($this->_expectedAddresses[1]);
        $expectedNewAddressBuilder->setId($savedAddress->getId());
        $expectedNewAddressBuilder->setRegion($this->_expectedAddresses[1]->getRegion());
        $expectedNewAddress = $expectedNewAddressBuilder->create();
        $this->assertEquals($expectedNewAddress, $savedAddress);
    }

    /**
     * @magentoDataFixture  Magento/Customer/_files/customer.php
     * @magentoDataFixture  Magento/Customer/_files/customer_address.php
     * @magentoAppIsolation enabled
     */
    public function testSaveNewAddressWithAttributes()
    {
        $addressBuilder = $this->_createFirstAddressBuilder()
            ->setCustomAttribute('firstname', 'Jane')
            ->setCustomAttribute('id', 4200)
            ->setCustomAttribute('weird', 'something_strange_with_hair')
            ->setId(null)
            ->setCustomerId(1);
        $proposedAddress = $addressBuilder->create();

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
     * @magentoDataFixture  Magento/Customer/_files/customer.php
     * @magentoDataFixture  Magento/Customer/_files/customer_address.php
     * @magentoAppIsolation enabled
     */
    public function testSaveNewInvalidAddress()
    {
        $addressBuilder = $this->_createFirstAddressBuilder()
            ->setCustomAttribute('firstname', null)
            ->setId(null)
            ->setFirstname(null)
            ->setLastname(null)
            ->setCustomerId(1);
        $address = $addressBuilder->create();
        try {
            $this->repository->save($address);
        } catch (InputException $exception) {
            $this->assertEquals(InputException::DEFAULT_MESSAGE, $exception->getMessage());
            $errors = $exception->getErrors();
            $this->assertCount(2, $errors);
            $this->assertEquals('firstname is a required field.', $errors[0]->getLogMessage());
            $this->assertEquals('lastname is a required field.', $errors[1]->getLogMessage());
        }
    }

    public function testSaveAddressesCustomerIdNotExist()
    {
        $proposedAddress = $this->_createSecondAddressBuilder()->setCustomerId(4200)->create();
        try {
            $this->repository->save($proposedAddress);
            $this->fail('Expected exception not thrown');
        } catch (NoSuchEntityException $nsee) {
            $this->assertEquals('No such entity with customerId = 4200', $nsee->getMessage());
        }
    }

    public function testSaveAddressesCustomerIdInvalid()
    {
        $proposedAddress = $this->_createSecondAddressBuilder()->setCustomerId('this_is_not_a_valid_id')->create();
        try {
            $this->repository->save($proposedAddress);
            $this->fail('Expected exception not thrown');
        } catch (NoSuchEntityException $nsee) {
            $this->assertEquals('No such entity with customerId = this_is_not_a_valid_id', $nsee->getMessage());
        }
    }

    /**
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
     * @param \Magento\Framework\Api\Filter[] $filters
     * @param \Magento\Framework\Api\Filter[] $filterGroup
     * @param array $expectedResult array of expected results indexed by ID
     *
     * @dataProvider searchAddressDataProvider
     *
     * @magentoDataFixture  Magento/Customer/_files/customer.php
     * @magentoDataFixture  Magento/Customer/_files/customer_two_addresses.php
     * @magentoAppIsolation enabled
     */
    public function testSearchAddresses($filters, $filterGroup, $expectedResult)
    {
        /** @var \Magento\Framework\Api\SearchCriteriaBuilder $searchBuilder */
        $searchBuilder = $this->_objectManager->create('Magento\Framework\Api\SearchCriteriaBuilder');
        foreach ($filters as $filter) {
            $searchBuilder->addFilter([$filter]);
        }
        if (!is_null($filterGroup)) {
            $searchBuilder->addFilter($filterGroup);
        }

        $searchResults = $this->repository->getList($searchBuilder->create());

        $this->assertEquals(count($expectedResult), $searchResults->getTotalCount());

        /** @var \Magento\Customer\Api\Data\AddressInterface $item*/
        foreach ($searchResults->getItems() as $item) {
            $this->assertEquals(
                $expectedResult[$item->getId()]['city'],
                $item->getCity()
            );
            $this->assertEquals(
                $expectedResult[$item->getId()]['postcode'],
                $item->getPostcode()
            );
            $this->assertEquals(
                $expectedResult[$item->getId()]['firstname'],
                $item->getFirstname()
            );
            unset($expectedResult[$item->getId()]);
        }
    }

    public function searchAddressDataProvider()
    {
        /**
         * @var \Magento\Framework\Api\FilterBuilder $filterBuilder
         */
        $filterBuilder = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Framework\Api\FilterBuilder');
        return [
            'Address with postcode 75477' => [
                [$filterBuilder->setField('postcode')->setValue('75477')->create()],
                null,
                [1 => ['city' => 'CityM', 'postcode' => 75477, 'firstname' => 'John']],
            ],
            'Address with city CityM' => [
                [$filterBuilder->setField('city')->setValue('CityM')->create()],
                null,
                [1 => ['city' => 'CityM', 'postcode' => 75477, 'firstname' => 'John']],
            ],
            'Addresses with firstname John' => [
                [$filterBuilder->setField('firstname')->setValue('John')->create()],
                null,
                [
                    1 => ['city' => 'CityM', 'postcode' => 75477, 'firstname' => 'John'],
                    2 => ['city' => 'CityX', 'postcode' => 47676, 'firstname' => 'John']
                ],
            ],
            'Addresses with postcode of either 75477 or 47676' => [
                [],
                [
                    $filterBuilder->setField('postcode')->setValue('75477')->create(),
                    $filterBuilder->setField('postcode')->setValue('47676')->create()
                ],
                [
                    1 => ['city' => 'CityM', 'postcode' => 75477, 'firstname' => 'John'],
                    2 => ['city' => 'CityX', 'postcode' => 47676, 'firstname' => 'John']
                ],
            ],
            'Addresses with postcode greater than 0' => [
                [$filterBuilder->setField('postcode')->setValue('0')->setConditionType('gt')->create()],
                null,
                [
                    1 => ['city' => 'CityM', 'postcode' => 75477, 'firstname' => 'John'],
                    2 => ['city' => 'CityX', 'postcode' => 47676, 'firstname' => 'John']
                ],
            ]
        ];
    }

    /**
     * Helper function that returns an Address Data Object that matches the data from customer_address fixture
     *
     * @return \Magento\Customer\Api\Data\AddressDataBuilder
     */
    private function _createFirstAddressBuilder()
    {
        $addressBuilder = $this->_addressBuilder->populate($this->_expectedAddresses[0]);
        $addressBuilder->setId(null);
        $addressBuilder->setRegion($this->_expectedAddresses[0]->getRegion());
        return $addressBuilder;
    }

    /**
     * Helper function that returns an Address Data Object that matches the data from customer_two_address fixture
     *
     * @return \Magento\Customer\Api\Data\AddressDataBuilder
     */
    private function _createSecondAddressBuilder()
    {
        $addressBuilder =  $this->_addressBuilder->populate($this->_expectedAddresses[1]);
        $addressBuilder->setId(null);
        $addressBuilder->setRegion($this->_expectedAddresses[1]->getRegion());
        return $addressBuilder;
    }
}
