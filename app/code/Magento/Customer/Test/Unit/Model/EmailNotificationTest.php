<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Helper\View;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Data\CustomerSecure;
use Magento\Customer\Model\EmailNotification;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Mail\TransportInterface;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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
     * @var CustomerRegistry|MockObject
     */
    private $customerRegistryMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var TransportBuilder|MockObject
     */
    private $transportBuilderMock;

    /**
     * @var View|MockObject
     */
    private $customerViewHelperMock;

    /**
     * @var DataObjectProcessor|MockObject
     */
    private $dataProcessorMock;

    /**
     * @var CustomerSecure|MockObject
     */
    private $customerSecureMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var Store|MockObject
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
    protected function setUp(): void
    {
        $this->customerRegistryMock = $this->createMock(CustomerRegistry::class);
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->transportBuilderMock = $this->createMock(TransportBuilder::class);
        $this->customerViewHelperMock = $this->createMock(View::class);
        $this->dataProcessorMock = $this->createMock(DataObjectProcessor::class);

        $contextMock = $this->createPartialMock(Context::class, ['getScopeConfig']);

        $this->scopeConfigMock = $this->createPartialMock(
            ScopeConfigInterface::class,
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
     * @param int $customerStoreId
     * @param string $oldEmail
     * @param string $newEmail
     * @param bool $isPasswordChanged
     * @dataProvider sendNotificationEmailsDataProvider
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testEmailNotifyWhenCredentialsChanged(
        $testNumber,
        $customerStoreId,
        $oldEmail,
        $newEmail,
        $isPasswordChanged
    ):void {
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
            ->with(self::STUB_SENDER, $customerStoreId)
            ->willReturn($senderValues);

        /**
         * @var MockObject $origCustomerMock
         */
        $origCustomerMock = $this->getMockForAbstractClass(CustomerInterface::class);
        $origCustomerMock->expects($this->any())
            ->method('getStoreId')
            ->willReturn($customerStoreId);
        $origCustomerMock->expects($this->any())
            ->method('getId')
            ->willReturn(self::STUB_CUSTOMER_ID);
        $origCustomerMock->expects($this->never())
            ->method('getWebsiteId');

        $storeMock = $this->createMock(Store::class);
        $storeMock->expects($this->any())
            ->method('getId')
            ->willReturn($customerStoreId);

        $this->storeManagerMock->expects(clone $expects)
            ->method('getStore')
            ->willReturn($storeMock);

        $websiteMock = $this->createPartialMock(Website::class, ['getStoreIds']);
        $websiteMock->expects($this->any())
            ->method('getStoreIds')
            ->willReturn([$customerStoreId]);

        $this->storeManagerMock->expects($this->never())
            ->method('getWebsite');

        $customerSecureMock = $this->createMock(CustomerSecure::class);
        $this->customerRegistryMock->expects(clone $expects)
            ->method('retrieveSecureData')
            ->with(self::STUB_CUSTOMER_ID)
            ->willReturn($customerSecureMock);

        $this->dataProcessorMock->expects(clone $expects)
            ->method('buildOutputDataArray')
            ->with($origCustomerMock, CustomerInterface::class)
            ->willReturn($customerData);

        $this->customerViewHelperMock->expects($this->any())
            ->method('getCustomerName')
            ->with($origCustomerMock)
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
        $savedCustomer = clone $origCustomerMock;

        $origCustomerMock->expects($this->any())
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
                    $customerStoreId
                ],
                [
                    EmailNotification::XML_PATH_FORGOT_EMAIL_IDENTITY,
                    ScopeInterface::SCOPE_STORE,
                    $customerStoreId
                ],
                [
                    $xmlPathTemplate,
                    ScopeInterface::SCOPE_STORE,
                    $customerStoreId
                ],
                [
                    EmailNotification::XML_PATH_FORGOT_EMAIL_IDENTITY,
                    ScopeInterface::SCOPE_STORE,
                    $customerStoreId
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
            ->with(['area' => Area::AREA_FRONTEND, 'store' => $customerStoreId])
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

        $transport = $this->getMockForAbstractClass(TransportInterface::class);

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
                'customerStoreId' => 0,
                'old_email' => 'test@example.com',
                'new_email' => 'test@example.com',
                'is_password_changed' => true
            ],
            [
                'test_number' => 1,
                'customerStoreId' => 2,
                'old_email' => 'test@example.com',
                'new_email' => 'test@example.com',
                'is_password_changed' => true
            ],
            [
                'test_number' => 2,
                'customerStoreId' => 0,
                'old_email' => 'test1@example.com',
                'new_email' => 'test2@example.com',
                'is_password_changed' => false
            ],
            [
                'test_number' => 2,
                'customerStoreId' => 2,
                'old_email' => 'test1@example.com',
                'new_email' => 'test2@example.com',
                'is_password_changed' => false
            ],
            [
                'test_number' => 3,
                'customerStoreId' => 0,
                'old_email' => 'test1@example.com',
                'new_email' => 'test2@example.com',
                'is_password_changed' => true
            ],
            [
                'test_number' => 3,
                'customerStoreId' => 2,
                'old_email' => 'test1@example.com',
                'new_email' => 'test2@example.com',
                'is_password_changed' => true
            ]
        ];
    }

    /**
     * Test Password Reminder Email Notify
     *
     * @param int $customerStoreId
     * @dataProvider customerStoreIdDataProvider
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testPasswordReminder($customerStoreId):void
    {
        $customerData = ['key' => 'value'];
        $senderValues = ['name' => self::STUB_SENDER, 'email' => self::STUB_SENDER];
        $storeIds = [1, 2];

        $this->senderResolverMock
            ->expects($this->once())
            ->method('resolve')
            ->with(self::STUB_SENDER, $customerStoreId)
            ->willReturn($senderValues);

        /**
         * @var CustomerInterface|MockObject $customerMock
         */
        $customerMock = $this->getMockForAbstractClass(CustomerInterface::class);
        $customerMock->expects($this->never())
            ->method('getWebsiteId');
        $customerMock->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn(self::STUB_CUSTOMER_WEBSITE_ID);
        $customerMock->expects($this->any())
            ->method('getStoreId')
            ->willReturn($customerStoreId);
        $customerMock->expects($this->any())
            ->method('getId')
            ->willReturn(self::STUB_CUSTOMER_ID);
        $customerMock->expects($this->any())
            ->method('getEmail')
            ->willReturn(self::STUB_CUSTOMER_EMAIL);

        $this->storeMock->expects($this->any())
            ->method('getId')
            ->willReturn($customerStoreId);

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
            ->with($customerMock, CustomerInterface::class)
            ->willReturn($customerData);

        $this->customerViewHelperMock->expects($this->any())
            ->method('getCustomerName')
            ->with($customerMock)
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
                $customerStoreId
            )->willReturn(self::STUB_EMAIL_IDENTIFIER);
        $this->scopeConfigMock->expects($this->at(1))
            ->method('getValue')
            ->with(
                EmailNotification::XML_PATH_FORGOT_EMAIL_IDENTITY,
                ScopeInterface::SCOPE_STORE,
                $customerStoreId
            )->willReturn(self::STUB_SENDER);

        $this->mockDefaultTransportBuilder(
            self::STUB_EMAIL_IDENTIFIER,
            $customerStoreId,
            $senderValues,
            self::STUB_CUSTOMER_EMAIL,
            self::STUB_CUSTOMER_NAME,
            ['customer' => $this->customerSecureMock, 'store' => $this->storeMock]
        );

        $this->model->passwordReminder($customerMock);
    }

    /**
     * Test password reminder customer withouer store id info
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testPasswordReminderCustomerWithoutStoreId():void
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
        $customer = $this->getMockForAbstractClass(CustomerInterface::class);
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
     * @dataProvider customerStoreIdDataProvider
     * @param int $customerStoreId
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testPasswordResetConfirmation($customerStoreId):void
    {
        $customerData = ['key' => 'value'];
        $senderValues = ['name' => self::STUB_SENDER, 'email' => self::STUB_SENDER];

        $this->senderResolverMock
            ->expects($this->once())
            ->method('resolve')
            ->with(self::STUB_SENDER, $customerStoreId)
            ->willReturn($senderValues);

        /**
         * @var CustomerInterface|MockObject $customerMock
         */
        $customerMock = $this->getMockForAbstractClass(CustomerInterface::class);

        $customerMock->expects($this->never())
            ->method('getWebsiteId');

        $customerMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($customerStoreId);
        $customerMock->expects($this->any())
            ->method('getId')
            ->willReturn(self::STUB_CUSTOMER_ID);
        $customerMock->expects($this->any())
            ->method('getEmail')
            ->willReturn(self::STUB_CUSTOMER_EMAIL);

        $this->storeMock->expects($this->any())
            ->method('getId')
            ->willReturn($customerStoreId);

        $this->storeManagerMock->expects($this->at(0))
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->customerRegistryMock->expects($this->once())
            ->method('retrieveSecureData')
            ->with(self::STUB_CUSTOMER_ID)
            ->willReturn($this->customerSecureMock);

        $this->dataProcessorMock->expects($this->once())
            ->method('buildOutputDataArray')
            ->with($customerMock, CustomerInterface::class)
            ->willReturn($customerData);

        $this->customerViewHelperMock->expects($this->any())
            ->method('getCustomerName')
            ->with($customerMock)
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
                $customerStoreId
            )->willReturn(self::STUB_EMAIL_IDENTIFIER);
        $this->scopeConfigMock->expects($this->at(1))
            ->method('getValue')
            ->with(
                EmailNotification::XML_PATH_FORGOT_EMAIL_IDENTITY,
                ScopeInterface::SCOPE_STORE,
                $customerStoreId
            )->willReturn(self::STUB_SENDER);

        $this->mockDefaultTransportBuilder(
            self::STUB_EMAIL_IDENTIFIER,
            $customerStoreId,
            $senderValues,
            self::STUB_CUSTOMER_EMAIL,
            self::STUB_CUSTOMER_NAME,
            ['customer' => $this->customerSecureMock, 'store' => $this->storeMock]
        );

        $this->model->passwordResetConfirmation($customerMock);
    }

    /**
     * Test email notify with new account
     *
     * @dataProvider customerStoreIdDataProvider
     * @param int $customerStoreId
     * @return  void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testNewAccount($customerStoreId):void
    {
        $customerData = ['key' => 'value'];
        $senderValues = ['name' => self::STUB_SENDER, 'email' => self::STUB_SENDER];

        $this->senderResolverMock
            ->expects($this->once())
            ->method('resolve')
            ->with(self::STUB_SENDER, $customerStoreId)
            ->willReturn($senderValues);

        /**
         * @var CustomerInterface|MockObject $customer
         */
        $customer = $this->getMockForAbstractClass(CustomerInterface::class);
        $customer->expects($this->never())
            ->method('getWebsiteId');
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
            ->willReturn($customerStoreId);

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->with($customerStoreId)
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
                $customerStoreId
            )->willReturn(self::STUB_EMAIL_IDENTIFIER);
        $this->scopeConfigMock->expects($this->at(1))
            ->method('getValue')
            ->with(
                EmailNotification::XML_PATH_REGISTER_EMAIL_IDENTITY,
                ScopeInterface::SCOPE_STORE,
                $customerStoreId
            )->willReturn(self::STUB_SENDER);

        $this->mockDefaultTransportBuilder(
            self::STUB_EMAIL_IDENTIFIER,
            $customerStoreId,
            $senderValues,
            self::STUB_CUSTOMER_EMAIL,
            self::STUB_CUSTOMER_NAME,
            ['customer' => $this->customerSecureMock, 'back_url' => '', 'store' => $this->storeMock]
        );

        $this->model->newAccount(
            $customer,
            EmailNotification::NEW_ACCOUNT_EMAIL_REGISTERED,
            '',
            $customerStoreId
        );
    }

    /**
     * DataProvider customer store
     *
     * @return array
     */
    public function customerStoreIdDataProvider():array
    {
        return [
            ['customerStoreId' => 0],
            ['customerStoreId' => 2]
        ];
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
    ):void {
        $transportMock = $this->getMockForAbstractClass(TransportInterface::class);

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
            ->willReturn($transportMock);

        $transportMock->expects($this->once())
            ->method('sendMessage');
    }
}
