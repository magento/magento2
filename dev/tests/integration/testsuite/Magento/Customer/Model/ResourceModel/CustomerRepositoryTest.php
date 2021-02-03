<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\ResourceModel;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Config\CacheInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Model\Customer;

/**
 * Checks Customer insert, update, search with repository
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /** @var AccountManagementInterface */
    private $accountManagement;

    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var CustomerInterfaceFactory */
    private $customerFactory;

    /** @var AddressInterfaceFactory */
    private $addressFactory;

    /** @var RegionInterfaceFactory */
    private $regionFactory;

    /** @var ExtensibleDataObjectConverter */
    private $converter;

    /** @var DataObjectHelper  */
    protected $dataObjectHelper;

    /** @var EncryptorInterface */
    protected $encryptor;

    /** @var CustomerRegistry */
    protected $customerRegistry;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->customerRepository = $this->objectManager->create(CustomerRepositoryInterface::class);
        $this->customerFactory = $this->objectManager->create(CustomerInterfaceFactory::class);
        $this->addressFactory = $this->objectManager->create(AddressInterfaceFactory::class);
        $this->regionFactory = $this->objectManager->create(RegionInterfaceFactory::class);
        $this->accountManagement = $this->objectManager->create(AccountManagementInterface::class);
        $this->converter = $this->objectManager->create(ExtensibleDataObjectConverter::class);
        $this->dataObjectHelper = $this->objectManager->create(DataObjectHelper::class);
        $this->encryptor = $this->objectManager->create(EncryptorInterface::class);
        $this->customerRegistry = $this->objectManager->create(CustomerRegistry::class);

        /** @var CacheInterface $cache */
        $cache = $this->objectManager->create(CacheInterface::class);
        $cache->remove('extension_attributes_config');
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var \Magento\Customer\Model\CustomerRegistry $customerRegistry */
        $customerRegistry = $objectManager->get(CustomerRegistry::class);
        $customerRegistry->remove(1);
    }

    /**
     * Check if first name update was successful
     *
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
        $newCustomerEntity = $this->customerFactory->create()
            ->setStoreId($storeId)
            ->setEmail($email)
            ->setFirstname($firstname)
            ->setLastname($lastname)
            ->setGroupId($groupId);
        $customer = $this->customerRepository->save($newCustomerEntity);
        /** Update customer */
        $newCustomerFirstname = 'New First Name';
        $updatedCustomer = $this->customerFactory->create();
        $this->dataObjectHelper->mergeDataObjects(
            CustomerInterface::class,
            $updatedCustomer,
            $customer
        );
        $updatedCustomer->setFirstname($newCustomerFirstname);
        $this->customerRepository->save($updatedCustomer);
        /** Check if update was successful */
        $customer = $this->customerRepository->get($customer->getEmail());
        $this->assertEquals($newCustomerFirstname, $customer->getFirstname());
        $this->assertEquals($lastname, $customer->getLastname());
    }

    /**
     * Test create new customer
     *
     * @magentoDbIsolation enabled
     */
    public function testCreateNewCustomer()
    {
        $email = 'email@example.com';
        $storeId = 1;
        $firstname = 'Tester';
        $lastname = 'McTest';
        $groupId = 1;

        $newCustomerEntity = $this->customerFactory->create()
            ->setStoreId($storeId)
            ->setEmail($email)
            ->setFirstname($firstname)
            ->setLastname($lastname)
            ->setGroupId($groupId);
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
     * Test update customer
     *
     * @dataProvider updateCustomerDataProvider
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @param int|null $defaultBilling
     * @param int|null $defaultShipping
     */
    public function testUpdateCustomer($defaultBilling, $defaultShipping)
    {
        $existingCustomerId = 1;
        $email = 'savecustomer@example.com';
        $firstName = 'Firstsave';
        $lastName = 'Lastsave';
        $newPassword = 'newPassword123';
        $newPasswordHash = $this->encryptor->getHash($newPassword, true);
        $customerBefore = $this->customerRepository->getById($existingCustomerId);
        $customerData = array_merge($customerBefore->__toArray(), [
                'id' => 1,
                'email' => $email,
                'firstname' => $firstName,
                'lastname' => $lastName,
                'created_in' => 'Admin',
                'password' => 'notsaved',
                'default_billing' => $defaultBilling,
                'default_shipping' => $defaultShipping
            ]);
        $customerDetails = $this->customerFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $customerDetails,
            $customerData,
            CustomerInterface::class
        );
        $this->customerRepository->save($customerDetails, $newPasswordHash);
        $customerAfter = $this->customerRepository->getById($existingCustomerId);
        $this->assertEquals($email, $customerAfter->getEmail());
        $this->assertEquals($firstName, $customerAfter->getFirstname());
        $this->assertEquals($lastName, $customerAfter->getLastname());
        $this->assertEquals($defaultBilling, $customerAfter->getDefaultBilling());
        $this->assertEquals($defaultShipping, $customerAfter->getDefaultShipping());
        $this->expectedDefaultShippingsInCustomerModelAttributes(
            $existingCustomerId,
            $defaultBilling,
            $defaultShipping
        );
        $this->assertEquals('Admin', $customerAfter->getCreatedIn());
        $this->accountManagement->authenticate($customerAfter->getEmail(), $newPassword);
        $attributesBefore = $this->converter->toFlatArray(
            $customerBefore,
            [],
            CustomerInterface::class
        );
        $attributesAfter = $this->converter->toFlatArray(
            $customerAfter,
            [],
            CustomerInterface::class
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
            $this->assertContains($key,array_keys($inBeforeOnly));
        }
        $this->assertContains('created_in',array_keys($inAfterOnly));
        $this->assertContains('firstname',array_keys($inAfterOnly));
        $this->assertContains('lastname',array_keys($inAfterOnly));
        $this->assertContains('email',array_keys($inAfterOnly));
        $this->assertNotContains('password_hash', array_keys($inAfterOnly));
    }

    /**
     * Test update customer address
     *
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
        $newAddressDataObject = $this->addressFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $newAddressDataObject,
            $newAddress,
            AddressInterface::class
        );
        $newAddressDataObject->setRegion($addresses[0]->getRegion());
        $newCustomerEntity = $this->customerFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $newCustomerEntity,
            $customerDetails,
            CustomerInterface::class
        );
        $newCustomerEntity->setId($customerId)
            ->setAddresses([$newAddressDataObject, $addresses[1]]);
        $this->customerRepository->save($newCustomerEntity);
        $newCustomer = $this->customerRepository->get($email);
        $this->assertCount(2, $newCustomer->getAddresses());

        foreach ($newCustomer->getAddresses() as $newAddress) {
            if ($newAddress->getId() == $addressId) {
                $this->assertEquals($city, $newAddress->getCity());
            }
        }
    }

    /**
     * Test preserve all addresses after customer update
     *
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_two_addresses.php
     */
    public function testUpdateCustomerPreserveAllAddresses()
    {
        $customerId = 1;
        $customer = $this->customerRepository->getById($customerId);
        $customerDetails = $customer->__toArray();
        $newCustomerEntity = $this->customerFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $newCustomerEntity,
            $customerDetails,
            CustomerInterface::class
        );
        $newCustomerEntity->setId($customer->getId())
            ->setAddresses(null);
        $this->customerRepository->save($newCustomerEntity);

        $newCustomerDetails = $this->customerRepository->getById($customerId);
        //Verify that old addresses are still present
        $this->assertCount(2, $newCustomerDetails->getAddresses());
    }

    /**
     * Test update delete all addresses with empty arrays
     *
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_two_addresses.php
     */
    public function testUpdateCustomerDeleteAllAddressesWithEmptyArray()
    {
        $customerId = 1;
        $customer = $this->customerRepository->getById($customerId);
        $customerDetails = $customer->__toArray();
        $newCustomerEntity = $this->customerFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $newCustomerEntity,
            $customerDetails,
            CustomerInterface::class
        );
        $newCustomerEntity->setId($customer->getId())
            ->setAddresses([]);
        $this->customerRepository->save($newCustomerEntity);

        $newCustomerDetails = $this->customerRepository->getById($customerId);
        //Verify that old addresses are removed
        $this->assertCount(0, $newCustomerDetails->getAddresses());
    }

    /**
     * Test customer update with new address
     *
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_two_addresses.php
     */
    public function testUpdateCustomerWithNewAddress()
    {
        $customerId = 1;
        $customer = $this->customerRepository->getById($customerId);
        $customerDetails = $customer->__toArray();
        unset($customerDetails['default_billing']);
        unset($customerDetails['default_shipping']);

        $beforeSaveCustomer = $this->customerFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $beforeSaveCustomer,
            $customerDetails,
            CustomerInterface::class
        );

        $addresses = $customer->getAddresses();
        $beforeSaveAddress = $addresses[0]->__toArray();
        unset($beforeSaveAddress['id']);
        $newAddressDataObject = $this->addressFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $newAddressDataObject,
            $beforeSaveAddress,
            AddressInterface::class
        );

        $beforeSaveCustomer->setAddresses([$newAddressDataObject]);
        $this->customerRepository->save($beforeSaveCustomer);

        $newCustomer = $this->customerRepository->getById($customerId);
        $newCustomerAddresses = $newCustomer->getAddresses();
        $addressId = $newCustomerAddresses[0]->getId();

        $this->assertEquals($newCustomer->getDefaultBilling(), $addressId, "Default billing invalid value");
        $this->assertEquals($newCustomer->getDefaultShipping(), $addressId, "Default shipping invalid value");
    }

    /**
     * Test search customers
     *
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
        /** @var SearchCriteriaBuilder $searchBuilder */
        $searchBuilder = Bootstrap::getObjectManager()->create(SearchCriteriaBuilder::class);
        foreach ($filters as $filter) {
            $searchBuilder->addFilters([$filter]);
        }
        if ($filterGroup !== null) {
            $searchBuilder->addFilters($filterGroup);
        }

        $searchResults = $this->customerRepository->getList($searchBuilder->create());

        $this->assertEquals(count($expectedResult), $searchResults->getTotalCount());

        foreach ($searchResults->getItems() as $item) {
            $this->assertEquals($expectedResult[$item->getId()]['email'], $item->getEmail());
            $this->assertEquals($expectedResult[$item->getId()]['firstname'], $item->getFirstname());
            unset($expectedResult[$item->getId()]);
        }
    }

    /**
     * Test ordering
     *
     * @magentoDataFixture Magento/Customer/_files/three_customers.php
     * @magentoDbIsolation enabled
     */
    public function testSearchCustomersOrder()
    {
        /** @var SearchCriteriaBuilder $searchBuilder */
        $objectManager = Bootstrap::getObjectManager();
        $searchBuilder = $objectManager->create(SearchCriteriaBuilder::class);

        // Filter for 'firstname' like 'First'
        $filterBuilder = $objectManager->create(FilterBuilder::class);
        $firstnameFilter = $filterBuilder->setField('firstname')
            ->setConditionType('like')
            ->setValue('First%')
            ->create();
        $searchBuilder->addFilters([$firstnameFilter]);
        // Search ascending order
        $sortOrderBuilder = $objectManager->create(SortOrderBuilder::class);
        $sortOrder = $sortOrderBuilder
            ->setField('lastname')
            ->setDirection(SortOrder::SORT_ASC)
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
            ->setDirection(SortOrder::SORT_DESC)
            ->create();
        $searchBuilder->addSortOrder($sortOrder);
        $searchResults = $this->customerRepository->getList($searchBuilder->create());
        $this->assertEquals('Lastname3', $searchResults->getItems()[0]->getLastname());
        $this->assertEquals('Lastname2', $searchResults->getItems()[1]->getLastname());
        $this->assertEquals('Lastname', $searchResults->getItems()[2]->getLastname());
    }

    /**
     * Test delete
     *
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
        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage('No such entity with email = customer@example.com, websiteId = 1');
        $this->customerRepository->get($fixtureCustomerEmail);
    }

    /**
     * Test delete by id
     *
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
        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage('No such entity with email = customer@example.com, websiteId = 1');
        $this->customerRepository->get($fixtureCustomerEmail);
    }

    /**
     * DataProvider update customer
     *
     * @return array
     */
    public function updateCustomerDataProvider()
    {
        return [
            'Customer remove default shipping and billing' => [
                null,
                null
            ],
            'Customer update default shipping and billing' => [
                1,
                1
            ],
        ];
    }

    /**
     * Search customer data provider
     *
     * @return array
     */
    public function searchCustomersDataProvider()
    {
        $builder = Bootstrap::getObjectManager()->create(FilterBuilder::class);
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
     * Check defaults billing and shipping in customer model
     *
     * @param $customerId
     * @param $defaultBilling
     * @param $defaultShipping
     */
    protected function expectedDefaultShippingsInCustomerModelAttributes(
        $customerId,
        $defaultBilling,
        $defaultShipping
    ) {
        /**
         * @var Customer $customer
         */
        $customer = $this->objectManager->create(Customer::class);
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer->load($customerId);
        $this->assertEquals(
            $defaultBilling,
            $customer->getDefaultBilling(),
            'default_billing customer attribute did not updated'
        );
        $this->assertEquals(
            $defaultShipping,
            $customer->getDefaultShipping(),
            'default_shipping customer attribute did not updated'
        );
    }

    /**
     * Test update default shipping and default billing address
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDbIsolation enabled
     */
    public function testUpdateDefaultShippingAndDefaultBillingTest()
    {
        $customerId = 1;
        $customerData = [
            "id" => 1,
            "website_id" => 1,
            "email" => "roni_cost@example.com",
            "firstname" => "1111",
            "lastname" => "Boss",
            "middlename" => null,
            "gender" => 0
        ];

        $customerEntity = $this->customerFactory->create(['data' => $customerData]);

        $customer = $this->customerRepository->getById($customerId);
        $oldDefaultBilling = $customer->getDefaultBilling();
        $oldDefaultShipping = $customer->getDefaultShipping();

        $savedCustomer = $this->customerRepository->save($customerEntity);

        $this->assertEquals(
            $savedCustomer->getDefaultBilling(),
            $oldDefaultBilling,
            'Default billing should not be overridden'
        );

        $this->assertEquals(
            $savedCustomer->getDefaultShipping(),
            $oldDefaultShipping,
            'Default shipping should not be overridden'
        );
    }
}
