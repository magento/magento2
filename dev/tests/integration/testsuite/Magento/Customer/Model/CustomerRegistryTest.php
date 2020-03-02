<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Customer\Model\CustomerRegistry
 *
 * @magentoDbIsolation enabled
 */
class CustomerRegistryTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var CustomerRegistry
     */
    private $model;

    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @var int
     */
    private $defaultWebsiteId;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->model = $this->objectManager->get(CustomerRegistry::class);
        $this->storeManager = $this->objectManager->get(StoreManager::class);
        $this->defaultWebsiteId = $this->storeManager->getWebsite('base')->getWebsiteId();
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @return void
     */
    public function testRetrieve(): void
    {
        $customer = $this->model->retrieve(1);
        $this->assertInstanceOf(Customer::class, $customer);
        $this->assertEquals(1, $customer->getId());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @return void
     */
    public function testRetrieveByEmail(): void
    {
        $email = 'customer@example.com';
        $customer = $this->model->retrieveByEmail($email, $this->defaultWebsiteId);
        $this->assertInstanceOf(Customer::class, $customer);
        $this->assertEquals($email, $customer->getEmail());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoAppArea adminhtml
     *
     * @return void
     */
    public function testRetrieveCached(): void
    {
        $customerId = 1;
        $customerBeforeDeletion = $this->model->retrieve($customerId);
        $this->objectManager->get(
            Customer::class
        )->load($customerId)->delete();
        $this->assertEquals($customerBeforeDeletion, $this->model->retrieve($customerId));
        $this->assertEquals($customerBeforeDeletion, $this->model
            ->retrieveByEmail('customer@example.com', $this->defaultWebsiteId));
    }

    /**
     * @return void
     */
    public function testRetrieveException(): void
    {
        $customerId = 1;
        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage(sprintf('No such entity with customerId = %s', $customerId));
        $this->model->retrieve($customerId);
    }

    /**
     * @return void
     */
    public function testRetrieveEmailException(): void
    {
        $email = 'customer@example.com';
        try {
            $this->model->retrieveByEmail($email, $this->defaultWebsiteId);
            $this->fail("NoSuchEntityException was not thrown as expected.");
        } catch (NoSuchEntityException $e) {
            $expectedParams = [
                'fieldName' => 'email',
                'fieldValue' => $email,
                'field2Name' => 'websiteId',
                'field2Value' => 1,
            ];
            $this->assertEquals($expectedParams, $e->getParameters());
        }
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoAppArea adminhtml
     *
     * @return void
     */
    public function testRemove(): void
    {
        $customerId = 1;
        $customer = $this->model->retrieve($customerId);
        $this->assertInstanceOf(Customer::class, $customer);
        $customer->delete();
        $this->model->remove($customerId);
        $this->expectException(NoSuchEntityException::class);
        $this->model->retrieve($customerId);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoAppArea adminhtml
     *
     * @return void
     */
    public function testRemoveByEmail(): void
    {
        $email = 'customer@example.com';
        $customer = $this->model->retrieve(1);
        $this->assertInstanceOf(Customer::class, $customer);
        $customer->delete();
        $this->model->removeByEmail($email, $this->defaultWebsiteId);
        $this->expectException(NoSuchEntityException::class);
        $this->model->retrieveByEmail($email, $customer->getWebsiteId());
    }

    /**
     * Test customer is available for all websites with global account scope config.
     *
     * @magentoConfigFixture current_store customer/account_share/scope 0
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Store/_files/second_website_with_two_stores.php
     *
     * @return void
     */
    public function testRetrieveAccountInGlobalScope(): void
    {
        $email = 'customer@example.com';
        $websiteId = $this->storeManager->getWebsite('test')->getWebsiteId();
        $customer = $this->model->retrieveByEmail($email, $websiteId);
        $this->assertEquals($email, $customer->getEmail());
    }

    /**
     * Test customer is not available for second website with account scope config per websites.
     *
     * @magentoConfigFixture current_store customer/account_share/scope 1
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Store/_files/second_website_with_two_stores.php
     *
     * @return void
     */
    public function testRetrieveAccountInWebsiteScope(): void
    {
        $email = 'customer@example.com';
        $websiteId = $this->storeManager->getWebsite('test')->getWebsiteId();
        $message = sprintf('No such entity with email = %s, websiteId = %s', $email, $websiteId);
        $this->expectExceptionObject(new NoSuchEntityException(__($message)));
        $this->model->retrieveByEmail($email, $websiteId);
    }
}
