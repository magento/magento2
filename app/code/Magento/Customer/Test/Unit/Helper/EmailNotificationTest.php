<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Helper;

use Magento\Customer\Helper\EmailNotification;

/**
 * Test class for \Magento\Customer\Helper\EmailNotification testing
 */
class EmailNotificationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Model\CustomerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerRegistryMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $transportBuilderMock;

    /**
     * @var \Magento\Customer\Helper\View|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerViewHelperMock;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataProcessorMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Customer\Helper\EmailNotification
     */
    protected $helper;

    public function setUp()
    {
        $this->customerRegistryMock = $this->getMock(
            '\Magento\Customer\Model\CustomerRegistry',
            [],
            [],
            '',
            false
        );

        $this->storeManagerMock = $this->getMock(
            '\Magento\Store\Model\StoreManagerInterface',
            [],
            [],
            '',
            false
        );

        $this->transportBuilderMock = $this->getMock(
            '\Magento\Framework\Mail\Template\TransportBuilder',
            [],
            [],
            '',
            false
        );

        $this->customerViewHelperMock = $this->getMock(
            '\Magento\Customer\Helper\View',
            [],
            [],
            '',
            false
        );

        $this->dataProcessorMock = $this->getMock(
            '\Magento\Framework\Reflection\DataObjectProcessor',
            [],
            [],
            '',
            false
        );

        $contextMock = $this->getMock(
            '\Magento\Framework\App\Helper\Context',
            ['getScopeConfig'],
            [],
            '',
            false
        );

        $this->scopeConfigMock =  $this->getMock(
            '\Magento\Framework\App\Config\ScopeConfigInterface',
            ['getValue', 'isSetFlag'],
            [],
            '',
            false
        );

        $contextMock->expects($this->any())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfigMock);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->helper = $objectManager->getObject(
            'Magento\Customer\Helper\EmailNotification',
            [
                'customerRegistry' =>  $this->customerRegistryMock,
                'storeManager' => $this->storeManagerMock,
                'transportBuilder' => $this->transportBuilderMock,
                'customerViewHelper' => $this->customerViewHelperMock,
                'dataProcessor' => $this->dataProcessorMock,
                'context' => $contextMock
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
    public function testSendNotificationEmailsIfRequired($testNumber, $oldEmail, $newEmail, $isPasswordChanged)
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
                $xmlPathTemplate = EmailNotification::XML_PATH_CHANGE_EMAIL_TEMPLATE;
                $expects = $this->exactly(2);
                break;
            case 3:
                $xmlPathTemplate = EmailNotification::XML_PATH_CHANGE_EMAIL_AND_PASSWORD_TEMPLATE;
                $expects = $this->exactly(2);
                break;
        }

        $origCustomer = $this->getMockBuilder('Magento\Customer\Api\Data\CustomerInterface')
            ->getMock();
        $origCustomer->expects($this->any())
            ->method('getStoreId')
            ->willReturn(0);
        $origCustomer->expects($this->any())
            ->method('getId')
            ->willReturn($customerId);
        $origCustomer->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn($customerWebsiteId);

        $storeMock = $this->getMockBuilder('Magento\Store\Model\Store')
            ->disableOriginalConstructor()
            ->getMock();

        $storeMock->expects($this->any())
            ->method('getId')
            ->willReturn($customerStoreId);

        $this->storeManagerMock->expects(clone $expects)
            ->method('getStore')
            ->willReturn($storeMock);

        $websiteMock = $this->getMockBuilder('Magento\Store\Model\Website')
            ->disableOriginalConstructor()
            ->setMethods(['getStoreIds'])
            ->getMock();
        $websiteMock->expects($this->any())
            ->method('getStoreIds')
            ->willReturn([$customerStoreId]);

        $this->storeManagerMock->expects(clone $expects)
            ->method('getWebsite')
            ->with($customerWebsiteId)
            ->willReturn($websiteMock);

        $customerSecureMock = $this->getMockBuilder('Magento\Customer\Model\Data\CustomerSecure')
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerRegistryMock->expects(clone $expects)
            ->method('retrieveSecureData')
            ->with($customerId)
            ->willReturn($customerSecureMock);

        $this->dataProcessorMock->expects(clone $expects)
            ->method('buildOutputDataArray')
            ->with($origCustomer, '\Magento\Customer\Api\Data\CustomerInterface')
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
                    \Magento\Customer\Helper\EmailNotification::XML_PATH_FORGOT_EMAIL_IDENTITY,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $customerStoreId
                ],
                [$xmlPathTemplate, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $customerStoreId],
                [
                    \Magento\Customer\Helper\EmailNotification::XML_PATH_FORGOT_EMAIL_IDENTITY,
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

        $transport = $this->getMockBuilder('Magento\Framework\Mail\TransportInterface')
            ->getMock();

        $this->transportBuilderMock->expects(clone $expects)
            ->method('getTransport')
            ->willReturn($transport);

        $transport->expects(clone $expects)
            ->method('sendMessage');

        $this->assertEquals(
            $this->helper,
            $this->helper
                ->sendNotificationEmailsIfRequired($origCustomer, $savedCustomer, $isPasswordChanged)
        );
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
}
