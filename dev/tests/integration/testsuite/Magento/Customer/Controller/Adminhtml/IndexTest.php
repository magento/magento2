<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Newsletter\Model\Subscriber;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppArea adminhtml
 */
class IndexTest extends \Magento\Backend\Utility\Controller
{
    /**
     * Base controller URL
     *
     * @var string
     */
    protected $_baseControllerUrl;

    /** @var CustomerRepositoryInterface */
    protected $customerRepository;

    /** @var AddressRepositoryInterface */
    protected $addressRepository;

    /** @var AccountManagementInterface */
    protected $accountManagement;

    protected function setUp()
    {
        parent::setUp();
        $this->_baseControllerUrl = 'http://localhost/index.php/backend/customer/index/';
        $this->customerRepository = Bootstrap::getObjectManager()->get(
            'Magento\Customer\Api\CustomerRepositoryInterface'
        );
        $this->addressRepository = Bootstrap::getObjectManager()->get(
            'Magento\Customer\Api\AddressRepositoryInterface'
        );
        $this->accountManagement = Bootstrap::getObjectManager()->get(
            'Magento\Customer\Api\AccountManagementInterface'
        );
    }

    protected function tearDown()
    {
        /**
         * Unset customer data
         */
        Bootstrap::getObjectManager()->get('Magento\Backend\Model\Session')->setCustomerData(null);

        /**
         * Unset messages
         */
        Bootstrap::getObjectManager()->get('Magento\Backend\Model\Session')->getMessages(true);
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testSaveActionWithEmptyPostData()
    {
        $this->getRequest()->setPost([]);
        $this->dispatch('backend/customer/index/save');
        $this->assertRedirect($this->stringStartsWith($this->_baseControllerUrl));
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testSaveActionWithInvalidFormData()
    {
        $post = ['account' => ['middlename' => 'test middlename', 'group_id' => 1]];
        $this->getRequest()->setPost($post);
        $this->dispatch('backend/customer/index/save');
        /**
         * Check that errors was generated and set to session
         */
        $this->assertSessionMessages(
            $this->logicalNot($this->isEmpty()),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );
        /**
         * Check that customer data were set to session
         */
        $this->assertEquals(
            $post,
            Bootstrap::getObjectManager()->get('Magento\Backend\Model\Session')->getCustomerData()
        );
        $this->assertRedirect($this->stringStartsWith($this->_baseControllerUrl . 'new'));
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testSaveActionWithInvalidCustomerAddressData()
    {
        $post = [
            'account' => [
                'middlename' => 'test middlename',
                'group_id' => 1,
                'website_id' => 0,
                'firstname' => 'test firstname',
                'lastname' => 'test lastname',
                'email' => 'example@domain.com',
                'default_billing' => '_item1',
                'customer_address' => ['_item1' => []],
            ],
        ];
        $this->getRequest()->setPost($post);
        $this->dispatch('backend/customer/index/save');
        /**
         * Check that errors was generated and set to session
         */
        $this->assertSessionMessages(
            $this->logicalNot($this->isEmpty()),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );
        /**
         * Check that customer data were set to session
         */
        $this->assertEquals(
            $post,
            Bootstrap::getObjectManager()->get('Magento\Backend\Model\Session')->getCustomerData()
        );
        $this->assertRedirect($this->stringStartsWith($this->_baseControllerUrl . 'new'));
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testSaveActionWithValidCustomerDataAndValidAddressData()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = Bootstrap::getObjectManager();

        $post = [
            'account' => [
                'middlename' => 'test middlename',
                'group_id' => 1,
                'website_id' => 0,
                'firstname' => 'test firstname',
                'lastname' => 'test lastname',
                'email' => 'example@domain.com',
                'default_billing' => '_item1',
                'password' => 'password',
                'customer_address' => [
                    '_item1' => [
                        'firstname' => 'test firstname',
                        'lastname' => 'test lastname',
                        'street' => ['test street'],
                        'city' => 'test city',
                        'country_id' => 'US',
                        'postcode' => '01001',
                        'telephone' => '+7000000001',
                        'default_billing' => 'true',
                    ],
                ],
            ],
        ];
        $this->getRequest()->setPost($post);
        $this->getRequest()->setParam('back', '1');

        // Emulate setting customer data to session in editAction
        $objectManager->get('Magento\Backend\Model\Session')->setCustomerData($post);

        $this->dispatch('backend/customer/index/save');
        /**
         * Check that errors was generated and set to session
         */
        $this->assertSessionMessages($this->isEmpty(), \Magento\Framework\Message\MessageInterface::TYPE_ERROR);

        /**
         * Check that customer data were cleaned after it was saved successfully
         */
        $this->assertEmpty($objectManager->get('Magento\Backend\Model\Session')->getCustomerData());

        /**
         * Check that success message is set
         */
        $this->assertSessionMessages(
            $this->logicalNot($this->isEmpty()),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );

        /**
         * Check that customer id set and addresses saved
         */
        $registry = $objectManager->get('Magento\Framework\Registry');
        $customerId = $registry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
        $customer = $this->customerRepository->getById($customerId);
        $this->assertEquals('test firstname', $customer->getFirstname());
        $addresses = $customer->getAddresses();
        $this->assertEquals(1, count($addresses));
        $this->assertNotEquals(0, $this->accountManagement->getDefaultBillingAddress($customerId));
        $this->assertNull($this->accountManagement->getDefaultShippingAddress($customerId));

        $this->assertRedirect(
            $this->stringStartsWith($this->_baseControllerUrl . 'edit/id/' . $customerId . '/back/1')
        );

        /** @var \Magento\Newsletter\Model\Subscriber $subscriber */
        $subscriber = $objectManager->get('Magento\Newsletter\Model\SubscriberFactory')->create();
        $this->assertEmpty($subscriber->getId());
        $subscriber->loadByCustomerId($customerId);
        $this->assertEmpty($subscriber->getId());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_sample.php
     */
    public function testSaveActionExistingCustomerAndExistingAddressData()
    {
        $post = [
            'customer_id' => '1',
            'account' => [
                'middlename' => 'test middlename',
                'group_id' => 1,
                'website_id' => 1,
                'firstname' => 'test firstname',
                'lastname' => 'test lastname',
                'email' => 'customer@example.com',
                'new_password' => 'auto',
                'sendemail_store_id' => '1',
                'sendemail' => '1',
                'created_at' => '2000-01-01 00:00:00',
                'default_shipping' => '_item1',
                'default_billing' => 1,
                'customer_address' => [
                    '1' => [
                        'firstname' => 'update firstname',
                        'lastname' => 'update lastname',
                        'street' => ['update street'],
                        'city' => 'update city',
                        'country_id' => 'US',
                        'postcode' => '01001',
                        'telephone' => '+7000000001',
                        'default_billing' => 'true',
                    ],
                    '_item1' => [
                        'firstname' => 'new firstname',
                        'lastname' => 'new lastname',
                        'street' => ['new street'],
                        'city' => 'new city',
                        'country_id' => 'US',
                        'postcode' => '01001',
                        'telephone' => '+7000000001',
                        'default_shipping' => 'true',
                    ],
                    '_template_' => [
                        'firstname' => '',
                        'lastname' => '',
                        'street' => [],
                        'city' => '',
                        'country_id' => 'US',
                        'postcode' => '',
                        'telephone' => '',
                    ],
                ],
            ],

            'subscription' => '',
        ];
        $this->getRequest()->setPost($post);
        $this->getRequest()->setParam('id', 1);
        $this->dispatch('backend/customer/index/save');
        /**
         * Check that success message is set
         */
        $this->assertSessionMessages(
            $this->equalTo(['You saved the customer.']),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );

        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = Bootstrap::getObjectManager();

        /**
         * Check that customer id set and addresses saved
         */
        $registry = $objectManager->get('Magento\Framework\Registry');
        $customerId = $registry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
        $customer = $this->customerRepository->getById($customerId);
        $this->assertEquals('test firstname', $customer->getFirstname());

        /**
         * Addresses should be removed by \Magento\Customer\Model\Resource\Customer::_saveAddresses during _afterSave
         * addressOne - updated
         * addressTwo - removed
         * addressThree - removed
         * _item1 - new address
         */
        $addresses = $customer->getAddresses();
        $this->assertEquals(2, count($addresses));
        $updatedAddress = $this->addressRepository->getById(1);
        $this->assertEquals('update firstname', $updatedAddress->getFirstname());
        $newAddress = $this->accountManagement->getDefaultShippingAddress($customerId);
        $this->assertEquals('new firstname', $newAddress->getFirstname());

        /** @var \Magento\Newsletter\Model\Subscriber $subscriber */
        $subscriber = $objectManager->get('Magento\Newsletter\Model\SubscriberFactory')->create();
        $this->assertEmpty($subscriber->getId());
        $subscriber->loadByCustomerId($customerId);
        $this->assertNotEmpty($subscriber->getId());
        $this->assertEquals(1, $subscriber->getStatus());

        $this->assertRedirect($this->stringStartsWith($this->_baseControllerUrl . 'index/key/'));
    }

    /**
     * @magentoDataFixture Magento/Newsletter/_files/subscribers.php
     */
    public function testSaveActionExistingCustomerUnsubscribeNewsletter()
    {
        $customerId = 1;
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = Bootstrap::getObjectManager();

        /** @var \Magento\Newsletter\Model\Subscriber $subscriber */
        $subscriber = $objectManager->get('Magento\Newsletter\Model\SubscriberFactory')->create();
        $this->assertEmpty($subscriber->getId());
        $subscriber->loadByCustomerId($customerId);
        $this->assertNotEmpty($subscriber->getId());
        $this->assertEquals(1, $subscriber->getStatus());

        $post = [
            'customer_id' => $customerId,
        ];
        $this->getRequest()->setPost($post);
        $this->getRequest()->setParam('id', 1);
        $this->dispatch('backend/customer/index/save');

        /** @var \Magento\Newsletter\Model\Subscriber $subscriber */
        $subscriber = $objectManager->get('Magento\Newsletter\Model\SubscriberFactory')->create();
        $this->assertEmpty($subscriber->getId());
        $subscriber->loadByCustomerId($customerId);
        $this->assertNotEmpty($subscriber->getId());
        $this->assertEquals(3, $subscriber->getStatus());

        /**
         * Check that success message is set
         */
        $this->assertSessionMessages(
            $this->equalTo(['You saved the customer.']),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );

        $this->assertRedirect($this->stringStartsWith($this->_baseControllerUrl . 'index/key/'));
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_sample.php
     */
    public function testSaveActionCoreException()
    {
        $post = [
            'account' => [
                'middlename' => 'test middlename',
                'group_id' => 1,
                'website_id' => 1,
                'firstname' => 'test firstname',
                'lastname' => 'test lastname',
                'email' => 'customer@example.com',
                'password' => 'password',
            ],
        ];
        $this->getRequest()->setPost($post);
        $this->dispatch('backend/customer/index/save');
        /*
         * Check that error message is set
         */
        $this->assertSessionMessages(
            $this->equalTo(['Customer with the same email already exists in associated website.']),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );
        $this->assertEquals(
            $post,
            Bootstrap::getObjectManager()->get('Magento\Backend\Model\Session')->getCustomerData()
        );
        $this->assertRedirect($this->stringStartsWith($this->_baseControllerUrl . 'new/key/'));
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_sample.php
     */
    public function testEditAction()
    {
        $customerData = [
            'customer_id' => '1',
            'account' => [
                'middlename' => 'new middlename',
                'group_id' => 1,
                'website_id' => 1,
                'firstname' => 'new firstname',
                'lastname' => 'new lastname',
                'email' => 'customer@example.com',
                'default_shipping' => '_item1',
                'new_password' => 'auto',
                'sendemail_store_id' => '1',
                'sendemail' => '1',
                'created_at' => '2000-01-01 00:00:00',
                'customer_address' => [
                    '1' => [
                        'firstname' => 'update firstname',
                        'lastname' => 'update lastname',
                        'street' => ['update street'],
                        'city' => 'update city',
                        'country_id' => 'US',
                        'postcode' => '01001',
                        'telephone' => '+7000000001',
                    ],
                    '_item1' => [
                        'firstname' => 'default firstname',
                        'lastname' => 'default lastname',
                        'street' => ['default street'],
                        'city' => 'default city',
                        'country_id' => 'US',
                        'postcode' => '01001',
                        'telephone' => '+7000000001',
                    ],
                    '_template_' => [
                        'firstname' => '',
                        'lastname' => '',
                        'street' => [],
                        'city' => '',
                        'country_id' => 'US',
                        'postcode' => '',
                        'telephone' => '',
                    ],
                ],
            ],
        ];
        /**
         * set customer data
         */
        Bootstrap::getObjectManager()->get('Magento\Backend\Model\Session')->setCustomerData($customerData);
        $this->getRequest()->setParam('id', 1);
        $this->dispatch('backend/customer/index/edit');
        $body = $this->getResponse()->getBody();

        // verify
        $this->assertContains('<h1 class="title">new firstname new lastname</h1>', $body);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_sample.php
     */
    public function testEditActionNoSessionData()
    {
        $this->getRequest()->setParam('id', 1);
        $this->dispatch('backend/customer/index/edit');
        $body = $this->getResponse()->getBody();

        // verify
        $this->assertContains('<h1 class="title">test firstname test lastname</h1>', $body);
    }

    public function testNewAction()
    {
        $this->dispatch('backend/customer/index/edit');
        $body = $this->getResponse()->getBody();

        // verify
        $this->assertContains('<h1 class="title">New Customer</h1>', $body);
    }

    /**
     * Test the editing of a new customer that has not been saved but the page has been reloaded
     */
    public function testNewActionWithCustomerData()
    {
        $customerData = [
            'customer_id' => 0,
            'account' => [
                'created_in' => false,
                'disable_auto_group_change' => false,
                'email' => false,
                'firstname' => false,
                'group_id' => false,
                'lastname' => false,
                'website_id' => false,
                'customer_address' => [],
            ],
        ];
        $context = Bootstrap::getObjectManager()->get('Magento\Backend\Block\Template\Context');
        $context->getBackendSession()->setCustomerData($customerData);
        $this->testNewAction();
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_sample.php
     */
    public function testDeleteAction()
    {
        $this->getRequest()->setParam('id', 1);
        $this->dispatch('backend/customer/index/delete');
        $this->assertRedirect($this->stringContains('customer/index'));
        $this->assertSessionMessages(
            $this->equalTo(['You deleted the customer.']),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_sample.php
     */
    public function testNotExistingCustomerDeleteAction()
    {
        $this->getRequest()->setParam('id', 2);
        $this->dispatch('backend/customer/index/delete');
        $this->assertRedirect($this->stringContains('customer/index'));
        $this->assertSessionMessages(
            $this->equalTo(['No such entity with customerId = 2']),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_sample.php
     */
    public function testGridAction()
    {
        $this->dispatch('backend/customer/index/grid');

        $body = $this->getResponse()->getBody();

        $this->assertContains('test firstname test lastname', $body);
        $this->assertContains('customer@example.com', $body);
        $this->assertContains('+7000000001', $body);
        $this->assertContains('United States', $body);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_sample.php
     */
    public function testCartAction()
    {
        $this->getRequest()->setParam('id', 1)->setParam('website_id', 1)->setPost('delete', 1);
        $this->dispatch('backend/customer/index/cart');
        $body = $this->getResponse()->getBody();
        $this->assertContains('<div id="customer_cart_grid1">', $body);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_sample.php
     */
    public function testProductReviewsAction()
    {
        $this->getRequest()->setParam('id', 1);
        $this->dispatch('backend/customer/index/productReviews');
        $body = $this->getResponse()->getBody();
        $this->assertContains('<div id="reviwGrid">', $body);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/two_customers.php
     */
    public function testMassSubscriberAction()
    {
        // Pre-condition
        /** @var \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory */
        $subscriberFactory = Bootstrap::getObjectManager()->get('Magento\Newsletter\Model\SubscriberFactory');
        $this->assertNull($subscriberFactory->create()->loadByCustomerId(1)->getSubscriberStatus());
        $this->assertNull($subscriberFactory->create()->loadByCustomerId(2)->getSubscriberStatus());
        // Setup
        $this->getRequest()->setParam('customer', [1, 2]);

        // Test
        $this->dispatch('backend/customer/index/massSubscribe');

        // Assertions
        $this->assertRedirect($this->stringContains('customer/index'));
        $this->assertSessionMessages(
            $this->equalTo(['A total of 2 record(s) were updated.']),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
        $this->assertEquals(
            Subscriber::STATUS_SUBSCRIBED,
            $subscriberFactory->create()->loadByCustomerId(1)->getSubscriberStatus()
        );
        $this->assertEquals(
            Subscriber::STATUS_SUBSCRIBED,
            $subscriberFactory->create()->loadByCustomerId(2)->getSubscriberStatus()
        );
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testMassSubscriberActionNoSelection()
    {
        $this->dispatch('backend/customer/index/massSubscribe');

        $this->assertRedirect($this->stringContains('customer/index'));
        $this->assertSessionMessages(
            $this->equalTo(['Please select customer(s).']),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testMassSubscriberActionInvalidId()
    {
        $this->getRequest()->setParam('customer', [4200]);

        $this->dispatch('backend/customer/index/massSubscribe');

        $this->assertRedirect($this->stringContains('customer/index'));
        $this->assertSessionMessages(
            $this->equalTo(['No such entity with customerId = 4200']),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/two_customers.php
     */
    public function testMassSubscriberActionPartialUpdate()
    {
        // Pre-condition
        /** @var \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory */
        $subscriberFactory = Bootstrap::getObjectManager()->get('Magento\Newsletter\Model\SubscriberFactory');
        $this->assertNull($subscriberFactory->create()->loadByCustomerId(1)->getSubscriberStatus());
        $this->assertNull($subscriberFactory->create()->loadByCustomerId(2)->getSubscriberStatus());
        // Setup
        $this->getRequest()->setParam('customer', [1, 4200, 2]);

        // Test
        $this->dispatch('backend/customer/index/massSubscribe');

        // Assertions
        $this->assertRedirect($this->stringContains('customer/index'));
        $this->assertSessionMessages(
            $this->equalTo(['A total of 2 record(s) were updated.']),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
        $this->assertSessionMessages(
            $this->equalTo(['No such entity with customerId = 4200']),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );
        $this->assertEquals(
            Subscriber::STATUS_SUBSCRIBED,
            $subscriberFactory->create()->loadByCustomerId(1)->getSubscriberStatus()
        );
        $this->assertEquals(
            Subscriber::STATUS_SUBSCRIBED,
            $subscriberFactory->create()->loadByCustomerId(2)->getSubscriberStatus()
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testMassDeleteAction()
    {
        $this->getRequest()->setPost('customer', [1]);
        $this->dispatch('backend/customer/index/massDelete');
        $this->assertSessionMessages(
            $this->equalTo(['A total of 1 record(s) were deleted.']),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
        $this->assertRedirect($this->stringContains('customer/index'));
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testInvalidIdMassDeleteAction()
    {
        $this->getRequest()->setPost('customer', [1]);
        $this->dispatch('backend/customer/index/massDelete');
        $this->assertSessionMessages(
            $this->equalTo(['No such entity with customerId = 1']),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );
    }

    /**
     * Valid group Id but no customer Ids specified
     * @magentoDbIsolation enabled
     */
    public function testMassDeleteActionNoCustomerIds()
    {
        $this->dispatch('backend/customer/index/massDelete');
        $this->assertSessionMessages(
            $this->equalTo(['Please select customer(s).']),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/two_customers.php
     */
    public function testMassDeleteActionPartialUpdate()
    {
        $this->getRequest()->setPost('customer', [1, 999, 2, 9999]);
        $this->dispatch('backend/customer/index/massDelete');
        $this->assertSessionMessages(
            $this->equalTo(['A total of 2 record(s) were deleted.']),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
        $this->assertSessionMessages(
            $this->equalTo(['No such entity with customerId = 999', 'No such entity with customerId = 9999']),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testMassAssignGroupAction()
    {
        $customer = $this->customerRepository->getById(1);
        $this->assertEquals(1, $customer->getGroupId());

        $this->getRequest()->setParam('group', 0)->setPost('customer', [1]);
        $this->dispatch('backend/customer/index/massAssignGroup');
        $this->assertSessionMessages(
            $this->equalTo(['A total of 1 record(s) were updated.']),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
        $this->assertRedirect($this->stringContains('customer/index'));

        $customer = $this->customerRepository->getById(1);
        $this->assertEquals(0, $customer->getGroupId());
    }

    /**
     * Valid group Id but no data fixture so no customer exists with customer Id = 1
     * @magentoDbIsolation enabled
     */
    public function testMassAssignGroupActionInvalidCustomerId()
    {
        $this->getRequest()->setParam('group', 0)->setPost('customer', [1]);
        $this->dispatch('backend/customer/index/massAssignGroup');
        $this->assertSessionMessages(
            $this->equalTo(['No such entity with customerId = 1']),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );
    }

    /**
     * Valid group Id but no customer Ids specified
     * @magentoDbIsolation enabled
     */
    public function testMassAssignGroupActionNoCustomerIds()
    {
        $this->getRequest()->setParam('group', 0);
        $this->dispatch('backend/customer/index/massAssignGroup');
        $this->assertSessionMessages(
            $this->equalTo(['Please select customer(s).']),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/two_customers.php
     */
    public function testMassAssignGroupActionPartialUpdate()
    {
        $this->assertEquals(1, $this->customerRepository->getById(1)->getGroupId());
        $this->assertEquals(1, $this->customerRepository->getById(2)->getGroupId());

        $this->getRequest()->setParam('group', 0)->setPost('customer', [1, 4200, 2]);
        $this->dispatch('backend/customer/index/massAssignGroup');
        $this->assertSessionMessages(
            $this->equalTo(['A total of 2 record(s) were updated.']),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
        $this->assertSessionMessages(
            $this->equalTo(['No such entity with customerId = 4200']),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );

        $this->assertEquals(0, $this->customerRepository->getById(1)->getGroupId());
        $this->assertEquals(0, $this->customerRepository->getById(2)->getGroupId());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/two_customers.php
     */
    public function testMassUnsubscriberAction()
    {
        // Setup
        /** @var \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory */
        $subscriberFactory = Bootstrap::getObjectManager()->get('Magento\Newsletter\Model\SubscriberFactory');
        $subscriberFactory->create()->subscribeCustomerById(1);
        $subscriberFactory->create()->subscribeCustomerById(2);
        $this->getRequest()->setParam('customer', [1, 2]);

        // Test
        $this->dispatch('backend/customer/index/massUnsubscribe');

        // Assertions
        $this->assertRedirect($this->stringContains('customer/index'));
        $this->assertSessionMessages(
            $this->equalTo(['A total of 2 record(s) were updated.']),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
        $this->assertEquals(
            Subscriber::STATUS_UNSUBSCRIBED,
            $subscriberFactory->create()->loadByCustomerId(1)->getSubscriberStatus()
        );
        $this->assertEquals(
            Subscriber::STATUS_UNSUBSCRIBED,
            $subscriberFactory->create()->loadByCustomerId(2)->getSubscriberStatus()
        );
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testMassUnsubscriberActionNoSelection()
    {
        $this->dispatch('backend/customer/index/massUnsubscribe');

        $this->assertRedirect($this->stringContains('customer/index'));
        $this->assertSessionMessages(
            $this->equalTo(['Please select customer(s).']),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testMassUnsubscriberActionInvalidId()
    {
        $this->getRequest()->setParam('customer', [4200]);

        $this->dispatch('backend/customer/index/massUnsubscribe');

        $this->assertRedirect($this->stringContains('customer/index'));
        $this->assertSessionMessages(
            $this->equalTo(['No such entity with customerId = 4200']),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/two_customers.php
     */
    public function testMassUnsubscriberActionPartialUpdate()
    {
        // Setup
        /** @var \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory */
        $subscriberFactory = Bootstrap::getObjectManager()->get('Magento\Newsletter\Model\SubscriberFactory');
        $subscriberFactory->create()->subscribeCustomerById(1);
        $subscriberFactory->create()->subscribeCustomerById(2);
        $this->getRequest()->setParam('customer', [1, 4200, 2]);

        // Test
        $this->dispatch('backend/customer/index/massUnsubscribe');

        // Assertions
        $this->assertRedirect($this->stringContains('customer/index'));
        $this->assertSessionMessages(
            $this->equalTo(['A total of 2 record(s) were updated.']),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
        $this->assertSessionMessages(
            $this->equalTo(['No such entity with customerId = 4200']),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );
        $this->assertEquals(
            Subscriber::STATUS_UNSUBSCRIBED,
            $subscriberFactory->create()->loadByCustomerId(1)->getSubscriberStatus()
        );
        $this->assertEquals(
            Subscriber::STATUS_UNSUBSCRIBED,
            $subscriberFactory->create()->loadByCustomerId(2)->getSubscriberStatus()
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     */
    public function testValidateCustomerWithAddressSuccess()
    {
        $customerData = [
            'id' => '1',
            'account' => [
                'middlename' => 'new middlename',
                'group_id' => 1,
                'website_id' => 1,
                'firstname' => 'new firstname',
                'lastname' => 'new lastname',
                'email' => 'example@domain.com',
                'default_shipping' => '_item1',
                'new_password' => 'auto',
                'sendemail_store_id' => '1',
                'sendemail' => '1',
            ],
            'address' => [
                '_item1' => [
                    'firstname' => 'update firstname',
                    'lastname' => 'update lastname',
                    'street' => ['update street'],
                    'city' => 'update city',
                    'country_id' => 'US',
                    'postcode' => '01001',
                    'telephone' => '+7000000001',
                ],
                '_template_' => [
                    'firstname' => '',
                    'lastname' => '',
                    'street' => [],
                    'city' => '',
                    'country_id' => 'US',
                    'postcode' => '',
                    'telephone' => '',
                ],
            ],
        ];
        /**
         * set customer data
         */
        $this->getRequest()->setParams($customerData);
        $this->dispatch('backend/customer/index/validate');
        $body = $this->getResponse()->getBody();

        /**
         * Check that no errors were generated and set to session
         */
        $this->assertSessionMessages($this->isEmpty(), \Magento\Framework\Message\MessageInterface::TYPE_ERROR);

        $this->assertEquals('{"error":0}', $body);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     */
    public function testValidateCustomerWithAddressFailure()
    {
        $customerData = [
            'id' => '1',
            'account' => [
                'middlename' => 'new middlename',
                'group_id' => 1,
                'website_id' => 1,
                'firstname' => '',
                'lastname' => '',
                'email' => '*',
                'default_shipping' => '_item1',
                'new_password' => 'auto',
                'sendemail_store_id' => '1',
                'sendemail' => '1',
                'customer_address' => [
                    '1' => [
                        'firstname' => '',
                        'lastname' => '',
                        'street' => ['update street'],
                        'city' => 'update city',
                        'postcode' => '01001',
                        'telephone' => '',
                    ],
                    '_template_' => [
                        'lastname' => '',
                        'street' => [],
                        'city' => '',
                        'country_id' => 'US',
                        'postcode' => '',
                        'telephone' => '',
                    ],
                ],
            ],
        ];
        /**
         * set customer data
         */
        $this->getRequest()->setParams($customerData);
        $this->dispatch('backend/customer/index/validate');
        $body = $this->getResponse()->getBody();

        $this->assertContains('{"error":1,"html_message":', $body);
        $this->assertContains('Please correct this email address: &quot;*&quot;.', $body);
        $this->assertContains('&quot;First Name&quot; is a required value.', $body);
        $this->assertContains('&quot;Last Name&quot; is a required value.', $body);
        $this->assertContains('&quot;Phone Number&quot; is a required value.', $body);
        $this->assertContains('&quot;Country&quot; is a required value.', $body);
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testResetPasswordActionNoCustomerId()
    {
        // No customer ID in post, will just get redirected to base
        $this->dispatch('backend/customer/index/resetPassword');
        $this->assertRedirect($this->stringStartsWith($this->_baseControllerUrl));
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testResetPasswordActionBadCustomerId()
    {
        // Bad customer ID in post, will just get redirected to base
        $this->getRequest()->setPost(['customer_id' => '789']);
        $this->dispatch('backend/customer/index/resetPassword');
        $this->assertRedirect($this->stringStartsWith($this->_baseControllerUrl));
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testResetPasswordActionSuccess()
    {
        $this->getRequest()->setPost(['customer_id' => '1']);
        $this->dispatch('backend/customer/index/resetPassword');
        $this->assertSessionMessages(
            $this->equalTo(['Customer will receive an email with a link to reset password.']),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
        $this->assertRedirect($this->stringStartsWith($this->_baseControllerUrl . 'edit'));
    }
}
