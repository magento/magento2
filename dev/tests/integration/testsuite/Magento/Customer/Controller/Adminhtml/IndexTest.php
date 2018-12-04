<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Controller\Adminhtml;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Customer\Model\EmailNotification;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppArea adminhtml
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IndexTest extends \Magento\TestFramework\TestCase\AbstractBackendController
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

    /** @var \Magento\Framework\Data\Form\FormKey */
    protected $formKey;

    /**@var \Magento\Customer\Helper\View */
    protected $customerViewHelper;

    /** @var \Magento\TestFramework\ObjectManager */
    protected $objectManager;

    protected function setUp()
    {
        parent::setUp();
        $this->_baseControllerUrl = 'http://localhost/index.php/backend/customer/index/';
        $this->customerRepository = Bootstrap::getObjectManager()->get(
            \Magento\Customer\Api\CustomerRepositoryInterface::class
        );
        $this->addressRepository = Bootstrap::getObjectManager()->get(
            \Magento\Customer\Api\AddressRepositoryInterface::class
        );
        $this->accountManagement = Bootstrap::getObjectManager()->get(
            \Magento\Customer\Api\AccountManagementInterface::class
        );
        $this->formKey = Bootstrap::getObjectManager()->get(
            \Magento\Framework\Data\Form\FormKey::class
        );

        $this->objectManager = Bootstrap::getObjectManager();
        $this->customerViewHelper = $this->objectManager->get(
            \Magento\Customer\Helper\View::class
        );
    }

    protected function tearDown()
    {
        /**
         * Unset customer data
         */
        Bootstrap::getObjectManager()->get(\Magento\Backend\Model\Session::class)->setCustomerData(null);

        /**
         * Unset messages
         */
        Bootstrap::getObjectManager()->get(\Magento\Backend\Model\Session::class)->getMessages(true);
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testSaveActionWithEmptyPostData()
    {
        $this->getRequest()->setPostValue([]);
        $this->dispatch('backend/customer/index/save');
        $this->assertRedirect($this->stringStartsWith($this->_baseControllerUrl));
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testSaveActionWithInvalidFormData()
    {
        $post = ['account' => ['middlename' => 'test middlename', 'group_id' => 1]];
        $this->getRequest()->setPostValue($post);
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
            $this->objectManager->get(\Magento\Backend\Model\Session::class)->getCustomerFormData()
        );
        $this->assertRedirect($this->stringStartsWith($this->_baseControllerUrl . 'new'));
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testSaveActionWithInvalidCustomerAddressData()
    {
        $post = [
            'customer' => [
                'middlename' => 'test middlename',
                'group_id' => 1,
                'website_id' => 0,
                'firstname' => 'test firstname',
                'lastname' => 'test lastname',
                'email' => 'example@domain.com',
                'default_billing' => '_item1',
            ],
            'address' => ['_item1' => []],
        ];
        $this->getRequest()->setPostValue($post);
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
            $this->objectManager->get(\Magento\Backend\Model\Session::class)->getCustomerFormData()
        );
        $this->assertRedirect($this->stringStartsWith($this->_baseControllerUrl . 'new'));
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testSaveActionWithValidCustomerDataAndValidAddressData()
    {
        $post = [
            'customer' => [
                'middlename' => 'test middlename',
                'group_id' => 1,
                'website_id' => 0,
                'firstname' => 'test firstname',
                'lastname' => 'test lastname',
                'email' => 'example@domain.com',
                'default_billing' => '_item1',
                'password' => 'password',
            ],
            'address' => [
                '_item1' => [
                    'firstname' => 'test firstname',
                    'lastname' => 'test lastname',
                    'street' => ['test street'],
                    'city' => 'test city',
                    'region_id' => 10,
                    'country_id' => 'US',
                    'postcode' => '01001',
                    'telephone' => '+7000000001',
                    'default_billing' => 'true',
                ],
            ],
        ];
        $this->getRequest()->setPostValue($post);
        $this->getRequest()->setParam('back', '1');

        // Emulate setting customer data to session in editAction
        $this->objectManager->get(\Magento\Backend\Model\Session::class)->setCustomerFormData($post);

        $this->dispatch('backend/customer/index/save');
        /**
         * Check that errors was generated and set to session
         */
        $this->assertSessionMessages($this->isEmpty(), \Magento\Framework\Message\MessageInterface::TYPE_ERROR);

        /**
         * Check that customer data were cleaned after it was saved successfully
         */
        $this->assertEmpty($this->objectManager->get(\Magento\Backend\Model\Session::class)->getCustomerData());

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
        $registry = $this->objectManager->get(\Magento\Framework\Registry::class);
        $customerId = $registry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
        $customer = $this->customerRepository->getById($customerId);
        $this->assertEquals('test firstname', $customer->getFirstname());
        $addresses = $customer->getAddresses();
        $this->assertEquals(1, count($addresses));
        $this->assertNotEquals(0, $this->accountManagement->getDefaultBillingAddress($customerId));
        $this->assertNull($this->accountManagement->getDefaultShippingAddress($customerId));

        $urlPatternParts = [
            $this->_baseControllerUrl . 'edit',
            'id/' . $customerId,
            'back/1',
        ];
        $urlPattern = '/^' . str_replace('/', '\/', implode('(/.*/)|/', $urlPatternParts)) . '/';

        $this->assertRedirect(
            $this->matchesRegularExpression($urlPattern)
        );

        /** @var \Magento\Newsletter\Model\Subscriber $subscriber */
        $subscriber = $this->objectManager->get(\Magento\Newsletter\Model\SubscriberFactory::class)->create();
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
            'customer' => [
                'entity_id' => '1',
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
            ],
            'address' => [
                '1' => [
                    'firstname' => 'update firstname',
                    'lastname' => 'update lastname',
                    'street' => ['update street'],
                    'city' => 'update city',
                    'region_id' => 10,
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
                    'region_id' => 10,
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
                    'region_id' => 10,
                    'country_id' => 'US',
                    'postcode' => '',
                    'telephone' => '',
                ],
            ],
            'subscription' => '',
        ];
        $this->getRequest()->setPostValue($post);
        $this->getRequest()->setParam('id', 1);
        $this->dispatch('backend/customer/index/save');

        /** Check that success message is set */
        $this->assertSessionMessages(
            $this->equalTo(['You saved the customer.']),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );

        /** Check that customer id set and addresses saved */
        $registry = $this->objectManager->get(\Magento\Framework\Registry::class);
        $customerId = $registry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
        $customer = $this->customerRepository->getById($customerId);
        $this->assertEquals('test firstname', $customer->getFirstname());

        /**
         * Addresses should be removed by
         * \Magento\Customer\Model\ResourceModel\Customer::_saveAddresses during _afterSave
         * addressOne - updated
         * addressTwo - removed
         * addressThree - removed
         * _item1 - new address
         */
        $addresses = $customer->getAddresses();
        $this->assertEquals(2, count($addresses));
        $updatedAddress = $this->addressRepository->getById(1);
        $this->assertEquals('update firstname', $updatedAddress->getFirstname());
        $this->assertTrue($updatedAddress->isDefaultBilling());
        $this->assertEquals($updatedAddress->getId(), $customer->getDefaultBilling());
        $newAddress = $this->accountManagement->getDefaultShippingAddress($customerId);
        $this->assertEquals('new firstname', $newAddress->getFirstname());

        /** @var \Magento\Newsletter\Model\Subscriber $subscriber */
        $subscriber = $this->objectManager->get(\Magento\Newsletter\Model\SubscriberFactory::class)->create();
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

        /** @var \Magento\Newsletter\Model\Subscriber $subscriber */
        $subscriber = $this->objectManager->get(\Magento\Newsletter\Model\SubscriberFactory::class)->create();
        $this->assertEmpty($subscriber->getId());
        $subscriber->loadByCustomerId($customerId);
        $this->assertNotEmpty($subscriber->getId());
        $this->assertEquals(1, $subscriber->getStatus());

        $post = [
            'customer' => [
                'entity_id' => $customerId,
                'email' => 'customer@example.com',
                'firstname' => 'test firstname',
                'lastname' => 'test lastname',
                'sendemail_store_id' => 1
            ],
            'subscription' => '0'
        ];
        $this->getRequest()->setPostValue($post);
        $this->getRequest()->setParam('id', 1);
        $this->dispatch('backend/customer/index/save');

        /** @var \Magento\Newsletter\Model\Subscriber $subscriber */
        $subscriber = $this->objectManager->get(\Magento\Newsletter\Model\SubscriberFactory::class)->create();
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
     * Ensure that an email is sent during save action
     *
     * @magentoConfigFixture current_store customer/account_information/change_email_template change_email_template
     * @magentoConfigFixture current_store customer/password/forgot_email_identity support
     * @magentoDataFixture Magento/Customer/_files/customer_sample.php
     */
    public function testSaveActionExistingCustomerChangeEmail()
    {
        $customerId = 1;
        $newEmail = 'newcustomer@example.com';
        $transportBuilderMock = $this->prepareEmailMock(
            2,
            'change_email_template',
            [
                'name' => 'CustomerSupport',
                'email' => 'support@example.com'
            ],
            $customerId,
            $newEmail
        );
        $this->addEmailMockToClass($transportBuilderMock, EmailNotification::class);
        $post = [
            'customer' => [
                'entity_id' => $customerId,
                'middlename' => 'test middlename',
                'group_id' => 1,
                'website_id' => 1,
                'firstname' => 'test firstname',
                'lastname' => 'test lastname',
                'email' => $newEmail,
                'new_password' => 'auto',
                'sendemail_store_id' => '1',
                'sendemail' => '1',
                'created_at' => '2000-01-01 00:00:00',
                'default_shipping' => '_item1',
                'default_billing' => 1,
            ]
        ];
        $this->getRequest()->setPostValue($post);
        $this->getRequest()->setParam('id', 1);
        $this->dispatch('backend/customer/index/save');

        /**
         * Check that no errors were generated and set to session
         */
        $this->assertSessionMessages($this->isEmpty(), \Magento\Framework\Message\MessageInterface::TYPE_ERROR);
        $this->assertRedirect($this->stringStartsWith($this->_baseControllerUrl . 'index/key/'));
    }

    /**
     * Ensure that an email is sent during inlineEdit action
     *
     * @magentoConfigFixture current_store customer/account_information/change_email_template change_email_template
     * @magentoConfigFixture current_store customer/password/forgot_email_identity support
     * @magentoDataFixture Magento/Customer/_files/customer_sample.php
     */
    public function testInlineEditChangeEmail()
    {
        $customerId = 1;
        $newEmail = 'newcustomer@example.com';
        $transportBuilderMock = $this->prepareEmailMock(
            2,
            'change_email_template',
            [
                'name' => 'CustomerSupport',
                'email' => 'support@example.com'
            ],
            $customerId,
            $newEmail
        );
        $this->addEmailMockToClass($transportBuilderMock, EmailNotification::class);
        $post = [
            'items' => [
                $customerId => [
                    'middlename' => 'test middlename',
                    'group_id' => 1,
                    'website_id' => 1,
                    'firstname' => 'test firstname',
                    'lastname' => 'test lastname',
                    'email' => $newEmail,
                    'password' => 'password',
                ],
            ]
        ];
        $this->getRequest()->setParam('ajax', true)->setParam('isAjax', true);
        $this->getRequest()->setPostValue($post);
        $this->getRequest()->setParam('id', 1);
        $this->dispatch('backend/customer/index/inlineEdit');

        /**
         * Check that no errors were generated and set to session
         */
        $this->assertSessionMessages($this->isEmpty(), \Magento\Framework\Message\MessageInterface::TYPE_ERROR);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_sample.php
     */
    public function testSaveActionCoreException()
    {
        $post = [
            'customer' => [
                'middlename' => 'test middlename',
                'group_id' => 1,
                'website_id' => 1,
                'firstname' => 'test firstname',
                'lastname' => 'test lastname',
                'email' => 'customer@example.com',
                'password' => 'password',
            ],
        ];
        $this->getRequest()->setPostValue($post);
        $this->dispatch('backend/customer/index/save');
        /*
         * Check that error message is set
         */
        $this->assertSessionMessages(
            $this->equalTo(['A customer with the same email already exists in an associated website.']),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );
        $this->assertEquals(
            $post,
            Bootstrap::getObjectManager()->get(\Magento\Backend\Model\Session::class)->getCustomerFormData()
        );
        $this->assertRedirect($this->stringStartsWith($this->_baseControllerUrl . 'new/key/'));
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_sample.php
     */
    public function testEditAction()
    {
        $this->getRequest()->setParam('id', 1);
        $this->dispatch('backend/customer/index/edit');
        $body = $this->getResponse()->getBody();

        // verify
        $this->assertContains('<h1 class="page-title">test firstname test lastname</h1>', $body);
    }

    public function testNewAction()
    {
        $this->dispatch('backend/customer/index/edit');
        $body = $this->getResponse()->getBody();

        // verify
        $this->assertContains('<h1 class="page-title">New Customer</h1>', $body);
    }

    /**
     * Test the editing of a new customer that has not been saved but the page has been reloaded
     */
    public function testNewActionWithCustomerData()
    {
        $customerData = [
            'customer_id' => 0,
            'customer' => [
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
        $context = Bootstrap::getObjectManager()->get(\Magento\Backend\Block\Template\Context::class);
        $context->getBackendSession()->setCustomerData($customerData);
        $this->testNewAction();
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_sample.php
     */
    public function testDeleteAction()
    {
        $this->getRequest()->setParam('id', 1);
        $this->getRequest()->setParam('form_key', $this->formKey->getFormKey());

        $this->getRequest()->setMethod(\Zend\Http\Request::METHOD_POST);

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
        $this->getRequest()->setParam('form_key', $this->formKey->getFormKey());

        $this->getRequest()->setMethod(\Zend\Http\Request::METHOD_POST);

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
    public function testCartAction()
    {
        $this->getRequest()->setParam('id', 1)->setParam('website_id', 1)->setPostValue('delete', 1);
        $this->dispatch('backend/customer/index/cart');
        $body = $this->getResponse()->getBody();
        $this->assertContains('<div id="customer_cart_grid1"', $body);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_sample.php
     */
    public function testProductReviewsAction()
    {
        $this->getRequest()->setParam('id', 1);
        $this->dispatch('backend/customer/index/productReviews');
        $body = $this->getResponse()->getBody();
        $this->assertContains('<div id="reviwGrid"', $body);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     */
    public function testValidateCustomerWithAddressSuccess()
    {
        $customerData = [
            'customer' => [
                'entity_id' => '1',
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
                    'region_id' => 10,
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
            'customer' => [
                'entity_id' => '1',
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
            ],
            'address' => [
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
        ];
        /**
         * set customer data
         */
        $this->getRequest()->setPostValue($customerData);
        $this->dispatch('backend/customer/index/validate');
        $body = $this->getResponse()->getBody();

        $this->assertContains('{"error":true,"messages":', $body);
        $this->assertContains('\"First Name\" is a required value', $body);
        $this->assertContains('\"Last Name\" is a required value.', $body);
        $this->assertContains('\"Country\" is a required value.', $body);
        $this->assertContains('\"Phone Number\" is a required value.', $body);
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
        $this->getRequest()->setPostValue(['customer_id' => '789']);
        $this->dispatch('backend/customer/index/resetPassword');
        $this->assertRedirect($this->stringStartsWith($this->_baseControllerUrl));
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testResetPasswordActionSuccess()
    {
        $this->getRequest()->setPostValue(['customer_id' => '1']);
        $this->dispatch('backend/customer/index/resetPassword');
        $this->assertSessionMessages(
            $this->equalTo(['The customer will receive an email with a link to reset password.']),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
        $this->assertRedirect($this->stringStartsWith($this->_baseControllerUrl . 'edit'));
    }

    /**
     * Prepare email mock to test emails
     *
     * @param int $occurrenceNumber
     * @param string $templateId
     * @param array $sender
     * @param int $customerId
     * @param string|null $newEmail
     * @return \PHPUnit_Framework_MockObject_MockObject
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    protected function prepareEmailMock($occurrenceNumber, $templateId, $sender, $customerId, $newEmail = null)
    {
        $area = \Magento\Framework\App\Area::AREA_FRONTEND;
        $customer = $this->customerRepository->getById($customerId);
        $storeId = $customer->getStoreId();
        $name = $this->customerViewHelper->getCustomerName($customer);

        $transportMock = $this->getMockBuilder(\Magento\Framework\Mail\TransportInterface::class)
            ->setMethods(['sendMessage'])
            ->getMockForAbstractClass();
        $transportMock->expects($this->exactly($occurrenceNumber))
            ->method('sendMessage');
        $transportBuilderMock = $this->getMockBuilder(\Magento\Framework\Mail\Template\TransportBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'addTo',
                    'setFrom',
                    'setTemplateIdentifier',
                    'setTemplateVars',
                    'setTemplateOptions',
                    'getTransport'
                ]
            )
            ->getMock();
        $transportBuilderMock->method('setTemplateIdentifier')
            ->with($templateId)
            ->willReturnSelf();
        $transportBuilderMock->method('setTemplateOptions')
            ->with(['area' => $area, 'store' => $storeId])
            ->willReturnSelf();
        $transportBuilderMock->method('setTemplateVars')
            ->willReturnSelf();
        $transportBuilderMock->method('setFrom')
            ->with($sender)
            ->willReturnSelf();
        $transportBuilderMock->method('addTo')
            ->with($this->logicalOr($customer->getEmail(), $newEmail), $name)
            ->willReturnSelf();
        $transportBuilderMock->expects($this->exactly($occurrenceNumber))
            ->method('getTransport')
            ->willReturn($transportMock);

        return $transportBuilderMock;
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $transportBuilderMock
     * @param string $className
     */
    protected function addEmailMockToClass(
        \PHPUnit_Framework_MockObject_MockObject $transportBuilderMock,
        $className
    ) {
        $mocked = $this->_objectManager->create(
            $className,
            ['transportBuilder' => $transportBuilderMock]
        );
        $this->_objectManager->addSharedInstance(
            $mocked,
            $className
        );
    }
}
