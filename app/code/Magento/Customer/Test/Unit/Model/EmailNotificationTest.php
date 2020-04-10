<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\EmailNotification;
use Magento\Framework\App\Area;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Store\Model\Store;
use Magento\Customer\Model\Data\CustomerSecure;
use Magento\Store\Model\Website;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit test for \Magento\Customer\Model\EmailNotification
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EmailNotificationTest extends TestCase
{
    /**
     * @var int
     */
    private const STUB_CUSTOMER_ID = 1;

    /**
     * @var int
     */
    private const STUB_CUSTOMER_STORE_ID = 2;

    /**
     * @var int
     */
    private const STUB_CUSTOMER_WEBSITE_ID = 1;

    /**
     * @var string
     */
    private const STUB_CUSTOMER_EMAIL = 'email@email.com';

    /**
     * @var string
     */
    private const STUB_CUSTOMER_NAME = 'Customer Name';

    /**
     * @var string
     */
    private const STUB_EMAIL_IDENTIFIER = 'Template Identifier';

    /**
     * @var string
     */
    private const STUB_SENDER = 'Sender';

    /**
     * @var \Magento\Customer\Model\CustomerRegistry|MockObject
     */
    private $customerRegistryMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder|MockObject
     */
    private $transportBuilderMock;

    /**
     * @var \Magento\Customer\Helper\View|MockObject
     */
    private $customerViewHelperMock;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor|MockObject
     */
    private $dataProcessorMock;

    /**
     * @var CustomerSecure|MockObject
     */
    private $customerSecureMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var \Magento\Store\Model\Store|MockObject
     */
    private $storeMock;

    /**
     * @var EmailNotification
     */
    private $model;

    /**
     * @var SenderResolverInterface|MockObject
     */
    private $senderResolverMock;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        $this->customerRegistryMock = $this->createMock(\Magento\Customer\Model\CustomerRegistry::class);

        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);

        $this->transportBuilderMock = $this->createMock(\Magento\Framework\Mail\Template\TransportBuilder::class);

        $this->customerViewHelperMock = $this->createMock(\Magento\Customer\Helper\View::class);

        $this->dataProcessorMock = $this->createMock(\Magento\Framework\Reflection\DataObjectProcessor::class);

        $contextMock = $this->createPartialMock(\Magento\Framework\App\Helper\Context::class, ['getScopeConfig']);

        $this->scopeConfigMock = $this->createPartialMock(
            \Magento\Framework\App\Config\ScopeConfigInterface::class,
            ['getValue', 'isSetFlag']
        );

        $contextMock->expects($this->any())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfigMock);

        $this->customerSecureMock = $this->createMock(CustomerSecure::class);

        $this->storeMock = $this->createMock(Store::class);

        $this->senderResolverMock = $this->getMockBuilder(SenderResolverInterface::class)
            ->setMethods(['resolve'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManager = new ObjectManagerHelper($this);

        $this->model = $objectManager->getObject(
            EmailNotification::class,
            [
                'customerRegistry' => $this->customerRegistryMock,
                'storeManager' => $this->storeManagerMock,
                'transportBuilder' => $this->transportBuilderMock,
                'customerViewHelper' => $this->customerViewHelperMock,
                'dataProcessor' => $this->dataProcessorMock,
                'scopeConfig' => $this->scopeConfigMock,
                'senderResolver' => $this->senderResolverMock,
            ]
        );
    }

    /**
     * Test email notify when credentials changed
     *
     * @param int $testNumber
     * @param string $oldEmail
     * @param string $newEmail
     * @param bool $isPasswordChanged
     *
     * @dataProvider sendNotificationEmailsDataProvider
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testEmailNotifyWhenCredentialsChanged($testNumber, $oldEmail, $newEmail, $isPasswordChanged): void
    {
        $customerData = ['key' => 'value'];
        $senderValues = ['name' => self::STUB_SENDER, 'email' => self::STUB_SENDER];

        $expects = $this->once();
        $xmlPathTemplate = EmailNotification::XML_PATH_RESET_PASSWORD_TEMPLATE;
        switch ($testNumber) {
            case 1:
                $xmlPathTemplate = EmailNotification::XML_PATH_RESET_PASSWORD_TEMPLATE;
                $expects = $this->once();
                break;
            case 2:
                $xmlPathTemplate = EmailNotification::XML_PATH_CHANGE_EMAIL_TEMPLATE;
                $expects = $this->exactly(2);
                break;
            case 3:
                $xmlPathTemplate = EmailNotification::XML_PATH_CHANGE_EMAIL_AND_PASSWORD_TEMPLATE;
                $expects = $this->exactly(2);
                break;
        }

        $this->senderResolverMock
            ->expects($expects)
            ->method('resolve')
            ->with(self::STUB_SENDER, self::STUB_CUSTOMER_STORE_ID)
            ->willReturn($senderValues);

        /**
         * @var MockObject $origCustomer
         */
        $origCustomer = $this->createMock(CustomerInterface::class);
        $origCustomer->expects($this->any())
            ->method('getStoreId')
            ->willReturn(0);
        $origCustomer->expects($this->any())
            ->method('getId')
            ->willReturn(self::STUB_CUSTOMER_ID);
        $origCustomer->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn(self::STUB_CUSTOMER_WEBSITE_ID);

        $storeMock = $this->createMock(Store::class);
        $storeMock->expects($this->any())
            ->method('getId')
            ->willReturn(self::STUB_CUSTOMER_STORE_ID);

        $this->storeManagerMock->expects(clone $expects)
            ->method('getStore')
            ->willReturn($storeMock);

        $websiteMock = $this->createPartialMock(Website::class, ['getStoreIds']);
        $websiteMock->expects($this->any())
            ->method('getStoreIds')
            ->willReturn([self::STUB_CUSTOMER_STORE_ID]);

        $this->storeManagerMock->expects(clone $expects)
            ->method('getWebsite')
            ->with(self::STUB_CUSTOMER_WEBSITE_ID)
            ->willReturn($websiteMock);

        $customerSecureMock = $this->createMock(CustomerSecure::class);
        $this->customerRegistryMock->expects(clone $expects)
            ->method('retrieveSecureData')
            ->with(self::STUB_CUSTOMER_ID)
            ->willReturn($customerSecureMock);

        $this->dataProcessorMock->expects(clone $expects)
            ->method('buildOutputDataArray')
            ->with($origCustomer, CustomerInterface::class)
            ->willReturn($customerData);

        $this->customerViewHelperMock->expects($this->any())
            ->method('getCustomerName')
            ->with($origCustomer)
            ->willReturn(self::STUB_CUSTOMER_NAME);

        $customerSecureMock->expects(clone $expects)
            ->method('addData')
            ->with($customerData)
            ->willReturnSelf();
        $customerSecureMock->expects(clone $expects)
            ->method('setData')
            ->with('name', self::STUB_CUSTOMER_NAME)
            ->willReturnSelf();

        /**
         * @var CustomerInterface|MockObject $savedCustomer
         */
        $savedCustomer = clone $origCustomer;

        $origCustomer->expects($this->any())
            ->method('getEmail')
            ->willReturn($oldEmail);

        $savedCustomer->expects($this->any())
            ->method('getEmail')
            ->willReturn($newEmail);

        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->withConsecutive(
                [
                    $xmlPathTemplate,
                    ScopeInterface::SCOPE_STORE,
                    self::STUB_CUSTOMER_STORE_ID
                ],
                [
                    EmailNotification::XML_PATH_FORGOT_EMAIL_IDENTITY,
                    ScopeInterface::SCOPE_STORE,
                    self::STUB_CUSTOMER_STORE_ID
                ],
                [
                    $xmlPathTemplate,
                    ScopeInterface::SCOPE_STORE,
                    self::STUB_CUSTOMER_STORE_ID
                ],
                [
                    EmailNotification::XML_PATH_FORGOT_EMAIL_IDENTITY,
                    ScopeInterface::SCOPE_STORE,
                    self::STUB_CUSTOMER_STORE_ID
                ]
            )->willReturnOnConsecutiveCalls(
                self::STUB_EMAIL_IDENTIFIER,
                self::STUB_SENDER,
                self::STUB_EMAIL_IDENTIFIER,
                self::STUB_SENDER
            );

        $this->transportBuilderMock->expects(clone $expects)
            ->method('setTemplateIdentifier')
            ->with(self::STUB_EMAIL_IDENTIFIER)
            ->willReturnSelf();
        $this->transportBuilderMock->expects(clone $expects)
            ->method('setTemplateOptions')
            ->with(['area' => Area::AREA_FRONTEND, 'store' => self::STUB_CUSTOMER_STORE_ID])
            ->willReturnSelf();
        $this->transportBuilderMock->expects(clone $expects)
            ->method('setTemplateVars')
            ->with(['customer' => $customerSecureMock, 'store' => $storeMock])
            ->willReturnSelf();
        $this->transportBuilderMock->expects(clone $expects)
            ->method('setFrom')
            ->with($senderValues)
            ->willReturnSelf();

        $this->transportBuilderMock->expects(clone $expects)
            ->method('addTo')
            ->withConsecutive([$oldEmail, self::STUB_CUSTOMER_NAME], [$newEmail, self::STUB_CUSTOMER_NAME])
            ->willReturnSelf();

        $transport = $this->createMock(\Magento\Framework\Mail\TransportInterface::class);

        $this->transportBuilderMock->expects(clone $expects)
            ->method('getTransport')
            ->willReturn($transport);

        $transport->expects(clone $expects)
            ->method('sendMessage');

        $this->model->credentialsChanged($savedCustomer, $oldEmail, $isPasswordChanged);
    }

    /**
     * Provides Emails Data Provider
     *
     * @param void
     * @return array
     */
    public function sendNotificationEmailsDataProvider(): array
    {
        return [
            [
                'test_number' => 1,
                'old_email' => 'test@example.com',
                'new_email' => 'test@example.com',
                'is_password_changed' => true
            ],
            [
                'test_number' => 2,
                'old_email' => 'test1@example.com',
                'new_email' => 'test2@example.com',
                'is_password_changed' => false
            ],
            [
                'test_number' => 3,
                'old_email' => 'test1@example.com',
                'new_email' => 'test2@example.com',
                'is_password_changed' => true
            ]
        ];
    }

    /**
     * Test Password Reminder Email Notify
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testPasswordReminder(): void
    {
        $customerData = ['key' => 'value'];
        $senderValues = ['name' => self::STUB_SENDER, 'email' => self::STUB_SENDER];
        $storeIds = [1, 2];

        $this->senderResolverMock
            ->expects($this->once())
            ->method('resolve')
            ->with(self::STUB_SENDER, self::STUB_CUSTOMER_STORE_ID)
            ->willReturn($senderValues);

        /**
         * @var CustomerInterface|MockObject $customer
         */
        $customer = $this->createMock(CustomerInterface::class);
        $customer->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn(self::STUB_CUSTOMER_WEBSITE_ID);
        $customer->expects($this->any())
            ->method('getStoreId')
            ->willReturn(self::STUB_CUSTOMER_STORE_ID);
        $customer->expects($this->any())
            ->method('getId')
            ->willReturn(self::STUB_CUSTOMER_ID);
        $customer->expects($this->any())
            ->method('getEmail')
            ->willReturn(self::STUB_CUSTOMER_EMAIL);

        $this->storeMock->expects($this->any())
            ->method('getId')
            ->willReturn(self::STUB_CUSTOMER_STORE_ID);

        $this->storeManagerMock->expects($this->at(0))
            ->method('getStore')
            ->willReturn($this->storeMock);

        $websiteMock = $this->createPartialMock(Website::class, ['getStoreIds']);
        $websiteMock->expects($this->any())
            ->method('getStoreIds')
            ->willReturn($storeIds);

        $this->storeManagerMock->expects($this->any())
            ->method('getWebsite')
            ->with(self::STUB_CUSTOMER_WEBSITE_ID)
            ->willReturn($websiteMock);

        $this->customerRegistryMock->expects($this->once())
            ->method('retrieveSecureData')
            ->with(self::STUB_CUSTOMER_ID)
            ->willReturn($this->customerSecureMock);

        $this->dataProcessorMock->expects($this->once())
            ->method('buildOutputDataArray')
            ->with($customer, CustomerInterface::class)
            ->willReturn($customerData);

        $this->customerViewHelperMock->expects($this->any())
            ->method('getCustomerName')
            ->with($customer)
            ->willReturn(self::STUB_CUSTOMER_NAME);

        $this->customerSecureMock->expects($this->once())
            ->method('addData')
            ->with($customerData)
            ->willReturnSelf();
        $this->customerSecureMock->expects($this->once())
            ->method('setData')
            ->with('name', self::STUB_CUSTOMER_NAME)
            ->willReturnSelf();

        $this->scopeConfigMock->expects($this->at(0))
            ->method('getValue')
            ->with(
                EmailNotification::XML_PATH_REMIND_EMAIL_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                self::STUB_CUSTOMER_STORE_ID
            )->willReturn(self::STUB_EMAIL_IDENTIFIER);
        $this->scopeConfigMock->expects($this->at(1))
            ->method('getValue')
            ->with(
                EmailNotification::XML_PATH_FORGOT_EMAIL_IDENTITY,
                ScopeInterface::SCOPE_STORE,
                self::STUB_CUSTOMER_STORE_ID
            )->willReturn(self::STUB_SENDER);

        $this->mockDefaultTransportBuilder(
            self::STUB_EMAIL_IDENTIFIER,
            self::STUB_CUSTOMER_STORE_ID,
            $senderValues,
            self::STUB_CUSTOMER_EMAIL,
            self::STUB_CUSTOMER_NAME,
            ['customer' => $this->customerSecureMock, 'store' => $this->storeMock]
        );

        $this->model->passwordReminder($customer);
    }

    /**
     * Test password reminder customer withouer store id info
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testPasswordReminderCustomerWithoutStoreId(): void
    {
        $customerStoreId = null;
        $customerData = ['key' => 'value'];
        $senderValues = ['name' => self::STUB_SENDER, 'email' => self::STUB_SENDER];
        $storeIds = [1, 2];
        $defaultStoreId = reset($storeIds);
        $this->senderResolverMock
            ->expects($this->once())
            ->method('resolve')
            ->with(self::STUB_SENDER, $defaultStoreId)
            ->willReturn($senderValues);
        /**
         * @var CustomerInterface|MockObject $customer
         */
        $customer = $this->createMock(CustomerInterface::class);
        $customer->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn(self::STUB_CUSTOMER_WEBSITE_ID);
        $customer->expects($this->any())
            ->method('getStoreId')
            ->willReturn($customerStoreId);
        $customer->expects($this->any())
            ->method('getId')
            ->willReturn(self::STUB_CUSTOMER_ID);
        $customer->expects($this->any())
            ->method('getEmail')
            ->willReturn(self::STUB_CUSTOMER_EMAIL);
        $this->storeMock->expects($this->any())
            ->method('getId')
            ->willReturn($defaultStoreId);
        $this->storeManagerMock->expects($this->at(0))
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->storeManagerMock->expects($this->at(1))
            ->method('getStore')
            ->with($defaultStoreId)
            ->willReturn($this->storeMock);
        $websiteMock = $this->createPartialMock(Website::class, ['getStoreIds']);
        $websiteMock->expects($this->any())
            ->method('getStoreIds')
            ->willReturn($storeIds);
        $this->storeManagerMock->expects($this->any())
            ->method('getWebsite')
            ->with(self::STUB_CUSTOMER_WEBSITE_ID)
            ->willReturn($websiteMock);

        $this->customerRegistryMock->expects($this->once())
            ->method('retrieveSecureData')
            ->with(self::STUB_CUSTOMER_ID)
            ->willReturn($this->customerSecureMock);
        $this->dataProcessorMock->expects($this->once())
            ->method('buildOutputDataArray')
            ->with($customer, CustomerInterface::class)
            ->willReturn($customerData);
        $this->customerViewHelperMock->expects($this->any())
            ->method('getCustomerName')
            ->with($customer)
            ->willReturn(self::STUB_CUSTOMER_NAME);
        $this->customerSecureMock->expects($this->once())
            ->method('addData')
            ->with($customerData)
            ->willReturnSelf();
        $this->customerSecureMock->expects($this->once())
            ->method('setData')
            ->with('name', self::STUB_CUSTOMER_NAME)
            ->willReturnSelf();
        $this->scopeConfigMock->expects($this->at(0))
            ->method('getValue')
            ->with(
                EmailNotification::XML_PATH_REMIND_EMAIL_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                $defaultStoreId
            )->willReturn(self::STUB_EMAIL_IDENTIFIER);
        $this->scopeConfigMock->expects($this->at(1))
            ->method('getValue')
            ->with(
                EmailNotification::XML_PATH_FORGOT_EMAIL_IDENTITY,
                ScopeInterface::SCOPE_STORE,
                $defaultStoreId
            )->willReturn(self::STUB_SENDER);
        $this->mockDefaultTransportBuilder(
            self::STUB_EMAIL_IDENTIFIER,
            $defaultStoreId,
            $senderValues,
            self::STUB_CUSTOMER_EMAIL,
            self::STUB_CUSTOMER_NAME,
            ['customer' => $this->customerSecureMock, 'store' => $this->storeMock]
        );
        $this->model->passwordReminder($customer);
    }

    /**
     * Test email notify for password reset confirm
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testPasswordResetConfirmation(): void
    {
        $customerData = ['key' => 'value'];
        $senderValues = ['name' => self::STUB_SENDER, 'email' => self::STUB_SENDER];

        $this->senderResolverMock
            ->expects($this->once())
            ->method('resolve')
            ->with(self::STUB_SENDER, self::STUB_CUSTOMER_STORE_ID)
            ->willReturn($senderValues);

        /**
         * @var CustomerInterface|MockObject $customer
         */
        $customer = $this->createMock(CustomerInterface::class);
        $customer->expects($this->once())
            ->method('getStoreId')
            ->willReturn(self::STUB_CUSTOMER_STORE_ID);
        $customer->expects($this->any())
            ->method('getId')
            ->willReturn(self::STUB_CUSTOMER_ID);
        $customer->expects($this->any())
            ->method('getEmail')
            ->willReturn(self::STUB_CUSTOMER_EMAIL);

        $this->storeMock->expects($this->any())
            ->method('getId')
            ->willReturn(self::STUB_CUSTOMER_STORE_ID);

        $this->storeManagerMock->expects($this->at(0))
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->customerRegistryMock->expects($this->once())
            ->method('retrieveSecureData')
            ->with(self::STUB_CUSTOMER_ID)
            ->willReturn($this->customerSecureMock);

        $this->dataProcessorMock->expects($this->once())
            ->method('buildOutputDataArray')
            ->with($customer, CustomerInterface::class)
            ->willReturn($customerData);

        $this->customerViewHelperMock->expects($this->any())
            ->method('getCustomerName')
            ->with($customer)
            ->willReturn(self::STUB_CUSTOMER_NAME);

        $this->customerSecureMock->expects($this->once())
            ->method('addData')
            ->with($customerData)
            ->willReturnSelf();
        $this->customerSecureMock->expects($this->once())
            ->method('setData')
            ->with('name', self::STUB_CUSTOMER_NAME)
            ->willReturnSelf();

        $this->scopeConfigMock->expects($this->at(0))
            ->method('getValue')
            ->with(
                EmailNotification::XML_PATH_FORGOT_EMAIL_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                self::STUB_CUSTOMER_STORE_ID
            )->willReturn(self::STUB_EMAIL_IDENTIFIER);
        $this->scopeConfigMock->expects($this->at(1))
            ->method('getValue')
            ->with(
                EmailNotification::XML_PATH_FORGOT_EMAIL_IDENTITY,
                ScopeInterface::SCOPE_STORE,
                self::STUB_CUSTOMER_STORE_ID
            )->willReturn(self::STUB_SENDER);

        $this->mockDefaultTransportBuilder(
            self::STUB_EMAIL_IDENTIFIER,
            self::STUB_CUSTOMER_STORE_ID,
            $senderValues,
            self::STUB_CUSTOMER_EMAIL,
            self::STUB_CUSTOMER_NAME,
            ['customer' => $this->customerSecureMock, 'store' => $this->storeMock]
        );

        $this->model->passwordResetConfirmation($customer);
    }

    /**
     * Test email notify with new account
     *
     * @param void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testNewAccount(): void
    {
        $customerData = ['key' => 'value'];
        $senderValues = ['name' => self::STUB_SENDER, 'email' => self::STUB_SENDER];

        $this->senderResolverMock
            ->expects($this->once())
            ->method('resolve')
            ->with(self::STUB_SENDER, self::STUB_CUSTOMER_STORE_ID)
            ->willReturn($senderValues);

        /**
         * @var CustomerInterface|MockObject $customer
         */
        $customer = $this->createMock(CustomerInterface::class);
        $customer->expects($this->any())
            ->method('getStoreId')
            ->willReturn(self::STUB_CUSTOMER_STORE_ID);
        $customer->expects($this->any())
            ->method('getId')
            ->willReturn(self::STUB_CUSTOMER_ID);
        $customer->expects($this->any())
            ->method('getEmail')
            ->willReturn(self::STUB_CUSTOMER_EMAIL);

        $this->storeMock->expects($this->any())
            ->method('getId')
            ->willReturn(self::STUB_CUSTOMER_STORE_ID);

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->with(self::STUB_CUSTOMER_STORE_ID)
            ->willReturn($this->storeMock);

        $this->customerRegistryMock->expects($this->once())
            ->method('retrieveSecureData')
            ->with(self::STUB_CUSTOMER_ID)
            ->willReturn($this->customerSecureMock);

        $this->dataProcessorMock->expects($this->once())
            ->method('buildOutputDataArray')
            ->with($customer, CustomerInterface::class)
            ->willReturn($customerData);

        $this->customerViewHelperMock->expects($this->any())
            ->method('getCustomerName')
            ->with($customer)
            ->willReturn(self::STUB_CUSTOMER_NAME);

        $this->customerSecureMock->expects($this->once())
            ->method('addData')
            ->with($customerData)
            ->willReturnSelf();
        $this->customerSecureMock->expects($this->once())
            ->method('setData')
            ->with('name', self::STUB_CUSTOMER_NAME)
            ->willReturnSelf();

        $this->scopeConfigMock->expects($this->at(0))
            ->method('getValue')
            ->with(
                EmailNotification::XML_PATH_REGISTER_EMAIL_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                self::STUB_CUSTOMER_STORE_ID
            )->willReturn(self::STUB_EMAIL_IDENTIFIER);
        $this->scopeConfigMock->expects($this->at(1))
            ->method('getValue')
            ->with(
                EmailNotification::XML_PATH_REGISTER_EMAIL_IDENTITY,
                ScopeInterface::SCOPE_STORE,
                self::STUB_CUSTOMER_STORE_ID
            )->willReturn(self::STUB_SENDER);

        $this->mockDefaultTransportBuilder(
            self::STUB_EMAIL_IDENTIFIER,
            self::STUB_CUSTOMER_STORE_ID,
            $senderValues,
            self::STUB_CUSTOMER_EMAIL,
            self::STUB_CUSTOMER_NAME,
            ['customer' => $this->customerSecureMock, 'back_url' => '', 'store' => $this->storeMock]
        );

        $this->model->newAccount(
            $customer,
            EmailNotification::NEW_ACCOUNT_EMAIL_REGISTERED,
            '',
            self::STUB_CUSTOMER_STORE_ID
        );
    }

    /**
     * Create default mock for $this->transportBuilderMock.
     *
     * @param string $templateIdentifier
     * @param int $customerStoreId
     * @param array $senderValues
     * @param string $customerEmail
     * @param string $customerName
     * @param array $templateVars
     *
     * @return void
     */
    private function mockDefaultTransportBuilder(
        string $templateIdentifier,
        int $customerStoreId,
        array $senderValues,
        string $customerEmail,
        string $customerName,
        array $templateVars = []
    ): void {
        $transport = $this->createMock(\Magento\Framework\Mail\TransportInterface::class);

        $this->transportBuilderMock->expects($this->once())
            ->method('setTemplateIdentifier')
            ->with($templateIdentifier)
            ->willReturnSelf();
        $this->transportBuilderMock->expects($this->once())
            ->method('setTemplateOptions')
            ->with(['area' => Area::AREA_FRONTEND, 'store' => $customerStoreId])
            ->willReturnSelf();
        $this->transportBuilderMock->expects($this->once())
            ->method('setTemplateVars')
            ->with($templateVars)
            ->willReturnSelf();
        $this->transportBuilderMock->expects($this->once())
            ->method('setFrom')
            ->with($senderValues)
            ->willReturnSelf();
        $this->transportBuilderMock->expects($this->once())
            ->method('addTo')
            ->with($customerEmail, $customerName)
            ->willReturnSelf();
        $this->transportBuilderMock->expects($this->once())
            ->method('getTransport')
            ->willReturn($transport);

        $transport->expects($this->once())
            ->method('sendMessage');
    }
}
