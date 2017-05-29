<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Model\Plugin;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppIsolation enabled
 */
class PluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Customer Account Service
     *
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    public function setUp()
    {
        $this->accountManagement = Bootstrap::getObjectManager()->get(
            \Magento\Customer\Api\AccountManagementInterface::class
        );
        $this->customerRepository = Bootstrap::getObjectManager()->get(
            \Magento\Customer\Api\CustomerRepositoryInterface::class
        );
    }

    public function tearDown()
    {
        /** @var \Magento\Customer\Model\CustomerRegistry $customerRegistry */
        $customerRegistry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\Customer\Model\CustomerRegistry::class);
        //Cleanup customer from registry
        $customerRegistry->remove(1);
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDataFixture Magento/Newsletter/_files/subscribers.php
     */
    public function testCustomerCreated()
    {
        $objectManager = Bootstrap::getObjectManager();

        /** @var \Magento\Newsletter\Model\Subscriber $subscriber */
        $subscriber = $objectManager->create(\Magento\Newsletter\Model\Subscriber::class);
        $subscriber->loadByEmail('customer_two@example.com');
        $this->assertTrue($subscriber->isSubscribed());
        $this->assertEquals(0, (int)$subscriber->getCustomerId());

        /** @var \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerFactory */
        $customerFactory = $objectManager->get(\Magento\Customer\Api\Data\CustomerInterfaceFactory::class);
        $customerDataObject = $customerFactory->create()
            ->setFirstname('Firstname')
            ->setLastname('Lastname')
            ->setEmail('customer_two@example.com');
        $createdCustomer = $this->customerRepository->save(
            $customerDataObject,
            $this->accountManagement->getPasswordHash('password')
        );

        $subscriber->loadByEmail('customer_two@example.com');
        $this->assertTrue($subscriber->isSubscribed());
        $this->assertEquals((int)$createdCustomer->getId(), (int)$subscriber->getCustomerId());
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     */
    public function testCustomerCreatedNotSubscribed()
    {
        $this->verifySubscriptionNotExist('customer@example.com');

        $objectManager = Bootstrap::getObjectManager();
        /** @var \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerFactory */
        $customerFactory = $objectManager->get(\Magento\Customer\Api\Data\CustomerInterfaceFactory::class);
        $customerDataObject = $customerFactory->create()
            ->setFirstname('Firstname')
            ->setLastname('Lastname')
            ->setEmail('customer@example.com');
        $this->accountManagement->createAccount($customerDataObject);

        $this->verifySubscriptionNotExist('customer@example.com');
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDataFixture Magento/Newsletter/_files/subscribers.php
     */
    public function testCustomerUpdatedEmail()
    {
        $objectManager = Bootstrap::getObjectManager();

        /** @var \Magento\Newsletter\Model\Subscriber $subscriber */
        $subscriber = $objectManager->create(\Magento\Newsletter\Model\Subscriber::class);
        $subscriber->loadByEmail('customer@example.com');
        $this->assertTrue($subscriber->isSubscribed());
        $this->assertEquals(1, (int)$subscriber->getCustomerId());

        $customer = $this->customerRepository->getById(1);
        $customer->setEmail('new@example.com');
        $this->customerRepository->save($customer);

        $subscriber->loadByEmail('new@example.com');
        $this->assertTrue($subscriber->isSubscribed());
        $this->assertEquals(1, (int)$subscriber->getCustomerId());
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDataFixture Magento/Newsletter/_files/subscribers.php
     */
    public function testCustomerDeletedByIdAdminArea()
    {
        $objectManager = Bootstrap::getObjectManager();

        /** @var \Magento\Newsletter\Model\Subscriber $subscriber */
        $subscriber = $objectManager->create(\Magento\Newsletter\Model\Subscriber::class);
        $subscriber->loadByEmail('customer@example.com');
        $this->assertTrue($subscriber->isSubscribed());

        $this->customerRepository->deleteById(1);

        $this->verifySubscriptionNotExist('customer@example.com');
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDataFixture Magento/Newsletter/_files/subscribers.php
     */
    public function testCustomerDeletedAdminArea()
    {
        $customer = $this->customerRepository->getById(1);
        $objectManager = Bootstrap::getObjectManager();
        /** @var \Magento\Newsletter\Model\Subscriber $subscriber */
        $subscriber = $objectManager->create(\Magento\Newsletter\Model\Subscriber::class);
        $subscriber->loadByEmail('customer@example.com');
        $this->assertTrue($subscriber->isSubscribed());
        $this->customerRepository->delete($customer);
        $this->verifySubscriptionNotExist('customer@example.com');
    }

    /**
     * Verify a subscription doesn't exist for a given email address
     *
     * @param string $email
     * @return \Magento\Newsletter\Model\Subscriber
     */
    private function verifySubscriptionNotExist($email)
    {
        $objectManager = Bootstrap::getObjectManager();

        /** @var \Magento\Newsletter\Model\Subscriber $subscriber */
        $subscriber = $objectManager->create(\Magento\Newsletter\Model\Subscriber::class);
        $subscriber->loadByEmail($email);
        $this->assertFalse($subscriber->isSubscribed());
        $this->assertEquals(0, (int)$subscriber->getId());
        return $subscriber;
    }
}
