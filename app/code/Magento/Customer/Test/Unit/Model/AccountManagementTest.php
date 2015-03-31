<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model;

use Magento\Customer\Model\AccountManagement;
use Magento\Framework\App\Area;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Model\ScopeInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AccountManagementTest extends \PHPUnit_Framework_TestCase
{
    /** @var AccountManagement */
    protected $accountManagement;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Customer\Model\CustomerFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerFactory;

    /** @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $manager;

    /** @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeManager;

    /** @var \Magento\Framework\Math\Random|\PHPUnit_Framework_MockObject_MockObject */
    protected $random;

    /** @var \Magento\Customer\Model\Metadata\Validator|\PHPUnit_Framework_MockObject_MockObject */
    protected $validator;

    /** @var \Magento\Customer\Api\Data\ValidationResultsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $validationResultsInterfaceFactory;

    /** @var \Magento\Customer\Api\AddressRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $addressRepository;

    /** @var \Magento\Customer\Api\CustomerMetadataInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerMetadata;

    /** @var \Magento\Customer\Model\CustomerRegistry|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerRegistry;

    /** @var \Magento\Framework\Url|\PHPUnit_Framework_MockObject_MockObject */
    protected $url;

    /** @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $logger;

    /** @var \Magento\Framework\Encryption\EncryptorInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $encryptor;

    /** @var \Magento\Customer\Model\Config\Share|\PHPUnit_Framework_MockObject_MockObject */
    protected $share;

    /** @var \Magento\Framework\Stdlib\String|\PHPUnit_Framework_MockObject_MockObject */
    protected $string;

    /** @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerRepository;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $scopeConfig;

    /** @var \Magento\Framework\Mail\Template\TransportBuilder|\PHPUnit_Framework_MockObject_MockObject */
    protected $transportBuilder;

    /** @var \Magento\Framework\Reflection\DataObjectProcessor|\PHPUnit_Framework_MockObject_MockObject */
    protected $dataObjectProcessor;

    /** @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject */
    protected $registry;

    /** @var \Magento\Customer\Helper\View|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerViewHelper;

    /** @var \Magento\Framework\Stdlib\DateTime|\PHPUnit_Framework_MockObject_MockObject */
    protected $dateTime;

    /** @var \Magento\Customer\Model\Customer|\PHPUnit_Framework_MockObject_MockObject */
    protected $customer;

    /** @var \Magento\Framework\ObjectFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $objectFactory;

    /** @var \Magento\Framework\Api\ExtensibleDataObjectConverter|\PHPUnit_Framework_MockObject_MockObject */
    protected $extensibleDataObjectConverter;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->customerFactory = $this->getMock('Magento\Customer\Model\CustomerFactory', [], [], '', false);
        $this->manager = $this->getMock('Magento\Framework\Event\ManagerInterface');
        $this->storeManager = $this->getMock('Magento\Store\Model\StoreManagerInterface');
        $this->random = $this->getMock('Magento\Framework\Math\Random');
        $this->validator = $this->getMock('Magento\Customer\Model\Metadata\Validator', [], [], '', false);
        $this->validationResultsInterfaceFactory = $this->getMock(
            'Magento\Customer\Api\Data\ValidationResultsInterfaceFactory',
            [],
            [],
            '',
            false
        );
        $this->addressRepository = $this->getMock('Magento\Customer\Api\AddressRepositoryInterface');
        $this->customerMetadata = $this->getMock('Magento\Customer\Api\CustomerMetadataInterface');
        $this->customerRegistry = $this->getMock('Magento\Customer\Model\CustomerRegistry', [], [], '', false);
        $this->url = $this->getMock('Magento\Framework\Url', [], [], '', false);
        $this->logger = $this->getMock('Psr\Log\LoggerInterface');
        $this->encryptor = $this->getMock('Magento\Framework\Encryption\EncryptorInterface');
        $this->share = $this->getMock('Magento\Customer\Model\Config\Share', [], [], '', false);
        $this->string = $this->getMock('Magento\Framework\Stdlib\String');
        $this->customerRepository = $this->getMock('Magento\Customer\Api\CustomerRepositoryInterface');
        $this->scopeConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->transportBuilder = $this->getMock(
            'Magento\Framework\Mail\Template\TransportBuilder',
            [],
            [],
            '',
            false
        );
        $this->dataObjectProcessor = $this->getMock(
            'Magento\Framework\Reflection\DataObjectProcessor',
            [],
            [],
            '',
            false
        );
        $this->registry = $this->getMock('Magento\Framework\Registry');
        $this->customerViewHelper = $this->getMock('Magento\Customer\Helper\View', [], [], '', false);
        $this->dateTime = $this->getMock('Magento\Framework\Stdlib\DateTime');
        $this->customer = $this->getMock('Magento\Customer\Model\Customer', [], [], '', false);
        $this->objectFactory = $this->getMock('Magento\Framework\ObjectFactory', [], [], '', false);
        $this->extensibleDataObjectConverter = $this->getMock(
            'Magento\Framework\Api\ExtensibleDataObjectConverter',
            [],
            [],
            '',
            false
        );

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->accountManagement = $this->objectManagerHelper->getObject(
            'Magento\Customer\Model\AccountManagement',
            [
                'customerFactory' => $this->customerFactory,
                'eventManager' => $this->manager,
                'storeManager' => $this->storeManager,
                'mathRandom' => $this->random,
                'validator' => $this->validator,
                'validationResultsDataFactory' => $this->validationResultsInterfaceFactory,
                'addressRepository' => $this->addressRepository,
                'customerMetadataService' => $this->customerMetadata,
                'customerRegistry' => $this->customerRegistry,
                'url' => $this->url,
                'logger' => $this->logger,
                'encryptor' => $this->encryptor,
                'configShare' => $this->share,
                'stringHelper' => $this->string,
                'customerRepository' => $this->customerRepository,
                'scopeConfig' => $this->scopeConfig,
                'transportBuilder' => $this->transportBuilder,
                'dataProcessor' => $this->dataObjectProcessor,
                'registry' => $this->registry,
                'customerViewHelper' => $this->customerViewHelper,
                'dateTime' => $this->dateTime,
                'customerModel' => $this->customer,
                'objectFactory' => $this->objectFactory,
                'extensibleDataObjectConverter' => $this->extensibleDataObjectConverter
            ]
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testSendPasswordReminderEmail()
    {
        $customerId = 1;
        $customerStoreId = 2;
        $customerEmail = 'email@email.com';
        $passwordToken = 'token';
        $isFrontendSecure = true;
        $resetUrl = 'reset url';
        $customerData = ['key' => 'value'];
        $customerName = 'Customer Name';
        $templateIdentifier = 'Template Identifier';
        $sender = 'Sender';

        $customer = $this->getMockBuilder('Magento\Customer\Api\Data\CustomerInterface')
            ->getMock();
        $customer->expects($this->any())
            ->method('getStoreId')
            ->willReturn($customerStoreId);
        $customer->expects($this->any())
            ->method('getId')
            ->willReturn($customerId);
        $customer->expects($this->any())
            ->method('getEmail')
            ->willReturn($customerEmail);

        $store = $this->getMockBuilder('Magento\Store\Model\Store')
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManager->expects($this->any())
            ->method('getStore')
            ->with($customerStoreId)
            ->willReturn($store);

        $store->expects($this->any())
            ->method('isFrontUrlSecure')
            ->willReturn($isFrontendSecure);

        $this->url->expects($this->once())
            ->method('getUrl')
            ->with(
                'customer/account/createPassword',
                [
                    '_query' => ['id' => $customerId, 'token' => $passwordToken],
                    '_store' => $customerStoreId,
                    '_secure' => $isFrontendSecure,
                ]
            )->willReturn($resetUrl);

        $customerSecure = $this->getMockBuilder('\Magento\Customer\Model\Data\CustomerSecure')
            ->disableOriginalConstructor()
            ->setMethods(['addData', 'setData', 'setResetPasswordUrl'])
            ->getMock();

        $this->customerRegistry->expects($this->once())
            ->method('retrieveSecureData')
            ->with($customerId)
            ->willReturn($customerSecure);

        $this->dataObjectProcessor->expects($this->once())
            ->method('buildOutputDataArray')
            ->with($customer, '\Magento\Customer\Api\Data\CustomerInterface')
            ->willReturn($customerData);

        $this->customerViewHelper->expects($this->any())
            ->method('getCustomerName')
            ->with($customer)
            ->willReturn($customerName);

        $customerSecure->expects($this->once())
            ->method('addData')
            ->with($customerData)
            ->willReturnSelf();
        $customerSecure->expects($this->once())
            ->method('setData')
            ->with('name', $customerName)
            ->willReturnSelf();
        $customerSecure->expects($this->once())
            ->method('setResetPasswordUrl')
            ->with($resetUrl);

        $this->scopeConfig->expects($this->at(0))
            ->method('getValue')
            ->with(AccountManagement::XML_PATH_REMIND_EMAIL_TEMPLATE, ScopeInterface::SCOPE_STORE, $customerStoreId)
            ->willReturn($templateIdentifier);
        $this->scopeConfig->expects($this->at(1))
            ->method('getValue')
            ->with(AccountManagement::XML_PATH_FORGOT_EMAIL_IDENTITY, ScopeInterface::SCOPE_STORE, $customerStoreId)
            ->willReturn($sender);

        $transport = $this->getMockBuilder('Magento\Framework\Mail\TransportInterface')
            ->getMock();

        $this->transportBuilder->expects($this->once())
            ->method('setTemplateIdentifier')
            ->with($templateIdentifier)
            ->willReturnSelf();
        $this->transportBuilder->expects($this->once())
            ->method('setTemplateOptions')
            ->with(['area' => Area::AREA_FRONTEND, 'store' => $customerStoreId])
            ->willReturnSelf();
        $this->transportBuilder->expects($this->once())
            ->method('setTemplateVars')
            ->with(['customer' => $customerSecure, 'store' => $store])
            ->willReturnSelf();
        $this->transportBuilder->expects($this->once())
            ->method('setFrom')
            ->with($sender)
            ->willReturnSelf();
        $this->transportBuilder->expects($this->once())
            ->method('addTo')
            ->with($customerEmail, $customerName)
            ->willReturnSelf();
        $this->transportBuilder->expects($this->once())
            ->method('getTransport')
            ->willReturn($transport);

        $transport->expects($this->once())
            ->method('sendMessage');

        $this->assertEquals(
            $this->accountManagement,
            $this->accountManagement->sendPasswordReminderEmail($customer, $passwordToken)
        );
    }
}
