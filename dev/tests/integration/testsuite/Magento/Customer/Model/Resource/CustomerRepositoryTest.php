<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\Resource;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\TestFramework\Helper\Bootstrap;

class CustomerRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var AccountManagementInterface */
    private $accountManagement;

    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var \Magento\Framework\ObjectManagerInterface */
    private $objectManager;

    /** @var \Magento\Customer\Api\Data\CustomerDataBuilder */
    private $customerBuilder;

    /** @var \Magento\Customer\Api\Data\AddressDataBuilder */
    private $addressBuilder;

    /** @var \Magento\Customer\Api\Data\RegionDataBuilder */
    private $regionBuilder;

    /** @var \Magento\Framework\Api\ExtensibleDataObjectConverter */
    private $converter;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->customerRepository = $this->objectManager->create('Magento\Customer\Api\CustomerRepositoryInterface');
        $this->customerBuilder = $this->objectManager->create('Magento\Customer\Api\Data\CustomerDataBuilder');
        $this->addressBuilder = $this->objectManager->create('Magento\Customer\Api\Data\AddressDataBuilder');
        $this->regionBuilder = $this->objectManager->create('Magento\Customer\Api\Data\RegionDataBuilder');
        $this->accountManagement = $this->objectManager->create('Magento\Customer\Api\AccountManagementInterface');
        $this->converter = $this->objectManager->create('Magento\Framework\Api\ExtensibleDataObjectConverter');
    }

    protected function tearDown()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Customer\Model\CustomerRegistry $customerRegistry */
        $customerRegistry = $objectManager->get('Magento\Customer\Model\CustomerRegistry');
        $customerRegistry->remove(1);
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testCreateCustomerNewThenUpdateFirstName()
    {
        /** Create a new customer */
        $email = 'first_last@example.com';
        $storeId = 1;
        $firstname = 'Tester';
        $lastname = 'McTest';
        $groupId = 1;
        $this->customerBuilder
            ->setStoreId($storeId)
            ->setEmail($email)
            ->setFirstname($firstname)
            ->setLastname($lastname)
            ->setGroupId($groupId);
        $newCustomerEntity = $this->customerBuilder->create();
        $customer = $this->customerRepository->save($newCustomerEntity);
        /** Update customer */
        $this->customerBuilder->populate($customer);
        $newCustomerFirstname = 'New First Name';
        $this->customerBuilder->setFirstname($newCustomerFirstname);
        $updatedCustomer = $this->customerBuilder->create();
        $this->customerRepository->save($updatedCustomer);
        /** Check if update was successful */
        $customer = $this->customerRepository->get($customer->getEmail());
        $this->assertEquals($newCustomerFirstname, $customer->getFirstname());
        $this->assertEquals($lastname, $customer->getLastname());
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testCreateNewCustomer()
    {
        $email = 'email@example.com';
        $storeId = 1;
        $firstname = 'Tester';
        $lastname = 'McTest';
        $groupId = 1;

        $this->customerBuilder
            ->setStoreId($storeId)
            ->setEmail($email)
            ->setFirstname($firstname)
            ->setLastname($lastname)
            ->setGroupId($groupId);
        $newCustomerEntity = $this->customerBuilder->create();
        $savedCustomer = $this->customerRepository->save($newCustomerEntity);
        $this->assertNotNull($savedCustomer->getId());
        $this->assertEquals($email, $savedCustomer->getEmail());
        $this->assertEquals($storeId, $savedCustomer->getStoreId());
        $this->assertEquals($firstname, $savedCustomer->getFirstname());
        $this->assertEquals($lastname, $savedCustomer->getLastname());
        $this->assertEquals($groupId, $savedCustomer->getGroupId());
        $this->assertTrue(!$savedCustomer->getSuffix());
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testUpdateCustomer()
    {
        $existingCustId = 1;
        $email = 'savecustomer@example.com';
        $firstName = 'Firstsave';
        $lastname = 'Lastsave';
        $customerBefore = $this->customerRepository->getById($existingCustId);
        $customerData = array_merge($customerBefore->__toArray(), [
                'id' => 1,
                'email' => $email,
                'firstname' => $firstName,
                'lastname' => $lastname,
                'created_in' => 'Admin',
                'password' => 'notsaved'
            ]);
        $this->customerBuilder->populateWithArray($customerData);
        $customerDetails = $this->customerBuilder->setId($existingCustId)->create();
        $this->customerRepository->save($customerDetails);
        $customerAfter = $this->customerRepository->getById($existingCustId);
        $this->assertEquals($email, $customerAfter->getEmail());
        $this->assertEquals($firstName, $customerAfter->getFirstname());
        $this->assertEquals($lastname, $customerAfter->getLastname());
        $this->assertEquals('Admin', $customerAfter->getCreatedIn());
        $passwordFromFixture = 'password';
        $this->accountManagement->authenticate($customerAfter->getEmail(), $passwordFromFixture);
        $attributesBefore = $this->converter->toFlatArray(
            $customerBefore,
            [],
            '\Magento\Customer\Api\Data\CustomerInterface'
        );
        $attributesAfter = $this->converter->toFlatArray(
            $customerAfter,
            [],
            '\Magento\Customer\Api\Data\CustomerInterface'
        );
        // ignore 'updated_at'
        unset($attributesBefore['updated_at']);
        unset($attributesAfter['updated_at']);
        $inBeforeOnly = array_diff_assoc($attributesBefore, $attributesAfter);
        $inAfterOnly = array_diff_assoc($attributesAfter, $attributesBefore);
        $expectedInBefore = [
            'firstname',
            'lastname',
            'email',
        ];
        foreach ($expectedInBefore as $key) {
            $this->assertContains($key, array_keys($inBeforeOnly));
        }
        $this->assertContains('created_in', array_keys($inAfterOnly));
        $this->assertContains('firstname', array_keys($inAfterOnly));
        $this->assertContains('lastname', array_keys($inAfterOnly));
        $this->assertContains('email', array_keys($inAfterOnly));
        $this->assertNotContains('password_hash', array_keys($inAfterOnly));
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_two_addresses.php
     */
    public function testUpdateCustomerAddress()
    {
        $customerId = 1;
        $city = 'San Jose';
        $email = 'customer@example.com';
        $customer = $this->customerRepository->getById($customerId);
        $customerDetails = $customer->__toArray();
        $addresses = $customer->getAddresses();
        $addressId = $addresses[0]->getId();
        $newAddress = array_merge($addresses[0]->__toArray(), ['city' => $city]);
        $this->addressBuilder->populateWithArray($newAddress)->setRegion($addresses[0]->getRegion());
        $newAddress = $this->addressBuilder->create();
        $this->customerBuilder->populateWithArray($customerDetails)
            ->setId($customerId)
            ->setAddresses([$newAddress, $addresses[1]]);
        $newCustomerEntity = $this->customerBuilder->create();
        $this->customerRepository->save($newCustomerEntity);
        $newCustomer = $this->customerRepository->get($email);
        $this->assertEquals(2, count($newCustomer->getAddresses()));

        foreach ($newCustomer->getAddresses() as $newAddress) {
            if ($newAddress->getId() == $addressId) {
                $this->assertEquals($city, $newAddress->getCity());
            }
        }
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_two_addresses.php
     */
    public function testUpdateCustomerPreserveAllAddresses()
    {
        $customerId = 1;
        $customer = $this->customerRepository->getById($customerId);
        $customerDetails = $customer->__toArray();
        $this->customerBuilder->populateWithArray($customerDetails)
            ->setId($customer->getId())
            ->setAddresses(null);
        $newCustomerEntity = $this->customerBuilder->create();
        $this->customerRepository->save($newCustomerEntity);

        $newCustomerDetails = $this->customerRepository->getById($customerId);
        //Verify that old addresses are still present
        $this->assertEquals(2, count($newCustomerDetails->getAddresses()));
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_two_addresses.php
     */
    public function testUpdateCustomerDeleteAllAddressesWithEmptyArray()
    {
        $customerId = 1;
        $customer = $this->customerRepository->getById($customerId);
        $customerDetails = $customer->__toArray();
        $this->customerBuilder->populateWithArray($customerDetails)
            ->setId($customer->getId())
            ->setAddresses([]);
        $newCustomerEntity = $this->customerBuilder->create();
        $this->customerRepository->save($newCustomerEntity);

        $newCustomerDetails = $this->customerRepository->getById($customerId);
        //Verify that old addresses are removed
        $this->assertEquals(0, count($newCustomerDetails->getAddresses()));
    }

    /**
     * @param \Magento\Framework\Api\Filter[] $filters
     * @param \Magento\Framework\Api\Filter[] $filterGroup
     * @param array $expectedResult array of expected results indexed by ID
     *
     * @dataProvider searchCustomersDataProvider
     *
     * @magentoDataFixture Magento/Customer/_files/three_customers.php
     * @magentoDbIsolation enabled
     */
    public function testSearchCustomers($filters, $filterGroup, $expectedResult)
    {
        /** @var \Magento\Framework\Api\SearchCriteriaDataBuilder $searchBuilder */
        $searchBuilder = Bootstrap::getObjectManager()
            ->create('Magento\Framework\Api\SearchCriteriaDataBuilder');
        foreach ($filters as $filter) {
            $searchBuilder->addFilter([$filter]);
        }
        if (!is_null($filterGroup)) {
            $searchBuilder->addFilter($filterGroup);
        }

        $searchResults = $this->customerRepository->getList($searchBuilder->create());

        $this->assertEquals(count($expectedResult), $searchResults->getTotalCount());

        foreach ($searchResults->getItems() as $item) {
            $this->assertEquals($expectedResult[$item->getId()]['email'], $item->getEmail());
            $this->assertEquals($expectedResult[$item->getId()]['firstname'], $item->getFirstname());
            unset($expectedResult[$item->getId()]);
        }
    }

    public function searchCustomersDataProvider()
    {
        $builder = Bootstrap::getObjectManager()->create('Magento\Framework\Api\FilterBuilder');
        return [
            'Customer with specific email' => [
                [$builder->setField('email')->setValue('customer@search.example.com')->create()],
                null,
                [1 => ['email' => 'customer@search.example.com', 'firstname' => 'Firstname']],
            ],
            'Customer with specific first name' => [
                [$builder->setField('firstname')->setValue('Firstname2')->create()],
                null,
                [2 => ['email' => 'customer2@search.example.com', 'firstname' => 'Firstname2']],
            ],
            'Customers with either email' => [
                [],
                [
                    $builder->setField('firstname')->setValue('Firstname')->create(),
                    $builder->setField('firstname')->setValue('Firstname2')->create()
                ],
                [
                    1 => ['email' => 'customer@search.example.com', 'firstname' => 'Firstname'],
                    2 => ['email' => 'customer2@search.example.com', 'firstname' => 'Firstname2']
                ],
            ],
            'Customers created since' => [
                [
                    $builder->setField('created_at')->setValue('2011-02-28 15:52:26')
                        ->setConditionType('gt')->create(),
                ],
                [],
                [
                    1 => ['email' => 'customer@search.example.com', 'firstname' => 'Firstname'],
                    3 => ['email' => 'customer3@search.example.com', 'firstname' => 'Firstname3']
                ],
            ]
        ];
    }

    /**
     * Test ordering
     *
     * @magentoDataFixture Magento/Customer/_files/three_customers.php
     * @magentoDbIsolation enabled
     */
    public function testSearchCustomersOrder()
    {
        /** @var \Magento\Framework\Api\SearchCriteriaDataBuilder $searchBuilder */
        $objectManager = Bootstrap::getObjectManager();
        $searchBuilder = $objectManager->create('Magento\Framework\Api\SearchCriteriaDataBuilder');

        // Filter for 'firstname' like 'First'
        $filterBuilder = $objectManager->create('Magento\Framework\Api\FilterBuilder');
        $firstnameFilter = $filterBuilder->setField('firstname')
            ->setConditionType('like')
            ->setValue('First%')
            ->create();
        $searchBuilder->addFilter([$firstnameFilter]);
        // Search ascending order
        $sortOrderBuilder = $objectManager->create('Magento\Framework\Api\SortOrderBuilder');
        $sortOrder = $sortOrderBuilder
            ->setField('lastname')
            ->setDirection(SearchCriteriaInterface::SORT_ASC)
            ->create();
        $searchBuilder->addSortOrder($sortOrder);
        $searchResults = $this->customerRepository->getList($searchBuilder->create());
        $this->assertEquals(3, $searchResults->getTotalCount());
        $this->assertEquals('Lastname', $searchResults->getItems()[0]->getLastname());
        $this->assertEquals('Lastname2', $searchResults->getItems()[1]->getLastname());
        $this->assertEquals('Lastname3', $searchResults->getItems()[2]->getLastname());

        // Search descending order
        $sortOrder = $sortOrderBuilder
            ->setField('lastname')
            ->setDirection(SearchCriteriaInterface::SORT_DESC)
            ->create();
        $searchBuilder->addSortOrder($sortOrder);
        $searchResults = $this->customerRepository->getList($searchBuilder->create());
        $this->assertEquals('Lastname3', $searchResults->getItems()[0]->getLastname());
        $this->assertEquals('Lastname2', $searchResults->getItems()[1]->getLastname());
        $this->assertEquals('Lastname', $searchResults->getItems()[2]->getLastname());
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoAppIsolation enabled
     */
    public function testDelete()
    {
        $fixtureCustomerEmail = 'customer@example.com';
        $customer = $this->customerRepository->get($fixtureCustomerEmail);
        $this->customerRepository->delete($customer);
        /** Ensure that customer was deleted */
        $this->setExpectedException(
            'Magento\Framework\Exception\NoSuchEntityException',
            'No such entity with email = customer@example.com, websiteId = 1'
        );
        $this->customerRepository->get($fixtureCustomerEmail);
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoAppIsolation enabled
     */
    public function testDeleteById()
    {
        $fixtureCustomerEmail = 'customer@example.com';
        $fixtureCustomerId = 1;
        $this->customerRepository->deleteById($fixtureCustomerId);
        /** Ensure that customer was deleted */
        $this->setExpectedException(
            'Magento\Framework\Exception\NoSuchEntityException',
            'No such entity with email = customer@example.com, websiteId = 1'
        );
        $this->customerRepository->get($fixtureCustomerEmail);
    }
}
