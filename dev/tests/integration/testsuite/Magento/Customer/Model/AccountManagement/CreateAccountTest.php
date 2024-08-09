<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\AccountManagement;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\EmailNotification;
use Magento\Email\Model\ResourceModel\Template\CollectionFactory as TemplateCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Framework\Math\Random;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Validator\Exception;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Xpath;
use Magento\TestFramework\Mail\Template\TransportBuilderMock;
use PHPUnit\Framework\TestCase;

/**
 * Tests for customer creation via customer account management service.
 *
 * @magentoDbIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreateAccountTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var AccountManagementInterface
     */
    private $accountManagement;

    /**
     * @var CustomerInterfaceFactory
     */
    private $customerFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var array
     */
    private $defaultCustomerData = [
        'email' => 'customer@example.com',
        'firstname' => 'First name',
        'lastname' => 'Last name',
    ];

    /**
     * @var TransportBuilderMock
     */
    private $transportBuilderMock;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var ExtensibleDataObjectConverter
     */
    private $extensibleDataObjectConverter;

    /**
     * @var CustomerFactory
     */
    private $customerModelFactory;

    /**
     * @var Random
     */
    private $random;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var MutableScopeConfigInterface
     */
    private $mutableScopeConfig;

    /**
     * @var TemplateCollectionFactory
     */
    private $templateCollectionFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->accountManagement = $this->objectManager->get(AccountManagementInterface::class);
        $this->customerFactory = $this->objectManager->get(CustomerInterfaceFactory::class);
        $this->dataObjectHelper = $this->objectManager->create(DataObjectHelper::class);
        $this->transportBuilderMock = $this->objectManager->get(TransportBuilderMock::class);
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $this->customerRepository = $this->objectManager->get(CustomerRepositoryInterface::class);
        $this->extensibleDataObjectConverter = $this->objectManager->get(ExtensibleDataObjectConverter::class);
        $this->customerModelFactory = $this->objectManager->get(CustomerFactory::class);
        $this->random = $this->objectManager->get(Random::class);
        $this->encryptor = $this->objectManager->get(EncryptorInterface::class);
        $this->mutableScopeConfig = $this->objectManager->get(MutableScopeConfigInterface::class);
        $this->templateCollectionFactory = $this->objectManager->get(TemplateCollectionFactory::class);
        parent::setUp();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->mutableScopeConfig->clean();
    }

    /**
     * @dataProvider createInvalidAccountDataProvider
     * @param array $customerData
     * @param string $password
     * @param string $errorType
     * @param string $errorMessage
     * @return void
     */
    public function testCreateAccountWithInvalidFields(
        array $customerData,
        string $password,
        string $errorType,
        array $errorMessage
    ): void {
        $customerEntity = $this->populateCustomerEntity($this->defaultCustomerData, $customerData);
        $this->expectException($errorType);
        $this->expectExceptionMessage((string)__(...$errorMessage));
        $this->accountManagement->createAccount($customerEntity, $password);
    }

    /**
     * @return array
     */
    public function createInvalidAccountDataProvider(): array
    {
        return [
            'empty_firstname' => [
                'customer_data' => ['firstname' => ''],
                'password' => '_aPassword1',
                'error_type' =>  Exception::class,
                'error_message' => ['"%1" is a required value.', 'First Name'],
            ],
            'empty_lastname' => [
                'customer_data' => ['lastname' => ''],
                'password' => '_aPassword1',
                'error_type' =>  Exception::class,
                'error_message' => ['"%1" is a required value.', 'Last Name'],
            ],
            'empty_email' => [
                'customer_data' => ['email' => ''],
                'password' => '_aPassword1',
                'error_type' => Exception::class,
                'error_message' => ['The customer email is missing. Enter and try again.'],
            ],
            'invalid_email' => [
                'customer_data' => ['email' => 'zxczxczxc'],
                'password' => '_aPassword1',
                'error_type' => Exception::class,
                'error_message' => ['"%1" is not a valid email address.', 'Email'],
            ],
            'empty_password' => [
                'customer_data' => [],
                'password' => '',
                'error_type' => InputException::class,
                'error_message' => ['The password needs at least 8 characters. Create a new password and try again.'],
            ],
            'invalid_password_minimum_length' => [
                'customer_data' => [],
                'password' => 'test',
                'error_type' => InputException::class,
                'error_message' => ['The password needs at least 8 characters. Create a new password and try again.'],
            ],
            'invalid_password_maximum_length' => [
                'customer_data' => [],
                'password' => $this->getRandomNumericString(257),
                'error_type' => InputException::class,
                'error_message' => ['Please enter a password with at most 256 characters.'],
            ],
            'invalid_password_without_minimum_characters_classes' => [
                'customer_data' => [],
                'password' => 'test_password',
                'error_type' => InputException::class,
                'error_message' => [
                    'Minimum of different classes of characters in password is %1.'
                    . ' Classes of characters: Lower Case, Upper Case, Digits, Special Characters.',
                    3,
                ],
            ],
            'password_same_as_email' => [
                'customer_data' => ['email' => 'test1@test.com'],
                'password' => 'test1@test.com',
                'error_type' => LocalizedException::class,
                'error_message' => [
                    'The password can\'t be the same as the email address. Create a new password and try again.',
                ],
            ],
            'send_email_store_id_not_match_website' => [
                'customer_data' => [
                    CustomerInterface::WEBSITE_ID => 1,
                    CustomerInterface::STORE_ID => 5,
                ],
                'password' => '_aPassword1',
                'error_type' => LocalizedException::class,
                'error_message' => [
                    'The store view is not in the associated website.',
                ],
            ],
        ];
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/customer_welcome_email_template.php
     * @return void
     */
    public function testCreateAccountWithConfiguredWelcomeEmail(): void
    {
        $emailTemplate = $this->getCustomTemplateId('customer_create_account_email_template');
        $this->setConfig([EmailNotification::XML_PATH_REGISTER_EMAIL_TEMPLATE => $emailTemplate,]);
        $this->accountManagement->createAccount(
            $this->populateCustomerEntity($this->defaultCustomerData),
            '_Password1'
        );
        $this->assertEmailData(
            [
                'name' => 'Owner',
                'email' => 'owner@example.com',
                'message' => 'Customer create account email template',
            ]
        );
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/customer_welcome_no_password_email_template.php
     * @magentoConfigFixture current_store customer/create_account/email_identity support
     * @return void
     */
    public function testCreateAccountWithConfiguredWelcomeNoPasswordEmail(): void
    {
        $emailTemplate = $this->getCustomTemplateId('customer_create_account_email_no_password_template');
        $this->setConfig([EmailNotification::XML_PATH_REGISTER_NO_PASSWORD_EMAIL_TEMPLATE => $emailTemplate,]);
        $this->accountManagement->createAccount($this->populateCustomerEntity($this->defaultCustomerData));
        $this->assertEmailData(
            [
                'name' => 'CustomerSupport',
                'email' => 'support@example.com',
                'message' => 'Customer create account email no password template',
            ]
        );
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/customer_confirmation_email_template.php
     * @magentoConfigFixture current_website customer/create_account/confirm 1
     * @magentoConfigFixture current_store customer/create_account/email_identity custom1
     * @return void
     */
    public function testCreateAccountWithConfiguredConfirmationEmail(): void
    {
        $emailTemplate = $this->getCustomTemplateId('customer_create_account_email_confirmation_template');
        $this->setConfig([EmailNotification::XML_PATH_CONFIRM_EMAIL_TEMPLATE => $emailTemplate,]);
        $this->accountManagement->createAccount(
            $this->populateCustomerEntity($this->defaultCustomerData),
            '_Password1'
        );
        $this->assertEmailData(
            [
                'name' => 'Custom 1',
                'email' => 'custom1@example.com',
                'message' => 'Customer create account email confirmation template',
            ]
        );
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/customer_confirmed_email_template.php
     * @magentoConfigFixture current_store customer/create_account/email_identity custom1
     * @magentoConfigFixture current_website customer/create_account/confirm 1
     * @return void
     */
    public function testCreateAccountWithConfiguredConfirmedEmail(): void
    {
        $emailTemplate = $this->getCustomTemplateId('customer_create_account_email_confirmed_template');
        $this->setConfig([EmailNotification::XML_PATH_CONFIRMED_EMAIL_TEMPLATE => $emailTemplate,]);
        $this->accountManagement->createAccount(
            $this->populateCustomerEntity($this->defaultCustomerData),
            '_Password1'
        );
        $customer = $this->customerRepository->get('customer@example.com');
        $this->accountManagement->activate($customer->getEmail(), $customer->getConfirmation());
        $this->assertEmailData(
            [
                'name' => 'Custom 1',
                'email' => 'custom1@example.com',
                'message' => 'Customer create account email confirmed template',
            ]
        );
    }

    /**
     * Assert that when you create customer account via admin, link with "set password" is send to customer email.
     *
     * @return void
     */
    public function testSendEmailWithSetPasswordLink(): void
    {
        $customerEntity = $this->populateCustomerEntity($this->defaultCustomerData);
        $newCustomerEntity = $this->accountManagement->createAccount($customerEntity);
        $mailTemplate = $this->transportBuilderMock->getSentMessage()->getBody()->getParts()[0]->getRawContent();

        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                sprintf("//a[contains(@href, 'customer/account/createPassword/?id=%s')]", $newCustomerEntity->getId()),
                $mailTemplate
            ),
            'Password creation link was not found.'
        );
    }

    /**
     * @magentoDataFixture Magento/Store/_files/second_website_with_two_stores.php
     * @return void
     */
    public function testCreateCustomerOnSecondWebsite(): void
    {
        $customerData = [
            CustomerInterface::WEBSITE_ID => $this->storeManager->getWebsite('test')->getId(),
            CustomerInterface::STORE_ID => $this->storeManager->getStore('fixture_third_store')->getId(),
        ];
        $expectedCustomerData = array_merge($this->defaultCustomerData, $customerData);
        $customerEntity = $this->populateCustomerEntity($this->defaultCustomerData, $customerData);
        $savedCustomerEntity = $this->accountManagement->createAccount($customerEntity);

        $this->assertNotNull($savedCustomerEntity->getId());
        $this->assertCustomerData($savedCustomerEntity, $expectedCustomerData);
    }

    /**
     * @return void
     */
    public function testCreateNewCustomerWithPasswordHash(): void
    {
        $customerData = $expectedCustomerData = [
            CustomerInterface::EMAIL => 'email@example.com',
            CustomerInterface::STORE_ID => 1,
            CustomerInterface::FIRSTNAME => 'Tester',
            CustomerInterface::LASTNAME => 'McTest',
            CustomerInterface::GROUP_ID => 1,
        ];
        $newCustomerEntity = $this->populateCustomerEntity($customerData);
        $password = $this->random->getRandomString(8);
        $passwordHash = $this->encryptor->getHash($password, true);
        $savedCustomer = $this->accountManagement->createAccountWithPasswordHash(
            $newCustomerEntity,
            $passwordHash
        );
        $this->assertNotNull($savedCustomer->getId());
        $this->assertCustomerData($savedCustomer, $expectedCustomerData);
        $this->assertEmpty($savedCustomer->getSuffix());
        $this->assertEquals(
            $savedCustomer->getId(),
            $this->accountManagement->authenticate($customerData[CustomerInterface::EMAIL], $password)->getId()
        );
    }

    /**
     * Customer has two addresses one of it is allowed in website and second is not
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_two_addresses.php
     * @magentoDataFixture Magento/Store/_files/websites_different_countries.php
     * @magentoConfigFixture fixture_second_store_store general/country/allow UA
     * @return void
     */
    public function testCreateNewCustomerWithPasswordHashWithNotAllowedCountry(): void
    {
        $customerId = 1;
        $allowedCountryIdForSecondWebsite = 'UA';
        $store = $this->storeManager->getStore('fixture_second_store');
        $customerData = $this->customerRepository->getById($customerId);
        $customerData->getAddresses()[1]->setRegion(null)->setCountryId($allowedCountryIdForSecondWebsite)
            ->setRegionId(null);
        $customerData->getAddresses()[1]->setIsDefaultBilling(true);
        $customerData->getAddresses()[1]->setIsDefaultShipping(true);
        $customerData->setStoreId($store->getId())->setWebsiteId($store->getWebsiteId())->setId(null);
        $password = $this->random->getRandomString(8);
        $passwordHash = $this->encryptor->getHash($password, true);
        $savedCustomer = $this->accountManagement->createAccountWithPasswordHash(
            $customerData,
            $passwordHash
        );
        $this->assertCount(
            1,
            $savedCustomer->getAddresses(),
            'The wrong address quantity was saved'
        );
        $this->assertSame(
            'UA',
            $savedCustomer->getAddresses()[0]->getCountryId(),
            'The address with the disallowed country was saved'
        );
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @return void
     */
    public function testCreateNoExistingCustomer(): void
    {
        $existingCustId = 1;
        $existingCustomer = $this->customerRepository->getById($existingCustId);
        $customerData = $expectedCustomerData = [
            CustomerInterface::EMAIL => 'savecustomer@example.com',
            CustomerInterface::FIRSTNAME => 'Firstsave',
            CustomerInterface::LASTNAME => 'Lastsave',
            CustomerInterface::ID => null,
        ];
        unset($expectedCustomerData[CustomerInterface::ID]);
        $customerEntity = $this->populateCustomerEntity($existingCustomer->__toArray(), $customerData);

        $customerAfter = $this->accountManagement->createAccount($customerEntity, '_aPassword1');
        $this->assertGreaterThan(0, $customerAfter->getId());
        $this->assertCustomerData($customerAfter, $expectedCustomerData);
        $this->accountManagement->authenticate(
            $customerAfter->getEmail(),
            '_aPassword1'
        );
        $attributesBefore = $this->extensibleDataObjectConverter->toFlatArray(
            $existingCustomer,
            [],
            CustomerInterface::class
        );
        $attributesAfter = $this->extensibleDataObjectConverter->toFlatArray(
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
            'email',
            'firstname',
            'id',
            'lastname',
        ];
        sort($expectedInBefore);
        $actualInBeforeOnly = array_keys($inBeforeOnly);
        sort($actualInBeforeOnly);
        $this->assertEquals($expectedInBefore, $actualInBeforeOnly);
        $expectedInAfter = [
            'created_in',
            'email',
            'firstname',
            'id',
            'lastname',
        ];
        $actualInAfterOnly = array_keys($inAfterOnly);
        foreach ($expectedInAfter as $item) {
            $this->assertContains($item, $actualInAfterOnly);
        }
    }

    /**
     * @return void
     */
    public function testCreateCustomerInServiceVsInModel(): void
    {
        $password = '_aPassword1';
        $firstCustomerData = $secondCustomerData = [
            CustomerInterface::EMAIL => 'email@example.com',
            CustomerInterface::FIRSTNAME => 'Tester',
            CustomerInterface::LASTNAME => 'McTest',
            CustomerInterface::GROUP_ID => 1,
        ];
        $secondCustomerData[CustomerInterface::EMAIL] = 'email2@example.com';

        /** @var Customer $customerModel */
        $customerModel = $this->customerModelFactory->create();
        $customerModel->setData($firstCustomerData)->setPassword($password);
        $customerModel->save();
        /** @var Customer $customerModel */
        $savedModel = $this->customerModelFactory->create()->load($customerModel->getId());
        $dataInModel = $savedModel->getData();
        $newCustomerEntity = $this->populateCustomerEntity($secondCustomerData);

        $customerData = $this->accountManagement->createAccount($newCustomerEntity, $password);
        $this->assertNotNull($customerData->getId());
        $savedCustomer = $this->customerRepository->getById($customerData->getId());

        /** @var SimpleDataObjectConverter $simpleDataObjectConverter */
        $simpleDataObjectConverter = $this->objectManager->get(SimpleDataObjectConverter::class);

        $dataInService = $simpleDataObjectConverter->toFlatArray(
            $savedCustomer,
            CustomerInterface::class
        );
        $expectedDifferences = [
            'created_at',
            'updated_at',
            'email',
            'is_active',
            'entity_id',
            'entity_type_id',
            'password_hash',
            'attribute_set_id',
            'disable_auto_group_change',
            'confirmation',
            'reward_update_notification',
            'reward_warning_notification',
        ];
        foreach ($dataInModel as $key => $value) {
            if (!in_array($key, $expectedDifferences)) {
                if ($value === null) {
                    $this->assertArrayNotHasKey($key, $dataInService);
                } elseif (isset($dataInService[$key])) {
                    $this->assertEquals($value, $dataInService[$key], 'Failed asserting value for ' . $key);
                }
            }
        }
        $this->assertEquals($secondCustomerData[CustomerInterface::EMAIL], $dataInService['email']);
        $this->assertArrayNotHasKey('is_active', $dataInService);
        $this->assertArrayNotHasKey('password_hash', $dataInService);
    }

    /**
     * @return void
     */
    public function testCreateNewCustomer(): void
    {
        $customerData = $expectedCustomerData = [
            CustomerInterface::EMAIL => 'email@example.com',
            CustomerInterface::STORE_ID => 1,
            CustomerInterface::FIRSTNAME => 'Tester',
            CustomerInterface::LASTNAME => 'McTest',
            CustomerInterface::GROUP_ID => 1,
        ];
        $newCustomerEntity = $this->populateCustomerEntity($customerData);

        $savedCustomer = $this->accountManagement->createAccount($newCustomerEntity, '_aPassword1');
        $this->assertNotNull($savedCustomer->getId());
        $this->assertCustomerData($savedCustomer, $expectedCustomerData);
        $this->assertEmpty($savedCustomer->getSuffix());
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @return void
     */
    public function testCreateNewCustomerFromClone(): void
    {
        $existingCustId = 1;
        $existingCustomer = $this->customerRepository->getById($existingCustId);
        $customerEntity = $this->customerFactory->create();
        $this->dataObjectHelper->mergeDataObjects(
            CustomerInterface::class,
            $customerEntity,
            $existingCustomer
        );
        $customerData = $expectedCustomerData = [
            CustomerInterface::EMAIL => 'savecustomer@example.com',
            CustomerInterface::FIRSTNAME => 'Firstsave',
            CustomerInterface::LASTNAME => 'Lastsave',
            CustomerInterface::ID => null,
        ];
        unset($expectedCustomerData[CustomerInterface::ID]);
        $customerEntity = $this->populateCustomerEntity($customerData, [], $customerEntity);

        $customer = $this->accountManagement->createAccount($customerEntity, '_aPassword1');
        $this->assertNotEmpty($customer->getId());
        $this->assertCustomerData($customer, $expectedCustomerData);
        $this->accountManagement->authenticate(
            $customer->getEmail(),
            '_aPassword1'
        );
    }

    /**
     * Test for create customer account for second website (with existing email for default website)
     * with global account scope config.
     *
     * @magentoConfigFixture current_store customer/account_share/scope 0
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Store/_files/second_website_with_two_stores.php
     *
     * @return void
     */
    public function testCreateAccountInGlobalScope(): void
    {
        $customerEntity = $this->customerFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $customerEntity,
            $this->defaultCustomerData,
            CustomerInterface::class
        );
        $storeId = $this->storeManager->getStore('fixture_second_store')->getStoreId();
        $customerEntity->setStoreId($storeId);
        $message = 'A customer with the same email address already exists in an associated website.';
        $this->expectExceptionObject(new InputMismatchException(__($message)));
        $this->accountManagement->createAccount($customerEntity, '_aPassword1');
    }

    /**
     * Returns random numeric string with given length.
     *
     * @param int $length
     * @return string
     */
    private function getRandomNumericString(int $length): string
    {
        $string = '';
        for ($i = 0; $i <= $length; $i++) {
            $string .= Random::getRandomNumber(0, 9);
        }

        return $string;
    }

    /**
     * Fill in customer entity using array of customer data and additional customer data.
     *
     * @param array $customerData
     * @param array $additionalCustomerData
     * @param CustomerInterface|null $customerEntity
     * @return CustomerInterface
     */
    private function populateCustomerEntity(
        array $customerData,
        array $additionalCustomerData = [],
        ?CustomerInterface $customerEntity = null
    ): CustomerInterface {
        $customerEntity = $customerEntity ?? $this->customerFactory->create();
        $customerData = array_merge(
            $customerData,
            $additionalCustomerData
        );
        $this->dataObjectHelper->populateWithArray(
            $customerEntity,
            $customerData,
            CustomerInterface::class
        );

        return $customerEntity;
    }

    /**
     * Check that customer parameters match expected values.
     *
     * @param CustomerInterface $customer
     * @param array $expectedData
     * return void
     */
    private function assertCustomerData(
        CustomerInterface $customer,
        array $expectedData
    ): void {
        $actualCustomerArray = $customer->__toArray();
        foreach ($expectedData as $key => $expectedValue) {
            $this->assertEquals(
                $expectedValue,
                $actualCustomerArray[$key],
                "Invalid expected value for $key field."
            );
        }
    }

    /**
     * Sets config data.
     *
     * @param array $configs
     * @return void
     */
    private function setConfig(array $configs): void
    {
        foreach ($configs as $path => $value) {
            $this->mutableScopeConfig->setValue($path, $value, ScopeInterface::SCOPE_STORE, 'default');
        }
    }

    /**
     * Assert email data.
     *
     * @param array $expectedData
     * @return void
     */
    private function assertEmailData(array $expectedData): void
    {
        $message = $this->transportBuilderMock->getSentMessage();
        $this->assertNotNull($message);
        $messageFrom = $message->getFrom();
        $this->assertNotNull($messageFrom);
        $messageFrom = reset($messageFrom);
        $this->assertEquals($expectedData['name'], $messageFrom->getName());
        $this->assertEquals($expectedData['email'], $messageFrom->getEmail());
        $this->assertStringContainsString(
            $expectedData['message'],
            $message->getBody()->getParts()[0]->getRawContent(),
            'Expected message wasn\'t found in email content.'
        );
    }

    /**
     * Returns email template id by template code.
     *
     * @param string $templateCode
     * @return int
     */
    private function getCustomTemplateId(string $templateCode): int
    {
        return (int)$this->templateCollectionFactory->create()
            ->addFieldToFilter('template_code', $templateCode)
            ->getFirstItem()
            ->getId();
    }
}
