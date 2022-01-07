<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Controller\Adminhtml\Index;

use Magento\Backend\Model\Session;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\CustomerNameGenerationInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Data\Customer as CustomerData;
use Magento\Customer\Model\EmailNotification;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Mail\TransportInterface;
use Magento\Framework\Message\MessageInterface;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractBackendController;
use PHPUnit\Framework\MockObject\MockObject;

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
    private $baseControllerUrl = 'backend/customer/index/';

    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /**@var CustomerNameGenerationInterface */
    private $customerViewHelper;

    /** @var SubscriberFactory */
    private $subscriberFactory;

    /** @var Session */
    private $session;

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var ResolverInterface */
    private $localeResolver;

    /** @var CustomerInterface */
    private $customer;

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
        $this->storeManager = $this->_objectManager->get(StoreManagerInterface::class);
        $this->localeResolver = $this->_objectManager->get(ResolverInterface::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        if ($this->customer instanceof CustomerInterface) {
            $this->customerRepository->delete($this->customer);
        }

        parent::tearDown();
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
        $this->assertRedirect($this->stringContains($this->baseControllerUrl . 'index/key/'));
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
        $this->assertNotEmpty($customerFormData);
        unset($customerFormData['form_key']);
        $this->assertEquals($expectedData, $customerFormData);
        $this->assertRedirect($this->stringContains($this->baseControllerUrl . 'new/key/'));
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
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @return void
     */
    public function testUpdateCustomer(): void
    {
        /** @var CustomerData $customerData */
        $customerData = $this->customerRepository->getById(1);
        $secondStore = $this->storeManager->getStore('fixturestore');
        $postData = $expectedData = [
            'customer' => [
                CustomerData::FIRSTNAME => 'Jane',
                CustomerData::MIDDLENAME => 'Mdl',
                CustomerData::LASTNAME => 'Doe',
            ],
            'subscription_status' => [$customerData->getWebsiteId() => '1'],
            'subscription_store' => [$customerData->getWebsiteId() => $secondStore->getId()],
        ];
        $postData['customer']['entity_id'] = $customerData->getId();
        $params = ['back' => true];

        $this->dispatchCustomerSave($postData, $params);
        $this->assertSessionMessages(
            $this->equalTo([(string)__('You saved the customer.')]),
            MessageInterface::TYPE_SUCCESS
        );
        $this->assertRedirect($this->stringContains(
            $this->baseControllerUrl . 'edit/id/' . $customerData->getId()
        ));
        $this->assertCustomerData($customerData->getEmail(), (int)$customerData->getWebsiteId(), $expectedData);
        $this->assertCustomerSubscription(
            (int)$customerData->getId(),
            (int)$customerData->getWebsiteId(),
            Subscriber::STATUS_SUBSCRIBED,
            (int)$secondStore->getId()
        );
    }

    /**
     * @magentoDataFixture Magento/Newsletter/_files/subscribers.php
     * @return void
     */
    public function testExistingCustomerUnsubscribeNewsletter(): void
    {
        /** @var CustomerData $customerData */
        $customerData = $this->customerRepository->getById(1);
        /** @var Store $defaultStore */
        $defaultStore = $this->storeManager->getWebsite()->getDefaultStore();
        $postData = [
            'customer' => [
                'entity_id' => $customerData->getId(),
                CustomerData::EMAIL => 'customer@example.com',
                CustomerData::FIRSTNAME => 'test firstname',
                CustomerData::LASTNAME => 'test lastname',
                'sendemail_store_id' => '1'
            ],
            'subscription_status' => [$customerData->getWebsiteId() => '0'],
            'subscription_store' => [$customerData->getWebsiteId() => $defaultStore->getId()],
        ];
        $this->dispatchCustomerSave($postData);
        $this->assertSessionMessages(
            $this->equalTo([(string)__('You saved the customer.')]),
            MessageInterface::TYPE_SUCCESS
        );
        $this->assertRedirect($this->stringContains($this->baseControllerUrl . 'index/key/'));
        $this->assertCustomerSubscription(
            (int)$customerData->getId(),
            (int)$customerData->getWebsiteId(),
            Subscriber::STATUS_UNSUBSCRIBED,
            (int)$defaultStore->getId()
        );
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
        $this->assertRedirect($this->stringContains($this->baseControllerUrl . 'index/key/'));
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
        $this->assertRedirect($this->stringContains($this->baseControllerUrl . 'new/key/'));
    }

    /**
     * @return void
     */
    public function testCreateCustomerByAdminWithLocaleGB(): void
    {
        $this->localeResolver->setLocale('en_GB');
        $postData = array_replace_recursive(
            $this->getDefaultCustomerData(),
            [
                'customer' => [
                    CustomerData::DOB => '24/10/1990',
                ],
            ]
        );
        $expectedData = array_replace_recursive(
            $postData,
            [
                'customer' => [
                    CustomerData::DOB => '1990-10-24',
                ],
            ]
        );
        unset($expectedData['customer']['sendemail_store_id']);
        $this->dispatchCustomerSave($postData);
        $this->assertSessionMessages(
            $this->containsEqual((string)__('You saved the customer.')),
            MessageInterface::TYPE_SUCCESS
        );
        $this->assertRedirect($this->stringContains($this->baseControllerUrl . 'index/key/'));
        $this->assertCustomerData(
            $postData['customer'][CustomerData::EMAIL],
            (int)$postData['customer'][CustomerData::WEBSITE_ID],
            $expectedData
        );
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
                CustomerData::GENDER => Bootstrap::getObjectManager()->get(AttributeRepositoryInterface::class)
                    ->get(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER, 'gender')->getSource()->getOptionId('Male'),
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
        $this->dispatch($this->baseControllerUrl . 'save');
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
        $this->customer = $this->customerRepository->get($customerEmail, $customerWebsiteId);
        $actualCustomerArray = $this->customer->__toArray();
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
     * @param int $websiteId
     * @param int $expectedStatus
     * @param int $expectedStoreId
     * @return void
     */
    private function assertCustomerSubscription(
        int $customerId,
        int $websiteId,
        int $expectedStatus,
        int $expectedStoreId
    ): void {
        $subscriber = $this->subscriberFactory->create();
        $subscriber->loadByCustomer($customerId, $websiteId);
        $this->assertNotEmpty($subscriber->getId());
        $this->assertEquals($expectedStatus, $subscriber->getStatus());
        $this->assertEquals($expectedStoreId, $subscriber->getStoreId());
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
     * @return MockObject
     */
    private function prepareEmailMock(
        int $occurrenceNumber,
        string $templateId,
        array $sender,
        int $customerId,
        $newEmail = null
    ) : MockObject {
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
     * @param MockObject $transportBuilderMock
     * @param string $className
     * @return void
     */
    private function addEmailMockToClass(
        MockObject $transportBuilderMock,
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
