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
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Verify EmailNotificationTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EmailNotificationTest extends TestCase
{
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
     * @var SenderResolverInterface|MockObject
     */
    private $senderResolverMock;

    /**
     * @var EmailNotification
     */
    private $model;

    public function setUp()
    {
        $this->customerRegistryMock = $this->createMock(CustomerRegistry::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
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

        $objectManager = new ObjectManager($this);

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
     * @param int $testNumber
     * @param int $customerStoreId
     * @param string $oldEmail
     * @param string $newEmail
     * @param bool $isPasswordChanged
     *
     * @dataProvider sendNotificationEmailsDataProvider
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCredentialsChanged($testNumber, $customerStoreId, $oldEmail, $newEmail, $isPasswordChanged)
    {
        $customerId = 1;
        $customerData = ['key' => 'value'];
        $customerName = 'Customer Name';
        $templateIdentifier = 'Template Identifier';
        $sender = 'Sender';
        $senderValues = ['name' => $sender, 'email' => $sender];

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
            ->with($sender, $customerStoreId)
            ->willReturn($senderValues);

        /** @var MockObject $origCustomerMock */
        $origCustomerMock = $this->createMock(CustomerInterface::class);
        $origCustomerMock->expects($this->any())
            ->method('getStoreId')
            ->willReturn($customerStoreId);
        $origCustomerMock->expects($this->any())
            ->method('getId')
            ->willReturn($customerId);
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

        $customerSecureMock = $this->createMock(CustomerSecure::class);
        $this->customerRegistryMock->expects(clone $expects)
            ->method('retrieveSecureData')
            ->with($customerId)
            ->willReturn($customerSecureMock);

        $this->dataProcessorMock->expects(clone $expects)
            ->method('buildOutputDataArray')
            ->with($origCustomerMock, CustomerInterface::class)
            ->willReturn($customerData);

        $this->customerViewHelperMock->expects($this->any())
            ->method('getCustomerName')
            ->with($origCustomerMock)
            ->willReturn($customerName);

        $customerSecureMock->expects(clone $expects)
            ->method('addData')
            ->with($customerData)
            ->willReturnSelf();
        $customerSecureMock->expects(clone $expects)
            ->method('setData')
            ->with('name', $customerName)
            ->willReturnSelf();

        /** @var CustomerInterface|MockObject $savedCustomerMock */
        $savedCustomerMock = clone $origCustomerMock;

        $origCustomerMock->expects($this->any())
            ->method('getEmail')
            ->willReturn($oldEmail);

        $savedCustomerMock->expects($this->any())
            ->method('getEmail')
            ->willReturn($newEmail);

        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->withConsecutive(
                [$xmlPathTemplate, ScopeInterface::SCOPE_STORE, $customerStoreId],
                [
                    EmailNotification::XML_PATH_FORGOT_EMAIL_IDENTITY,
                    ScopeInterface::SCOPE_STORE,
                    $customerStoreId
                ],
                [$xmlPathTemplate, ScopeInterface::SCOPE_STORE, $customerStoreId],
                [
                    EmailNotification::XML_PATH_FORGOT_EMAIL_IDENTITY,
                    ScopeInterface::SCOPE_STORE,
                    $customerStoreId
                ]
            )
            ->willReturnOnConsecutiveCalls($templateIdentifier, $sender, $templateIdentifier, $sender);

        $this->transportBuilderMock->expects(clone $expects)
            ->method('setTemplateIdentifier')
            ->with($templateIdentifier)
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
            ->withConsecutive([$oldEmail, $customerName], [$newEmail, $customerName])
            ->willReturnSelf();

        $transport = $this->createMock(TransportInterface::class);

        $this->transportBuilderMock->expects(clone $expects)
            ->method('getTransport')
            ->willReturn($transport);

        $transport->expects(clone $expects)
            ->method('sendMessage');

        $this->model->credentialsChanged($savedCustomerMock, $oldEmail, $isPasswordChanged);
    }

    /**
     * @return array
     */
    public function sendNotificationEmailsDataProvider()
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
     * @param int $customerStoreId
     * @dataProvider customerStoreIdDataProvider
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testPasswordReminder($customerStoreId):void
    {
        $customerId = 1;
        $customerWebsiteId = 1;
        $customerEmail = 'email@email.com';
        $customerData = ['key' => 'value'];
        $customerName = 'Customer Name';
        $templateIdentifier = 'Template Identifier';
        $sender = 'Sender';
        $senderValues = ['name' => $sender, 'email' => $sender];
        $storeIds = [1, 2];

        $this->senderResolverMock
            ->expects($this->once())
            ->method('resolve')
            ->with($sender, $customerStoreId)
            ->willReturn($senderValues);

        /** @var CustomerInterface|MockObject $customerMock */
        $customerMock = $this->createMock(CustomerInterface::class);
        $customerMock->expects($this->never())
            ->method('getWebsiteId');
        $customerMock->expects($this->any())
            ->method('getStoreId')
            ->willReturn($customerStoreId);
        $customerMock->expects($this->any())
            ->method('getId')
            ->willReturn($customerId);
        $customerMock->expects($this->any())
            ->method('getEmail')
            ->willReturn($customerEmail);

        $this->storeMock->expects($this->any())
            ->method('getId')
            ->willReturn($customerStoreId);

        $this->storeManagerMock->expects($this->at(0))
            ->method('getStore')
            ->willReturn($this->storeMock);

        $websiteMock = $this->createPartialMock(
            Website::class,
            ['getStoreIds']
        );
        $websiteMock->expects($this->any())
            ->method('getStoreIds')
            ->willReturn($storeIds);

        $this->storeManagerMock->expects($this->any())
            ->method('getWebsite')
            ->with($customerWebsiteId)
            ->willReturn($websiteMock);

        $this->customerRegistryMock->expects($this->once())
            ->method('retrieveSecureData')
            ->with($customerId)
            ->willReturn($this->customerSecureMock);

        $this->dataProcessorMock->expects($this->once())
            ->method('buildOutputDataArray')
            ->with($customerMock, CustomerInterface::class)
            ->willReturn($customerData);

        $this->customerViewHelperMock->expects($this->any())
            ->method('getCustomerName')
            ->with($customerMock)
            ->willReturn($customerName);

        $this->customerSecureMock->expects($this->once())
            ->method('addData')
            ->with($customerData)
            ->willReturnSelf();
        $this->customerSecureMock->expects($this->once())
            ->method('setData')
            ->with('name', $customerName)
            ->willReturnSelf();

        $this->scopeConfigMock->expects($this->at(0))
            ->method('getValue')
            ->with(EmailNotification::XML_PATH_REMIND_EMAIL_TEMPLATE, ScopeInterface::SCOPE_STORE, $customerStoreId)
            ->willReturn($templateIdentifier);
        $this->scopeConfigMock->expects($this->at(1))
            ->method('getValue')
            ->with(EmailNotification::XML_PATH_FORGOT_EMAIL_IDENTITY, ScopeInterface::SCOPE_STORE, $customerStoreId)
            ->willReturn($sender);

        $this->mockDefaultTransportBuilder(
            $templateIdentifier,
            $customerStoreId,
            $senderValues,
            $customerEmail,
            $customerName,
            ['customer' => $this->customerSecureMock, 'store' => $this->storeMock]
        );

        $this->model->passwordReminder($customerMock);
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testPasswordReminderCustomerWithoutStoreId():void
    {
        $customerId = 1;
        $customerWebsiteId = 1;
        $customerStoreId = null;
        $customerEmail = 'email@email.com';
        $customerData = ['key' => 'value'];
        $customerName = 'Customer Name';
        $templateIdentifier = 'Template Identifier';
        $sender = 'Sender';
        $senderValues = ['name' => $sender, 'email' => $sender];
        $storeIds = [1, 2];
        $defaultStoreId = reset($storeIds);
        $this->senderResolverMock
            ->expects($this->once())
            ->method('resolve')
            ->with($sender, $defaultStoreId)
            ->willReturn($senderValues);
        /** @var CustomerInterface | MockObject $customerMock */
        $customerMock = $this->createMock(CustomerInterface::class);
        $customerMock->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn($customerWebsiteId);
        $customerMock->expects($this->any())
            ->method('getStoreId')
            ->willReturn($customerStoreId);
        $customerMock->expects($this->any())
            ->method('getId')
            ->willReturn($customerId);
        $customerMock->expects($this->any())
            ->method('getEmail')
            ->willReturn($customerEmail);
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
            ->with($customerWebsiteId)
            ->willReturn($websiteMock);

        $this->customerRegistryMock->expects($this->once())
            ->method('retrieveSecureData')
            ->with($customerId)
            ->willReturn($this->customerSecureMock);
        $this->dataProcessorMock->expects($this->once())
            ->method('buildOutputDataArray')
            ->with($customerMock, CustomerInterface::class)
            ->willReturn($customerData);
        $this->customerViewHelperMock->expects($this->any())
            ->method('getCustomerName')
            ->with($customerMock)
            ->willReturn($customerName);
        $this->customerSecureMock->expects($this->once())
            ->method('addData')
            ->with($customerData)
            ->willReturnSelf();
        $this->customerSecureMock->expects($this->once())
            ->method('setData')
            ->with('name', $customerName)
            ->willReturnSelf();
        $this->scopeConfigMock->expects($this->at(0))
            ->method('getValue')
            ->with(EmailNotification::XML_PATH_REMIND_EMAIL_TEMPLATE, ScopeInterface::SCOPE_STORE, $defaultStoreId)
            ->willReturn($templateIdentifier);
        $this->scopeConfigMock->expects($this->at(1))
            ->method('getValue')
            ->with(EmailNotification::XML_PATH_FORGOT_EMAIL_IDENTITY, ScopeInterface::SCOPE_STORE, $defaultStoreId)
            ->willReturn($sender);
        $this->mockDefaultTransportBuilder(
            $templateIdentifier,
            $defaultStoreId,
            $senderValues,
            $customerEmail,
            $customerName,
            ['customer' => $this->customerSecureMock, 'store' => $this->storeMock]
        );
        $this->model->passwordReminder($customerMock);
    }

    /**
     * @dataProvider customerStoreIdDataProvider
     * @param int $customerStoreId
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testPasswordResetConfirmation($customerStoreId):void
    {
        $customerId = 1;
        $customerEmail = 'email@email.com';
        $customerData = ['key' => 'value'];
        $customerName = 'Customer Name';
        $templateIdentifier = 'Template Identifier';
        $sender = 'Sender';
        $senderValues = ['name' => $sender, 'email' => $sender];

        $this->senderResolverMock
            ->expects($this->once())
            ->method('resolve')
            ->with($sender, $customerStoreId)
            ->willReturn($senderValues);

        /** @var CustomerInterface|MockObject $customerMock */
        $customerMock = $this->createMock(CustomerInterface::class);

        $customerMock->expects($this->never())
            ->method('getWebsiteId');

        $customerMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($customerStoreId);
        $customerMock->expects($this->any())
            ->method('getId')
            ->willReturn($customerId);
        $customerMock->expects($this->any())
            ->method('getEmail')
            ->willReturn($customerEmail);

        $this->storeMock->expects($this->any())
            ->method('getId')
            ->willReturn($customerStoreId);

        $this->storeManagerMock->expects($this->at(0))
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->customerRegistryMock->expects($this->once())
            ->method('retrieveSecureData')
            ->with($customerId)
            ->willReturn($this->customerSecureMock);

        $this->dataProcessorMock->expects($this->once())
            ->method('buildOutputDataArray')
            ->with($customerMock, CustomerInterface::class)
            ->willReturn($customerData);

        $this->customerViewHelperMock->expects($this->any())
            ->method('getCustomerName')
            ->with($customerMock)
            ->willReturn($customerName);

        $this->customerSecureMock->expects($this->once())
            ->method('addData')
            ->with($customerData)
            ->willReturnSelf();
        $this->customerSecureMock->expects($this->once())
            ->method('setData')
            ->with('name', $customerName)
            ->willReturnSelf();

        $this->scopeConfigMock->expects($this->at(0))
            ->method('getValue')
            ->with(EmailNotification::XML_PATH_FORGOT_EMAIL_TEMPLATE, ScopeInterface::SCOPE_STORE, $customerStoreId)
            ->willReturn($templateIdentifier);
        $this->scopeConfigMock->expects($this->at(1))
            ->method('getValue')
            ->with(EmailNotification::XML_PATH_FORGOT_EMAIL_IDENTITY, ScopeInterface::SCOPE_STORE, $customerStoreId)
            ->willReturn($sender);

        $this->mockDefaultTransportBuilder(
            $templateIdentifier,
            $customerStoreId,
            $senderValues,
            $customerEmail,
            $customerName,
            ['customer' => $this->customerSecureMock, 'store' => $this->storeMock]
        );

        $this->model->passwordResetConfirmation($customerMock);
    }

    /**
     * @dataProvider customerStoreIdDataProvider
     * @param int $customerStoreId
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testNewAccount($customerStoreId):void
    {
        $customerId = 1;
        $customerEmail = 'email@email.com';
        $customerData = ['key' => 'value'];
        $customerName = 'Customer Name';
        $templateIdentifier = 'Template Identifier';
        $sender = 'Sender';
        $senderValues = ['name' => $sender, 'email' => $sender];

        $this->senderResolverMock
            ->expects($this->once())
            ->method('resolve')
            ->with($sender, $customerStoreId)
            ->willReturn($senderValues);

        /** @var CustomerInterface|MockObject $customer */
        $customer = $this->createMock(CustomerInterface::class);

        $customer->expects($this->never())
            ->method('getWebsiteId');

        $customer->expects($this->any())
            ->method('getStoreId')
            ->willReturn($customerStoreId);
        $customer->expects($this->any())
            ->method('getId')
            ->willReturn($customerId);
        $customer->expects($this->any())
            ->method('getEmail')
            ->willReturn($customerEmail);

        $this->storeMock->expects($this->any())
            ->method('getId')
            ->willReturn($customerStoreId);

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->with($customerStoreId)
            ->willReturn($this->storeMock);

        $this->customerRegistryMock->expects($this->once())
            ->method('retrieveSecureData')
            ->with($customerId)
            ->willReturn($this->customerSecureMock);

        $this->dataProcessorMock->expects($this->once())
            ->method('buildOutputDataArray')
            ->with($customer, CustomerInterface::class)
            ->willReturn($customerData);

        $this->customerViewHelperMock->expects($this->any())
            ->method('getCustomerName')
            ->with($customer)
            ->willReturn($customerName);

        $this->customerSecureMock->expects($this->once())
            ->method('addData')
            ->with($customerData)
            ->willReturnSelf();
        $this->customerSecureMock->expects($this->once())
            ->method('setData')
            ->with('name', $customerName)
            ->willReturnSelf();

        $this->scopeConfigMock->expects($this->at(0))
            ->method('getValue')
            ->with(EmailNotification::XML_PATH_REGISTER_EMAIL_TEMPLATE, ScopeInterface::SCOPE_STORE, $customerStoreId)
            ->willReturn($templateIdentifier);
        $this->scopeConfigMock->expects($this->at(1))
            ->method('getValue')
            ->with(EmailNotification::XML_PATH_REGISTER_EMAIL_IDENTITY, ScopeInterface::SCOPE_STORE, $customerStoreId)
            ->willReturn($sender);

        $this->mockDefaultTransportBuilder(
            $templateIdentifier,
            $customerStoreId,
            $senderValues,
            $customerEmail,
            $customerName,
            ['customer' => $this->customerSecureMock, 'back_url' => '', 'store' => $this->storeMock]
        );

        $this->model->newAccount($customer, EmailNotification::NEW_ACCOUNT_EMAIL_REGISTERED, '', $customerStoreId);
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
    ): void {
        $transportMock = $this->createMock(TransportInterface::class);

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
