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

namespace Magento\Customer\Model\Resource;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Api\SearchCriteriaInterface;

class CustomerRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Customer\Api\CustomerRepositoryInterface */
    private $service;

    /** @var \Magento\Framework\ObjectManager */
    private $objectManager;

    /** @var \Magento\Customer\Api\Data\CustomerDataBuilder */
    private $customerBuilder;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->service = $this->objectManager->create('Magento\Customer\Api\CustomerRepositoryInterface');
        $this->customerBuilder = $this->objectManager->create('Magento\Customer\Api\Data\CustomerDataBuilder');
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
        $customer = $this->service->save($newCustomerEntity);

        /** Update customer */
        $this->customerBuilder->populate($customer);
        $newCustomerFirstname = 'Tested';
        $this->customerBuilder->setFirstname($newCustomerFirstname);
        $updatedCustomer = $this->customerBuilder->create();
        $this->service->save($updatedCustomer);

        /** Check if update was successful */
        $customer = $this->service->get($customer->getEmail());
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
        $savedCustomer = $this->service->save($newCustomerEntity);
        $this->assertNotNull($savedCustomer->getId());
        $this->assertEquals($email, $savedCustomer->getEmail());
        $this->assertEquals($storeId, $savedCustomer->getStoreId());
        $this->assertEquals($firstname, $savedCustomer->getFirstname());
        $this->assertEquals($lastname, $savedCustomer->getLastname());
        $this->assertEquals($groupId, $savedCustomer->getGroupId());
        $this->assertTrue(!$savedCustomer->getSuffix());
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

        $searchResults = $this->service->getList($searchBuilder->create());

        $this->assertEquals(count($expectedResult), $searchResults->getTotalCount());

        foreach ($searchResults->getItems() as $item) {
            $this->assertEquals($expectedResult[$item->getId()]['email'], $item->getEmail());
            $this->assertEquals($expectedResult[$item->getId()]['firstname'], $item->getFirstname());
            unset($expectedResult[$item->getId()]);
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
        /** @var \Magento\Framework\Api\SearchCriteriaDataBuilder $searchBuilder */
        $objectManager = Bootstrap::getObjectManager();
        $searchBuilder = $objectManager->create('Magento\Framework\Api\SearchCriteriaDataBuilder');

        // Filter for 'firstname' like 'First'
        $filterBuilder = $objectManager->create('\Magento\Framework\Api\FilterBuilder');
        $firstnameFilter = $filterBuilder->setField('firstname')
            ->setConditionType('like')
            ->setValue('First%')
            ->create();
        $searchBuilder->addFilter([$firstnameFilter]);
        // Search ascending order
        $sortOrderBuilder = $objectManager->create('\Magento\Framework\Api\SortOrderBuilder');
        $sortOrder = $sortOrderBuilder
            ->setField('lastname')
            ->setDirection(SearchCriteriaInterface::SORT_ASC)
            ->create();
        $searchBuilder->addSortOrder($sortOrder);
        $searchResults = $this->service->getList($searchBuilder->create());
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
        $searchResults = $this->service->getList($searchBuilder->create());
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
        $customer = $this->service->get($fixtureCustomerEmail);
        $this->service->delete($customer);
        /** Ensure that customer was deleted */
        $this->setExpectedException(
            'Magento\Framework\Exception\NoSuchEntityException',
            'No such entity with email = customer@example.com, websiteId = 1'
        );
        $this->service->get($fixtureCustomerEmail);
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
        $this->service->deleteById($fixtureCustomerId);
        /** Ensure that customer was deleted */
        $this->setExpectedException(
            'Magento\Framework\Exception\NoSuchEntityException',
            'No such entity with email = customer@example.com, websiteId = 1'
        );
        $this->service->get($fixtureCustomerEmail);
    }
}
