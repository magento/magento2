<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Controller\Adminhtml\Index;

use Magento\Customer\Api\CustomerNameGenerationInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Data\Customer as CustomerData;
use Magento\Customer\Model\EmailNotification;
use Magento\Backend\Model\Session;
use Magento\Framework\App\Area;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Mail\TransportInterface;
use Magento\Framework\Message\MessageInterface;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Tests for save customer via backend/customer/index/save controller.
 *
 * @magentoAppArea adminhtml
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveTest extends AbstractBackendController
{
    /**
     * Base controller URL
     *
     * @var string
     */
    private $_baseControllerUrl = 'http://localhost/index.php/backend/customer/index/';

    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /**@var CustomerNameGenerationInterface */
    private $customerViewHelper;

    /** @var SubscriberFactory */
    private $subscriberFactory;

    /** @var Session */
    private $session;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->customerRepository = $this->_objectManager->get(CustomerRepositoryInterface::class);
        $this->customerViewHelper = $this->_objectManager->get(CustomerNameGenerationInterface::class);
        $this->subscriberFactory = $this->_objectManager->get(SubscriberFactory::class);
        $this->session = $this->_objectManager->get(Session::class);
    }

    /**
     * Create customer
     *
     * @dataProvider createCustomerProvider
     * @magentoDbIsolation enabled
     *
     * @param array $postData
     * @param array $expectedData
     * @return void
     */
    public function testCreateCustomer(array $postData, array $expectedData): void
    {
        $this->dispatchCustomerSave($postData);
        $this->assertSessionMessages(
            $this->equalTo([(string)__('You saved the customer.')]),
            MessageInterface::TYPE_SUCCESS
        );
        $this->assertRedirect($this->stringStartsWith($this->_baseControllerUrl . 'index/key/'));
        $this->assertCustomerData(
            $postData['customer'][CustomerData::EMAIL],
            (int)$postData['customer'][CustomerData::WEBSITE_ID],
            $expectedData
        );
    }

    /**
     * Create customer provider
     *
     * @return array
     */
    public function createCustomerProvider(): array
    {
        $defaultCustomerData = $this->getDefaultCustomerData();
        $expectedCustomerData = $this->getExpectedCustomerData($defaultCustomerData);
        return [
            "fill_all_fields" => [
                'post_data' => $defaultCustomerData,
                'expected_data' => $expectedCustomerData
            ],
            'only_require_fields' => [
                'post_data' => array_replace_recursive(
                    $defaultCustomerData,
                    [
                        'customer' => [
                            CustomerData::DISABLE_AUTO_GROUP_CHANGE => '0',
                            CustomerData::PREFIX => '',
                            CustomerData::MIDDLENAME => '',
                            CustomerData::SUFFIX => '',
                            CustomerData::DOB => '',
                            CustomerData::TAXVAT => '',
                            CustomerData::GENDER => '',
                        ],
                    ]
                ),
                'expected_data' => array_replace_recursive(
                    $expectedCustomerData,
                    [
                        'customer' => [
                            CustomerData::DISABLE_AUTO_GROUP_CHANGE => '0',
                            CustomerData::PREFIX => '',
                            CustomerData::MIDDLENAME => '',
                            CustomerData::SUFFIX => '',
                            CustomerData::DOB => '',
                            CustomerData::TAXVAT => '',
                            CustomerData::GENDER => '0',
                        ],
                    ]
                ),
            ],
        ];
    }

    /**
     * Create customer with exceptions
     *
     * @dataProvider createCustomerErrorsProvider
     * @magentoDbIsolation enabled
     *
     * @param array $postData
     * @param array $expectedData
     * @param array $expectedMessage
     * @return void
     */
    public function testCreateCustomerErrors(array $postData, array $expectedData, array $expectedMessage): void
    {
        $this->dispatchCustomerSave($postData);
        $this->assertSessionMessages(
            $this->equalTo($expectedMessage),
            MessageInterface::TYPE_ERROR
        );
        $customerFormData = $this->session->getCustomerFormData();
        unset($customerFormData['form_key']);
        $this->assertEquals($expectedData, $customerFormData);
        $this->assertRedirect($this->stringStartsWith($this->_baseControllerUrl . 'new/key/'));
    }

    /**
     * Create customer errors provider
     *
     * @return array
     */
    public function createCustomerErrorsProvider(): array
    {
        $defaultCustomerData = $this->getDefaultCustomerData();
        return [
            'without_some_require_fields' => [
                'post_data' => array_replace_recursive(
                    $defaultCustomerData,
                    [
                        'customer' => [
                            CustomerData::FIRSTNAME => '',
                            CustomerData::LASTNAME => '',
                        ],
                    ]
                ),
                'expected_data' => array_replace_recursive(
                    $defaultCustomerData,
                    [
                        'customer' => [
                            CustomerData::FIRSTNAME => '',
                            CustomerData::LASTNAME => '',
                            CustomerData::DOB => '2000-01-01',
                        ],
                    ]
                ),
                'expected_message' => [
                    (string)__('"%1" is a required value.', 'First Name'),
                    (string)__('"%1" is a required value.', 'Last Name'),
                ],
            ],
            'with_empty_post_data' => [
                'post_data' => [],
                'expected_data' => [],
                'expected_message' => [
                    (string)__('The customer email is missing. Enter and try again.'),
                ],
            ],
            'with_invalid_form_data' => [
                'post_data' => [
                    'account' => [
                        'middlename' => 'test middlename',
                        'group_id' => 1,
                    ],
                ],
                'expected_data' => [
                    'account' => [
                        'middlename' => 'test middlename',
                        'group_id' => 1,
                    ],
                ],
                'expected_message' => [
                    (string)__('The customer email is missing. Enter and try again.'),
                ],
            ]
        ];
    }

    /**
     * Update customer with exceptions
     *
     * @magentoDbIsolation enabled
     *
     * @return void
     */
    public function testUpdateCustomerErrors(): void
    {
        $postData = [
            'customer' => [
                CustomerData::FIRSTNAME => 'John',
                CustomerData::LASTNAME => 'Doe',
            ],
            'subscription' => '1',
        ];
        $expectedMessages = [(string)__('Something went wrong while saving the customer.')];
        $postData['customer']['entity_id'] = -1;
        $params = ['back' => true];
        $this->dispatchCustomerSave($postData, $params);
        $this->assertSessionMessages(
            $this->equalTo($expectedMessages),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     * Update customer with subscription and redirect to edit page.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @return void
     */
    public function testUpdateCustomer(): void
    {
        $postData = $expectedData = [
            'customer' => [
                CustomerData::FIRSTNAME => 'Jane',
                CustomerData::MIDDLENAME => 'Mdl',
                CustomerData::LASTNAME => 'Doe',
            ],
            'subscription' => '1',
        ];
        /** @var CustomerData $customerData */
        $customerData = $this->customerRepository->getById(1);
        $postData['customer']['entity_id'] = $customerData->getId();
        $params = ['back' => true];

        $this->dispatchCustomerSave($postData, $params);
        $this->assertSessionMessages(
            $this->equalTo([(string)__('You saved the customer.')]),
            MessageInterface::TYPE_SUCCESS
        );
        $this->assertRedirect($this->stringStartsWith(
            $this->_baseControllerUrl . 'edit/id/' . $customerData->getId()
        ));
        $this->assertCustomerData($customerData->getEmail(), (int)$customerData->getWebsiteId(), $expectedData);
        $this->assertCustomerSubscription((int)$customerData->getId(), Subscriber::STATUS_SUBSCRIBED);
    }

    /**
     * @magentoDataFixture Magento/Newsletter/_files/subscribers.php
     * @return void
     */
    public function testExistingCustomerUnsubscribeNewsletter(): void
    {
        $customerId = 1;
        $postData = [
            'customer' => [
                'entity_id' => $customerId,
                CustomerData::EMAIL => 'customer@example.com',
                CustomerData::FIRSTNAME => 'test firstname',
                CustomerData::LASTNAME => 'test lastname',
                'sendemail_store_id' => '1'
            ],
            'subscription' => '0'
        ];
        $this->dispatchCustomerSave($postData);
        $this->assertSessionMessages(
            $this->equalTo([(string)__('You saved the customer.')]),
            MessageInterface::TYPE_SUCCESS
        );
        $this->assertRedirect($this->stringStartsWith($this->_baseControllerUrl . 'index/key/'));
        $this->assertCustomerSubscription($customerId, Subscriber::STATUS_UNSUBSCRIBED);
    }

    /**
     * Ensure that an email is sent during save action
     *
     * @magentoConfigFixture current_store customer/account_information/change_email_template change_email_template
     * @magentoConfigFixture current_store customer/password/forgot_email_identity support
     * @magentoDataFixture Magento/Customer/_files/customer_sample.php
     * @return void
     */
    public function testExistingCustomerChangeEmail(): void
    {
        $customerId = 1;
        $newEmail = 'newcustomer@example.com';
        $transportBuilderMock = $this->prepareEmailMock(
            2,
            'change_email_template',
            [
                'name' => 'CustomerSupport',
                'email' => 'support@example.com',
            ],
            $customerId,
            $newEmail
        );
        $this->addEmailMockToClass($transportBuilderMock, EmailNotification::class);
        $postData = [
            'customer' => [
                'entity_id' => $customerId,
                CustomerData::WEBSITE_ID => '1',
                CustomerData::GROUP_ID => '1',
                CustomerData::FIRSTNAME => 'test firstname',
                CustomerData::MIDDLENAME => 'test middlename',
                CustomerData::LASTNAME => 'test lastname',
                CustomerData::EMAIL => $newEmail,
                'new_password' => 'auto',
                'sendemail_store_id' => '1',
                'sendemail' => '1',
                CustomerData::CREATED_AT => '2000-01-01 00:00:00',
                CustomerData::DEFAULT_SHIPPING => '_item1',
                CustomerData::DEFAULT_BILLING => '1'
            ]
        ];
        $this->dispatchCustomerSave($postData);

        /**
         * Check that no errors were generated and set to session
         */
        $this->assertSessionMessages($this->isEmpty(), MessageInterface::TYPE_ERROR);
        $this->assertRedirect($this->stringStartsWith($this->_baseControllerUrl . 'index/key/'));
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_sample.php
     * @return void
     */
    public function testCreateSameEmailFormatDateError(): void
    {
        $postData = [
            'customer' => [
                CustomerData::WEBSITE_ID => '1',
                CustomerData::FIRSTNAME => 'test firstname',
                CustomerData::MIDDLENAME => 'test middlename',
                CustomerData::LASTNAME => 'test lastname',
                CustomerData::EMAIL => 'customer@example.com',
                CustomerData::DOB => '12/3/1996',
            ],
        ];
        $postFormatted = array_replace_recursive(
            $postData,
            [
                'customer' => [
                    CustomerData::DOB => '1996-12-03',
                ],
            ]
        );
        $this->dispatchCustomerSave($postData);
        $this->assertSessionMessages(
            $this->equalTo([
                (string)__('A customer with the same email address already exists in an associated website.'),
            ]),
            MessageInterface::TYPE_ERROR
        );
        $customerFormData = $this->session->getCustomerFormData();
        $this->assertNotEmpty($customerFormData);
        unset($customerFormData['form_key']);
        $this->assertEquals(
            $postFormatted,
            $customerFormData,
            'Customer form data should be formatted'
        );
        $this->assertRedirect($this->stringStartsWith($this->_baseControllerUrl . 'new/key/'));
    }

    /**
     * Default values for customer creation
     *
     * @return array
     */
    private function getDefaultCustomerData(): array
    {
        return [
            'customer' => [
                CustomerData::WEBSITE_ID => '1',
                CustomerData::GROUP_ID => '1',
                CustomerData::DISABLE_AUTO_GROUP_CHANGE => '1',
                CustomerData::PREFIX => 'Mr.',
                CustomerData::FIRSTNAME => 'Jane',
                CustomerData::MIDDLENAME => 'Mdl',
                CustomerData::LASTNAME => 'Doe',
                CustomerData::SUFFIX => 'Esq.',
                CustomerData::EMAIL => 'janedoe' . uniqid() . '@example.com',
                CustomerData::DOB => '01/01/2000',
                CustomerData::TAXVAT => '121212',
                CustomerData::GENDER => 'Male',
                'sendemail_store_id' => '1',
            ]
        ];
    }

    /**
     * Expected values for customer creation
     *
     * @param array $defaultCustomerData
     * @return array
     */
    private function getExpectedCustomerData(array $defaultCustomerData): array
    {
        unset($defaultCustomerData['customer']['sendemail_store_id']);
        return array_replace_recursive(
            $defaultCustomerData,
            [
                'customer' => [
                    CustomerData::DOB => '2000-01-01',
                    CustomerData::GENDER => '0',
                    CustomerData::STORE_ID => 1,
                    CustomerData::CREATED_IN => 'Default Store View',
                ],
            ]
        );
    }

    /**
     * Create or update customer using backend/customer/index/save action.
     *
     * @param array $postData
     * @param array $params
     * @return void
     */
    private function dispatchCustomerSave(array $postData, array $params = []): void
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($postData);
        if (!empty($params)) {
            $this->getRequest()->setParams($params);
        }
        $this->dispatch('backend/customer/index/save');
    }

    /**
     * Check that customer parameters match expected values.
     *
     * @param string $customerEmail
     * @param int $customerWebsiteId
     * @param array $expectedData
     * @return void
     */
    private function assertCustomerData(
        string $customerEmail,
        int $customerWebsiteId,
        array $expectedData
    ): void {
        /** @var CustomerData $customerData */
        $customerData = $this->customerRepository->get($customerEmail, $customerWebsiteId);
        $actualCustomerArray = $customerData->__toArray();
        foreach ($expectedData['customer'] as $key => $expectedValue) {
            $this->assertEquals(
                $expectedValue,
                $actualCustomerArray[$key],
                "Invalid expected value for $key field."
            );
        }
    }

    /**
     * Check that customer subscription status match expected status.
     *
     * @param int $customerId
     * @param int $expectedStatus
     * @return void
     */
    private function assertCustomerSubscription(int $customerId, int $expectedStatus): void
    {
        $subscriber = $this->subscriberFactory->create();
        $subscriber->loadByCustomerId($customerId);
        $this->assertNotEmpty($subscriber->getId());
        $this->assertEquals($expectedStatus, $subscriber->getStatus());
    }

    /**
     * Prepare email mock to test emails.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @param int $occurrenceNumber
     * @param string $templateId
     * @param array $sender
     * @param int $customerId
     * @param string|null $newEmail
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function prepareEmailMock(
        int $occurrenceNumber,
        string $templateId,
        array $sender,
        int $customerId,
        $newEmail = null
    ) : \PHPUnit\Framework\MockObject\MockObject {
        $area = Area::AREA_FRONTEND;
        $customer = $this->customerRepository->getById($customerId);
        $storeId = $customer->getStoreId();
        $name = $this->customerViewHelper->getCustomerName($customer);

        $transportMock = $this->getMockBuilder(TransportInterface::class)
            ->setMethods(['sendMessage'])
            ->getMockForAbstractClass();
        $transportMock->expects($this->exactly($occurrenceNumber))
            ->method('sendMessage');
        $transportBuilderMock = $this->getMockBuilder(TransportBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'addTo',
                    'setFrom',
                    'setTemplateIdentifier',
                    'setTemplateVars',
                    'setTemplateOptions',
                    'getTransport',
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
     * Add email mock to class
     *
     * @param \PHPUnit\Framework\MockObject\MockObject $transportBuilderMock
     * @param string $className
     * @return void
     */
    private function addEmailMockToClass(
        \PHPUnit\Framework\MockObject\MockObject $transportBuilderMock,
        $className
    ): void {
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
