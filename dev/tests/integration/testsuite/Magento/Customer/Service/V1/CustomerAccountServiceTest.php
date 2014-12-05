<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Customer\Service\V1;

use Magento\Customer\Service\V1;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Exception\State\ExpiredException;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Integration test for service layer \Magento\Customer\Service\V1\CustomerAccountService
 *
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @magentoAppArea frontend
 */
class CustomerAccountServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var CustomerAccountServiceInterface */
    private $_customerAccountService;

    /** @var CustomerAddressServiceInterface needed to setup tests */
    private $_customerAddressService;

    /** @var \Magento\Framework\ObjectManagerInterface */
    private $_objectManager;

    /** @var \Magento\Customer\Service\V1\Data\Address[] */
    private $_expectedAddresses;

    /** @var \Magento\Customer\Service\V1\Data\AddressBuilder */
    private $_addressBuilder;

    /** @var \Magento\Customer\Service\V1\Data\CustomerBuilder */
    private $_customerBuilder;

    /** @var \Magento\Customer\Service\V1\Data\CustomerDetailsBuilder */
    private $_customerDetailsBuilder;

    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    private $_extensibleDataObjectConverter;

    protected function setUp()
    {
        $this->markTestSkipped('Will be removed as part of MAGETWO-30671');
        $this->_objectManager = Bootstrap::getObjectManager();
        $this->_customerAccountService = $this->_objectManager
            ->create('Magento\Customer\Service\V1\CustomerAccountServiceInterface');
        $this->_customerAddressService =
            $this->_objectManager->create('Magento\Customer\Service\V1\CustomerAddressServiceInterface');

        $this->_addressBuilder = $this->_objectManager->create('Magento\Customer\Service\V1\Data\AddressBuilder');
        $this->_customerBuilder = $this->_objectManager->create('Magento\Customer\Service\V1\Data\CustomerBuilder');
        $this->_customerDetailsBuilder =
            $this->_objectManager->create('Magento\Customer\Service\V1\Data\CustomerDetailsBuilder');

        $regionBuilder = $this->_objectManager->create('Magento\Customer\Service\V1\Data\RegionBuilder');
        $this->_addressBuilder->setId(1)
            ->setCountryId('US')
            ->setCustomerId(1)
            ->setDefaultBilling(true)
            ->setDefaultShipping(true)
            ->setPostcode('75477')
            ->setRegion(
                $regionBuilder->setRegionCode('AL')->setRegion('Alabama')->setRegionId(1)->create()
            )
            ->setStreet(['Green str, 67'])
            ->setTelephone('3468676')
            ->setCity('CityM')
            ->setFirstname('John')
            ->setLastname('Smith');
        $address = $this->_addressBuilder->create();

        $this->_addressBuilder->setId(2)
            ->setCountryId('US')
            ->setCustomerId(1)
            ->setDefaultBilling(false)
            ->setDefaultShipping(false)
            ->setPostcode('47676')
            ->setRegion(
                $regionBuilder->setRegionCode('AL')->setRegion('Alabama')->setRegionId(1)->create()
            )
            ->setStreet(['Black str, 48'])
            ->setCity('CityX')
            ->setTelephone('3234676')
            ->setFirstname('John')
            ->setLastname('Smith');
        $address2 = $this->_addressBuilder->create();

        $this->_expectedAddresses = [$address, $address2];

        $this->_extensibleDataObjectConverter = $this->_objectManager->get(
            'Magento\Framework\Api\ExtensibleDataObjectConverter'
        );
    }

    /**
     * Clean up shared dependencies
     */
    protected function tearDown()
    {
        /** @var \Magento\Customer\Model\CustomerRegistry $customerRegistry */
        $customerRegistry = Bootstrap::getObjectManager()->get('Magento\Customer\Model\CustomerRegistry');
        //Cleanup customer from registry
        $customerRegistry->remove(1);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_two_addresses.php
     */
    public function testUpdateCustomerName()
    {
        $customerId = 1;
        $firstName = 'Firstsave';
        $lastName = 'Lastsave';

        $customerDetails = $this->_customerAccountService->getCustomerDetails($customerId);
        $newCustomer = array_merge(
            $customerDetails->getCustomer()->__toArray(),
            [
                'firstname' => $firstName,
                'lastname' => $lastName,
            ]
        );
        $this->_customerBuilder->populateWithArray($newCustomer);
        $this->_customerDetailsBuilder->setCustomer($this->_customerBuilder->create());
        $this->_customerAccountService->updateCustomer($customerId, $this->_customerDetailsBuilder->create());

        $newCustomerDetails = $this->_customerAccountService->getCustomerDetails($customerId);
        $this->assertEquals($firstName, $newCustomerDetails->getCustomer()->getFirstname());
        $this->assertEquals($lastName, $newCustomerDetails->getCustomer()->getLastname());
        $this->assertEquals(2, count($newCustomerDetails->getAddresses()));

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

        $customerDetails = $this->_customerAccountService->getCustomerDetails($customerId);
        $addresses = $customerDetails->getAddresses();
        $addressId = $addresses[0]->getId();
        $newAddress = array_merge($addresses[0]->__toArray(), ['city' => $city]);

        $this->_addressBuilder->populateWithArray($newAddress);
        $this->_customerDetailsBuilder
            ->setCustomer($customerDetails->getCustomer())
            ->setAddresses([$this->_addressBuilder->create(), $addresses[1]]);
        $this->_customerAccountService->updateCustomer($customerId, $this->_customerDetailsBuilder->create());

        $newCustomerDetails = $this->_customerAccountService->getCustomerDetails($customerId);
        $this->assertEquals(2, count($newCustomerDetails->getAddresses()));

        foreach ($newCustomerDetails->getAddresses() as $newAddress) {
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
    public function testUpdateCustomerDeleteOneAddress()
    {
        $customerId = 1;
        $customerDetails = $this->_customerAccountService->getCustomerDetails($customerId);
        $addresses = $customerDetails->getAddresses();
        $addressIdToRetain = $addresses[1]->getId();

        $this->_customerDetailsBuilder
            ->setCustomer($customerDetails->getCustomer())->setAddresses([$addresses[1]]);

        $this->_customerAccountService->updateCustomer($customerId, $this->_customerDetailsBuilder->create());

        $newCustomerDetails = $this->_customerAccountService->getCustomerDetails($customerId);
        $this->assertEquals(1, count($newCustomerDetails->getAddresses()));
        $this->assertEquals($addressIdToRetain, $newCustomerDetails->getAddresses()[0]->getId());
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_two_addresses.php
     */
    public function testUpdateCustomerDeleteAllAddresses()
    {
        $customerId = 1;
        $customerDetails = $this->_customerAccountService->getCustomerDetails($customerId);
        $this->_customerDetailsBuilder->setCustomer($customerDetails->getCustomer())
            ->setAddresses([]);
        $this->_customerAccountService->updateCustomer($customerId, $this->_customerDetailsBuilder->create());

        $newCustomerDetails = $this->_customerAccountService->getCustomerDetails($customerId);
        $this->assertEquals(0, count($newCustomerDetails->getAddresses()));
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

        $customerBefore = $this->_customerAccountService->getCustomer($existingCustId);

        $customerData = array_merge($customerBefore->__toArray(), [
            'id' => 1,
            'email' => $email,
            'firstname' => $firstName,
            'lastname' => $lastname,
            'created_in' => 'Admin',
            'password' => 'notsaved'
        ]);
        $this->_customerBuilder->populateWithArray($customerData);
        $modifiedCustomer = $this->_customerBuilder->create();
        $customerDetails = $this->_customerDetailsBuilder->setCustomer($modifiedCustomer)->create();
        $this->_customerAccountService->updateCustomer($existingCustId, $customerDetails);
        $customerAfter = $this->_customerAccountService->getCustomer($existingCustId);
        $this->assertEquals($email, $customerAfter->getEmail());
        $this->assertEquals($firstName, $customerAfter->getFirstname());
        $this->assertEquals($lastname, $customerAfter->getLastname());
        $this->assertEquals('Admin', $customerAfter->getCreatedIn());
        $passwordFromFixture = 'password';
        $this->_customerAccountService->authenticate($customerAfter->getEmail(), $passwordFromFixture);
        $attributesBefore = $this->_extensibleDataObjectConverter->toFlatArray($customerBefore);
        $attributesAfter = $this->_extensibleDataObjectConverter->toFlatArray($customerAfter);
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
     */
    public function testUpdateCustomerWithoutChangingPassword()
    {
        $existingCustId = 1;

        $email = 'savecustomer@example.com';
        $firstName = 'Firstsave';
        $lastName = 'Lastsave';

        $customerBefore = $this->_customerAccountService->getCustomer($existingCustId);
        $customerData = array_merge(
            $customerBefore->__toArray(),
            [
                'id' => 1,
                'email' => $email,
                'firstname' => $firstName,
                'lastname' => $lastName,
                'created_in' => 'Admin'
            ]
        );
        $this->_customerBuilder->populateWithArray($customerData);
        $modifiedCustomer = $this->_customerBuilder->create();

        $customerDetails = $this->_customerDetailsBuilder->setCustomer($modifiedCustomer)->create();
        $this->_customerAccountService->updateCustomer($existingCustId, $customerDetails);
        $customerAfter = $this->_customerAccountService->getCustomer($existingCustId);
        $this->assertEquals($email, $customerAfter->getEmail());
        $this->assertEquals($firstName, $customerAfter->getFirstname());
        $this->assertEquals($lastName, $customerAfter->getLastname());
        $this->assertEquals('Admin', $customerAfter->getCreatedIn());
        $this->_customerAccountService->authenticate(
            $customerAfter->getEmail(),
            'password',
            true
        );
        $attributesBefore = $this->_extensibleDataObjectConverter->toFlatArray($customerBefore);
        $attributesAfter = $this->_extensibleDataObjectConverter->toFlatArray($customerAfter);
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
        sort($expectedInBefore);
        $actualInBeforeOnly = array_keys($inBeforeOnly);
        sort($actualInBeforeOnly);
        $this->assertEquals($expectedInBefore, $actualInBeforeOnly);
        $expectedInAfter = [
            'firstname',
            'lastname',
            'email',
            'created_in',
        ];
        sort($expectedInAfter);
        $actualInAfterOnly = array_keys($inAfterOnly);
        sort($actualInAfterOnly);
        $this->assertEquals($expectedInAfter, $actualInAfterOnly);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testUpdateCustomerPasswordCannotSetThroughAttributeSetting()
    {
        $existingCustId = 1;

        $email = 'savecustomer@example.com';
        $firstName = 'Firstsave';
        $lastName = 'Lastsave';

        $customerBefore = $this->_customerAccountService->getCustomer($existingCustId);
        $customerData = array_merge(
            $customerBefore->__toArray(),
            [
                'id' => 1,
                'email' => $email,
                'firstname' => $firstName,
                'lastname' => $lastName,
                'created_in' => 'Admin',
                'password' => 'aPassword'
            ]
        );
        $this->_customerBuilder->populateWithArray($customerData);
        $modifiedCustomer = $this->_customerBuilder->create();
        $customerDetails = $this->_customerDetailsBuilder->setCustomer($modifiedCustomer)->create();
        $this->_customerAccountService->updateCustomer($existingCustId, $customerDetails);
        $customerAfter = $this->_customerAccountService->getCustomer($existingCustId);
        $this->assertEquals($email, $customerAfter->getEmail());
        $this->assertEquals($firstName, $customerAfter->getFirstname());
        $this->assertEquals($lastName, $customerAfter->getLastname());
        $this->assertEquals('Admin', $customerAfter->getCreatedIn());
        $this->_customerAccountService->authenticate(
            $customerAfter->getEmail(),
            'password',
            true
        );
        $attributesBefore = $this->_extensibleDataObjectConverter->toFlatArray($customerBefore);
        $attributesAfter = $this->_extensibleDataObjectConverter->toFlatArray($customerAfter);
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
        sort($expectedInBefore);
        $actualInBeforeOnly = array_keys($inBeforeOnly);
        sort($actualInBeforeOnly);
        $this->assertEquals($expectedInBefore, $actualInBeforeOnly);
        $expectedInAfter = [
            'firstname',
            'lastname',
            'email',
            'created_in',
        ];
        sort($expectedInAfter);
        $actualInAfterOnly = array_keys($inAfterOnly);
        sort($actualInAfterOnly);
        $this->assertEquals($expectedInAfter, $actualInAfterOnly);
    }


    /**
     * @magentoDbIsolation enabled
     */
    public function testCreateCustomerNewThenUpdateFirstName()
    {
        $email = 'first_last@example.com';
        $storeId = 1;
        $firstname = 'Tester';
        $lastname = 'McTest';
        $groupId = 1;
        $password = 'aPassword';

        $this->_customerBuilder
            ->setStoreId($storeId)
            ->setEmail($email)
            ->setFirstname($firstname)
            ->setLastname($lastname)
            ->setGroupId($groupId);
        $newCustomerEntity = $this->_customerBuilder->create();
        $customerDetails = $this->_customerDetailsBuilder->setCustomer($newCustomerEntity)->create();

        $customer = $this->_customerAccountService->createCustomer($customerDetails, $password);

        $this->_customerBuilder->populate($customer);
        $this->_customerBuilder->setFirstname('Tested');
        $customerDetails = $this->_customerDetailsBuilder->setCustomer($this->_customerBuilder->create())->create();
        $this->_customerAccountService->updateCustomer($customer->getId(), $customerDetails);

        $customer = $this->_customerAccountService->getCustomer($customer->getId());

        $this->assertEquals('Tested', $customer->getFirstname());
        $this->assertEquals($lastname, $customer->getLastname());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoAppIsolation enabled
     */
    public function testGetCustomer()
    {
        // _files/customer.php sets the customer id to 1
        $customer = $this->_customerAccountService->getCustomer(1);

        // All these expected values come from _files/customer.php fixture
        $this->assertEquals(1, $customer->getId());
        $this->assertEquals('customer@example.com', $customer->getEmail());
        $this->assertEquals('Firstname', $customer->getFirstname());
        $this->assertEquals('Lastname', $customer->getLastname());
    }

    public function testGetCustomerNotExist()
    {
        try {
            // No fixture, so customer with id 1 shouldn't exist, exception should be thrown
            $this->_customerAccountService->getCustomer(1);
            $this->fail('Did not throw expected exception.');
        } catch (NoSuchEntityException $nsee) {
            $this->assertEquals('No such entity with customerId = 1', $nsee->getMessage());
        }
    }

    /**
     * @param mixed $custId
     * @dataProvider invalidCustomerIdsDataProvider
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with customerId =
     */
    public function testGetCustomerInvalidIds($custId)
    {
        $this->_customerAccountService->getCustomer($custId);
    }

    public function invalidCustomerIdsDataProvider()
    {
        return [
            ['ab'],
            [' '],
            [-1],
            [0],
            [' 1234'],
            ['-1'],
            ['0'],
        ];
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
        /** @var \Magento\Framework\Api\SearchCriteriaBuilder $searchBuilder */
        $searchBuilder = Bootstrap::getObjectManager()->create(
            'Magento\Framework\Api\SearchCriteriaBuilder'
        );
        foreach ($filters as $filter) {
            $searchBuilder->addFilter([$filter]);
        }
        if (!is_null($filterGroup)) {
            $searchBuilder->addFilter($filterGroup);
        }

        $searchResults = $this->_customerAccountService->searchCustomers($searchBuilder->create());

        $this->assertEquals(count($expectedResult), $searchResults->getTotalCount());

        /** @var $item Data\CustomerDetails */
        foreach ($searchResults->getItems() as $item) {
            $this->assertEquals(
                $expectedResult[$item->getCustomer()->getId()]['email'],
                $item->getCustomer()->getEmail()
            );
            $this->assertEquals(
                $expectedResult[$item->getCustomer()->getId()]['firstname'],
                $item->getCustomer()->getFirstname()
            );
            unset($expectedResult[$item->getCustomer()->getId()]);
        }
    }

    public function searchCustomersDataProvider()
    {
        $builder = Bootstrap::getObjectManager()->create('\Magento\Framework\Api\FilterBuilder');
        return [
            'Customer with specific email' => [
                [$builder->setField('email')->setValue('customer@search.example.com')->create()],
                null,
                [1 => ['email' => 'customer@search.example.com', 'firstname' => 'Firstname']]
            ],
            'Customer with specific first name' => [
                [$builder->setField('firstname')->setValue('Firstname2')->create()],
                null,
                [2 => ['email' => 'customer2@search.example.com', 'firstname' => 'Firstname2']]
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
                ]
            ],
            'Customers created since' => [
                [
                    $builder->setField('created_at')->setValue('2011-02-28 15:52:26')
                        ->setConditionType('gt')->create()
                ],
                [],
                [
                    1 => ['email' => 'customer@search.example.com', 'firstname' => 'Firstname'],
                    3 => ['email' => 'customer3@search.example.com', 'firstname' => 'Firstname3']
                ]
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
        /** @var \Magento\Framework\Api\SearchCriteriaBuilder $searchBuilder */
        $searchBuilder = Bootstrap::getObjectManager()
            ->create('Magento\Framework\Api\SearchCriteriaBuilder');

        // Filter for 'firstname' like 'First'
        $filterBuilder = $this->_objectManager->create('\Magento\Framework\Api\FilterBuilder');
        $firstnameFilter = $filterBuilder->setField('firstname')
            ->setConditionType('like')
            ->setValue('First%')
            ->create();
        $searchBuilder->addFilter([$firstnameFilter]);
        // Search ascending order
        $sortOrderBuilder = $this->_objectManager->create('\Magento\Framework\Api\SortOrderBuilder');
        $sortOrder = $sortOrderBuilder
            ->setField('lastname')
            ->setDirection(SearchCriteria::SORT_ASC)
            ->create();
        $searchBuilder->addSortOrder($sortOrder);
        $searchResults = $this->_customerAccountService->searchCustomers($searchBuilder->create());
        $this->assertEquals(3, $searchResults->getTotalCount());
        $this->assertEquals('Lastname', $searchResults->getItems()[0]->getCustomer()->getLastname());
        $this->assertEquals('Lastname2', $searchResults->getItems()[1]->getCustomer()->getLastname());
        $this->assertEquals('Lastname3', $searchResults->getItems()[2]->getCustomer()->getLastname());

        // Search descending order
        $sortOrder = $sortOrderBuilder
            ->setField('lastname')
            ->setDirection(SearchCriteria::SORT_DESC)
            ->create();
        $searchBuilder->addSortOrder($sortOrder);
        $searchResults = $this->_customerAccountService->searchCustomers($searchBuilder->create());
        $this->assertEquals('Lastname3', $searchResults->getItems()[0]->getCustomer()->getLastname());
        $this->assertEquals('Lastname2', $searchResults->getItems()[1]->getCustomer()->getLastname());
        $this->assertEquals('Lastname', $searchResults->getItems()[2]->getCustomer()->getLastname());
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoAppIsolation enabled
     */
    public function testDeleteCustomer()
    {
        // _files/customer.php sets the customer id to 1
        $this->_customerAccountService->deleteCustomer(1);
        //Verify if the customer is deleted
        $this->setExpectedException(
            'Magento\Framework\Exception\NoSuchEntityException',
            'No such entity with customerId = 1'
        );
        $this->_customerAccountService->getCustomer(1);
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_two_addresses.php
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with customerId = 1
     */
    public function testDeleteCustomerWithAddress()
    {
        //Verify address is created for the customer;
        $result = $this->_customerAddressService->getAddresses(1);
        $this->assertEquals(2, count($result));
        // _files/customer.php sets the customer id to 1
        $this->_customerAccountService->deleteCustomer(1);

        // Verify by directly loading the address by id
        $this->verifyDeletedAddress(1);
        $this->verifyDeletedAddress(2);

        //Verify by calling the Address Service. This will throw the expected exception since customerId doesn't exist
        $result = $this->_customerAddressService->getAddresses(1);
    }

    /**
     * Check if the Address with the give addressid is deleted
     *
     * @param int $addressId
     */
    protected function verifyDeletedAddress($addressId)
    {
        /** @var $addressFactory \Magento\Customer\Model\AddressFactory */
        $addressFactory = $this->_objectManager
            ->create('Magento\Customer\Model\AddressFactory');
        $addressModel = $addressFactory->create()->load($addressId);
        $addressData = $addressModel->getData();
        $this->assertTrue(empty($addressData));
    }

    /**
     * @param $email
     * @param $websiteId
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @dataProvider getValidEmailDataProvider
     */
    public function testGetCustomerByEmail($email, $websiteId)
    {
        /** @var \Magento\Customer\Service\V1\Data\Customer $customer */
        $customer = $this->_customerAccountService->getCustomerByEmail($email, $websiteId);

        // All these expected values come from _files/customer.php fixture
        $this->assertEquals(1, $customer->getId());
        $this->assertEquals('customer@example.com', $customer->getEmail());
        $this->assertEquals('Firstname', $customer->getFirstname());
        $this->assertEquals('Lastname', $customer->getLastname());
    }

    /**
     * @param $email
     * @param $websiteId
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @dataProvider getInvalidEmailDataProvider
     */
    public function testGetCustomerByEmailWithException($email, $websiteId)
    {
        $this->_customerAccountService->getCustomerByEmail($email, $websiteId);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_non_default_website_id.php
     */
    public function testGetCustomerByEmailWithNonDefaultWebsiteId()
    {
        $email = 'customer2@example.com';
        /** @var \Magento\Store\Model\Website $website */
        $website = Bootstrap::getObjectManager()->get('Magento\Store\Model\Website');
        $websiteId = $website->load('newwebsite')->getId();
        /** @var \Magento\Customer\Service\V1\Data\Customer $customer */
        $customer = $this->_customerAccountService->getCustomerByEmail($email, $websiteId);

        // All these expected values come from _files/customer.php fixture
        $this->assertEquals(1, $customer->getId());
        $this->assertEquals($email, $customer->getEmail());
        $this->assertEquals('Firstname', $customer->getFirstname());
        $this->assertEquals('Lastname', $customer->getLastname());
    }

    /**
     * @return array
     *
     */
    public function getValidEmailDataProvider()
    {
        /** @var \Magento\Framework\StoreManagerInterface  $storeManager */
        $storeManager = Bootstrap::getObjectManager()->get('Magento\Framework\StoreManagerInterface');
        $defaultWebsiteId = $storeManager->getStore()->getWebsiteId();
        return [
            'valid email' => ['customer@example.com', null],
            'default websiteId' => ['customer@example.com', $defaultWebsiteId],
        ];
    }

    /**
     * @return array
     *
     */
    public function getInvalidEmailDataProvider()
    {
        return [
            'invalid email' => ['nonexistent@example.com', null],
            'invalid websiteId' => ['customer@example.com', 123456],
        ];
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/customer_non_default_website_id.php
     */
    public function testUpdateCustomerDetailsByEmail()
    {
        $customerId = 1;
        $firstName = 'Firstsave';
        $lastName = 'Lastsave';
        $newEmail = 'newcustomeremail@example.com';
        $city = 'San Jose';

        $customerDetails = $this->_customerAccountService->getCustomerDetails($customerId);
        $website = Bootstrap::getObjectManager()->get('Magento\Store\Model\Website');
        $websiteId = $website->load('newwebsite')->getId();
        $email = $customerDetails->getCustomer()->getEmail();
        $customerData = array_merge(
            $customerDetails->getCustomer()->__toArray(),
            [
                'firstname' => $firstName,
                'lastname' => $lastName,
                'email' => $newEmail,
                'id' => null
            ]
        );
        $addresses = $customerDetails->getAddresses();
        $addressId = $addresses[0]->getId();
        $newAddress = array_merge($addresses[0]->__toArray(), ['city' => $city]);
        $this->_customerBuilder->populateWithArray($customerData);
        $this->_addressBuilder->populateWithArray($newAddress);
        $this->_customerDetailsBuilder->setCustomer(($this->_customerBuilder->create()))
            ->setAddresses([$this->_addressBuilder->create(), $addresses[1]]);

        $this->_customerAccountService->updateCustomerByEmail(
            $email,
            $this->_customerDetailsBuilder->create(),
            $websiteId
        );

        $updatedCustomerDetails = $this->_customerAccountService->getCustomerDetails($customerId);
        $updateCustomerData = $updatedCustomerDetails->getCustomer();
        $this->assertEquals($firstName, $updateCustomerData->getFirstname());
        $this->assertEquals($lastName, $updateCustomerData->getLastname());
        $this->assertEquals($newEmail, $updateCustomerData->getEmail());
        $this->assertEquals(2, count($updatedCustomerDetails->getAddresses()));

        foreach ($updatedCustomerDetails->getAddresses() as $newAddress) {
            if ($newAddress->getId() == $addressId) {
                $this->assertEquals($city, $newAddress->getCity());
            }
        }
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @expectedException \Magento\Framework\Exception\StateException
     */
    public function testUpdateCustomerDetailsByEmailWithException()
    {
        $customerId = 1;
        $customerDetails = $this->_customerAccountService->getCustomerDetails($customerId);
        $email = $customerDetails->getCustomer()->getEmail();
        $customerData = array_merge(
            $customerDetails->getCustomer()->__toArray(),
            [
                'firstname' => 'fname',
                'id' => 1234567
            ]
        );
        $this->_customerBuilder->populateWithArray($customerData);
        $this->_customerDetailsBuilder->setCustomer(($this->_customerBuilder->create()))->setAddresses([]);
        $this->_customerAccountService->updateCustomerByEmail($email, $this->_customerDetailsBuilder->create());

    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoAppIsolation enabled
     */
    public function testDeleteCustomerByEmail()
    {
        // _files/customer.php sets the customer email to customer@example.com
        $this->_customerAccountService->deleteCustomerByEmail('customer@example.com');
        //Verify if the customer is deleted
        $this->setExpectedException(
            'Magento\Framework\Exception\NoSuchEntityException',
            'No such entity with email = customer@example.com'
        );
        $this->_customerAccountService->getCustomerByEmail('customer@example.com');
    }

    /**
     * Set Rp data to Customer in fixture
     *
     * @param $resetToken
     * @param $date
     */
    protected function setResetPasswordData($resetToken, $date)
    {
        $customerIdFromFixture = 1;
        /** @var \Magento\Customer\Model\Customer $customerModel */
        $customerModel = $this->_objectManager->create('Magento\Customer\Model\Customer');
        $customerModel->load($customerIdFromFixture);
        $customerModel->setRpToken($resetToken);
        $customerModel->setRpTokenCreatedAt(date($date));
        $customerModel->save();
    }
}
