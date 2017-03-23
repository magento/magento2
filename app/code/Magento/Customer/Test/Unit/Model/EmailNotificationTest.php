<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model;

use Magento\Store\Model\ScopeInterface;
use Magento\Customer\Model\EmailNotification;
use Magento\Framework\App\Area;

/**
 * Class EmailNotificationTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EmailNotificationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Model\CustomerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRegistryMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $transportBuilderMock;

    /**
     * @var \Magento\Customer\Helper\View|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerViewHelperMock;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataProcessorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Customer\Model\Data\CustomerSecure
     */
    private $customerSecureMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Store\Model\Store
     */
    private $storeMock;

    /**
     * @var \Magento\Customer\Model\EmailNotification
     */
    private $model;

    public function setUp()
    {
        $this->customerRegistryMock = $this->getMock(
            \Magento\Customer\Model\CustomerRegistry::class,
            [],
            [],
            '',
            false
        );

        $this->storeManagerMock = $this->getMock(
            \Magento\Store\Model\StoreManagerInterface::class,
            [],
            [],
            '',
            false
        );

        $this->transportBuilderMock = $this->getMock(
            \Magento\Framework\Mail\Template\TransportBuilder::class,
            [],
            [],
            '',
            false
        );

        $this->customerViewHelperMock = $this->getMock(
            \Magento\Customer\Helper\View::class,
            [],
            [],
            '',
            false
        );

        $this->dataProcessorMock = $this->getMock(
            \Magento\Framework\Reflection\DataObjectProcessor::class,
            [],
            [],
            '',
            false
        );

        $contextMock = $this->getMock(
            \Magento\Framework\App\Helper\Context::class,
            ['getScopeConfig'],
            [],
            '',
            false
        );

        $this->scopeConfigMock = $this->getMock(
            \Magento\Framework\App\Config\ScopeConfigInterface::class,
            ['getValue', 'isSetFlag'],
            [],
            '',
            false
        );

        $contextMock->expects($this->any())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfigMock);

        $this->customerSecureMock = $this->getMock(
            \Magento\Customer\Model\Data\CustomerSecure::class,
            [],
            [],
            '',
            false
        );

        $this->storeMock = $this->getMock(
            \Magento\Store\Model\Store::class,
            [],
            [],
            '',
            false
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->model = $objectManager->getObject(
            EmailNotification::class,
            [
                'customerRegistry' =>  $this->customerRegistryMock,
                'storeManager' => $this->storeManagerMock,
                'transportBuilder' => $this->transportBuilderMock,
                'customerViewHelper' => $this->customerViewHelperMock,
                'dataProcessor' => $this->dataProcessorMock,
                'scopeConfig' => $this->scopeConfigMock
            ]
        );
    }

    /**
     * @param int $testNumber
     * @param string $oldEmail
     * @param string $newEmail
     * @param bool $isPasswordChanged
     *
     * @dataProvider sendNotificationEmailsDataProvider
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCredentialsChanged($testNumber, $oldEmail, $newEmail, $isPasswordChanged)
    {
        $customerId = 1;
        $customerStoreId = 2;
        $customerWebsiteId = 1;
        $customerData = ['key' => 'value'];
        $customerName = 'Customer Name';
        $templateIdentifier = 'Template Identifier';
        $sender = 'Sender';

        switch ($testNumber) {
            case 1:
                $xmlPathTemplate = EmailNotification::XML_PATH_RESET_PASSWORD_TEMPLATE;
                $expects = $this->once();
                break;
            case 2:
                $xmlPathTemplate = \Magento\Customer\Model\EmailNotification::XML_PATH_CHANGE_EMAIL_TEMPLATE;
                $expects = $this->exactly(2);
                break;
            case 3:
                $xmlPathTemplate = EmailNotification::XML_PATH_CHANGE_EMAIL_AND_PASSWORD_TEMPLATE;
                $expects = $this->exactly(2);
                break;
        }

        $origCustomer = $this->getMock(
            \Magento\Customer\Api\Data\CustomerInterface::class,
            [],
            [],
            '',
            false
        );
        $origCustomer->expects($this->any())
            ->method('getStoreId')
            ->willReturn(0);
        $origCustomer->expects($this->any())
            ->method('getId')
            ->willReturn($customerId);
        $origCustomer->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn($customerWebsiteId);

        $storeMock = $this->getMock(
            \Magento\Store\Model\Store::class,
            [],
            [],
            '',
            false
        );
        $storeMock->expects($this->any())
            ->method('getId')
            ->willReturn($customerStoreId);

        $this->storeManagerMock->expects(clone $expects)
            ->method('getStore')
            ->willReturn($storeMock);

        $websiteMock = $this->getMock(
            \Magento\Store\Model\Website::class,
            ['getStoreIds'],
            [],
            '',
            false
        );
        $websiteMock->expects($this->any())
            ->method('getStoreIds')
            ->willReturn([$customerStoreId]);

        $this->storeManagerMock->expects(clone $expects)
            ->method('getWebsite')
            ->with($customerWebsiteId)
            ->willReturn($websiteMock);

        $customerSecureMock = $this->getMock(
            \Magento\Customer\Model\Data\CustomerSecure::class,
            [],
            [],
            '',
            false
        );
        $this->customerRegistryMock->expects(clone $expects)
            ->method('retrieveSecureData')
            ->with($customerId)
            ->willReturn($customerSecureMock);

        $this->dataProcessorMock->expects(clone $expects)
            ->method('buildOutputDataArray')
            ->with($origCustomer, \Magento\Customer\Api\Data\CustomerInterface::class)
            ->willReturn($customerData);

        $this->customerViewHelperMock->expects($this->any())
            ->method('getCustomerName')
            ->with($origCustomer)
            ->willReturn($customerName);

        $customerSecureMock->expects(clone $expects)
            ->method('addData')
            ->with($customerData)
            ->willReturnSelf();
        $customerSecureMock->expects(clone $expects)
            ->method('setData')
            ->with('name', $customerName)
            ->willReturnSelf();

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
                [$xmlPathTemplate, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $customerStoreId],
                [
                    \Magento\Customer\Model\EmailNotification::XML_PATH_FORGOT_EMAIL_IDENTITY,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $customerStoreId
                ],
                [$xmlPathTemplate, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $customerStoreId],
                [
                    \Magento\Customer\Model\EmailNotification::XML_PATH_FORGOT_EMAIL_IDENTITY,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
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
            ->with(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $customerStoreId])
            ->willReturnSelf();
        $this->transportBuilderMock->expects(clone $expects)
            ->method('setTemplateVars')
            ->with(['customer' => $customerSecureMock, 'store' => $storeMock])
            ->willReturnSelf();
        $this->transportBuilderMock->expects(clone $expects)
            ->method('setFrom')
            ->with($sender)
            ->willReturnSelf();

        $this->transportBuilderMock->expects(clone $expects)
            ->method('addTo')
            ->withConsecutive([$oldEmail, $customerName], [$newEmail, $customerName])
            ->willReturnSelf();

        $transport = $this->getMock(
            \Magento\Framework\Mail\TransportInterface::class,
            [],
            [],
            '',
            false
        );

        $this->transportBuilderMock->expects(clone $expects)
            ->method('getTransport')
            ->willReturn($transport);

        $transport->expects(clone $expects)
            ->method('sendMessage');

        $this->model->credentialsChanged($savedCustomer, $oldEmail, $isPasswordChanged);
    }

    /**
     * @return array
     */
    public function sendNotificationEmailsDataProvider()
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
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testPasswordReminder()
    {
        $customerId = 1;
        $customerStoreId = 2;
        $customerEmail = 'email@email.com';
        $customerData = ['key' => 'value'];
        $customerName = 'Customer Name';
        $templateIdentifier = 'Template Identifier';
        $sender = 'Sender';

        $customer = $this->getMock(
            \Magento\Customer\Api\Data\CustomerInterface::class,
            [],
            [],
            '',
            false
        );
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

        $this->storeManagerMock->expects($this->at(0))
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->storeManagerMock->expects($this->at(1))
            ->method('getStore')
            ->with($customerStoreId)
            ->willReturn($this->storeMock);

        $this->customerRegistryMock->expects($this->once())
            ->method('retrieveSecureData')
            ->with($customerId)
            ->willReturn($this->customerSecureMock);

        $this->dataProcessorMock->expects($this->once())
            ->method('buildOutputDataArray')
            ->with($customer, \Magento\Customer\Api\Data\CustomerInterface::class)
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
            ->with(EmailNotification::XML_PATH_REMIND_EMAIL_TEMPLATE, ScopeInterface::SCOPE_STORE, $customerStoreId)
            ->willReturn($templateIdentifier);
        $this->scopeConfigMock->expects($this->at(1))
            ->method('getValue')
            ->with(EmailNotification::XML_PATH_FORGOT_EMAIL_IDENTITY, ScopeInterface::SCOPE_STORE, $customerStoreId)
            ->willReturn($sender);

        $transport = $this->getMock(
            \Magento\Framework\Mail\TransportInterface::class,
            [],
            [],
            '',
            false
        );

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
            ->with(['customer' => $this->customerSecureMock, 'store' => $this->storeMock])
            ->willReturnSelf();
        $this->transportBuilderMock->expects($this->once())
            ->method('setFrom')
            ->with($sender)
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

        $this->model->passwordReminder($customer);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testPasswordResetConfirmation()
    {
        $customerId = 1;
        $customerStoreId = 2;
        $customerEmail = 'email@email.com';
        $customerData = ['key' => 'value'];
        $customerName = 'Customer Name';
        $templateIdentifier = 'Template Identifier';
        $sender = 'Sender';

        $customer = $this->getMock(
            \Magento\Customer\Api\Data\CustomerInterface::class,
            [],
            [],
            '',
            false
        );
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

        $this->storeManagerMock->expects($this->at(0))
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->storeManagerMock->expects($this->at(1))
            ->method('getStore')
            ->with($customerStoreId)
            ->willReturn($this->storeMock);

        $this->customerRegistryMock->expects($this->once())
            ->method('retrieveSecureData')
            ->with($customerId)
            ->willReturn($this->customerSecureMock);

        $this->dataProcessorMock->expects($this->once())
            ->method('buildOutputDataArray')
            ->with($customer, \Magento\Customer\Api\Data\CustomerInterface::class)
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
            ->with(EmailNotification::XML_PATH_FORGOT_EMAIL_TEMPLATE, ScopeInterface::SCOPE_STORE, $customerStoreId)
            ->willReturn($templateIdentifier);
        $this->scopeConfigMock->expects($this->at(1))
            ->method('getValue')
            ->with(EmailNotification::XML_PATH_FORGOT_EMAIL_IDENTITY, ScopeInterface::SCOPE_STORE, $customerStoreId)
            ->willReturn($sender);

        $transport = $this->getMock(
            \Magento\Framework\Mail\TransportInterface::class,
            [],
            [],
            '',
            false
        );

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
            ->with(['customer' => $this->customerSecureMock, 'store' => $this->storeMock])
            ->willReturnSelf();
        $this->transportBuilderMock->expects($this->once())
            ->method('setFrom')
            ->with($sender)
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

        $this->model->passwordResetConfirmation($customer);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testNewAccount()
    {
        $customerId = 1;
        $customerStoreId = 2;
        $customerEmail = 'email@email.com';
        $customerData = ['key' => 'value'];
        $customerName = 'Customer Name';
        $templateIdentifier = 'Template Identifier';
        $sender = 'Sender';

        $customer = $this->getMock(
            \Magento\Customer\Api\Data\CustomerInterface::class,
            [],
            [],
            '',
            false
        );
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
            ->with($customer, \Magento\Customer\Api\Data\CustomerInterface::class)
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

        $transport = $this->getMock(
            \Magento\Framework\Mail\TransportInterface::class,
            [],
            [],
            '',
            false
        );

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
            ->with(['customer' => $this->customerSecureMock, 'back_url' => '', 'store' => $this->storeMock])
            ->willReturnSelf();
        $this->transportBuilderMock->expects($this->once())
            ->method('setFrom')
            ->with($sender)
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

        $this->model->newAccount($customer, EmailNotification::NEW_ACCOUNT_EMAIL_REGISTERED, '', $customerStoreId);
    }
}
