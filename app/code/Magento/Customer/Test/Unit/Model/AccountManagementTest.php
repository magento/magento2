<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\AccountConfirmation;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\AuthenticationInterface;
use Magento\Customer\Model\EmailNotificationInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit test for Magento\Customer\Model\AccountManagement.
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AccountManagementTest extends \PHPUnit\Framework\TestCase
{
    /** @var AccountManagement */
    private $accountManagement;

    /** @var ObjectManagerHelper */
    private $objectManagerHelper;

    /** @var \Magento\Customer\Model\CustomerFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $customerFactoryMock;

    /** @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $managerMock;

    /** @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeManagerMock;

    /** @var \Magento\Framework\Math\Random|\PHPUnit_Framework_MockObject_MockObject */
    private $randomMock;

    /** @var \Magento\Customer\Model\Metadata\Validator|\PHPUnit_Framework_MockObject_MockObject */
    private $validatorMock;

    /** @var \Magento\Customer\Api\Data\ValidationResultsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $validationResultsInterfaceFactoryMock;

    /** @var \Magento\Customer\Api\AddressRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $addressRepositoryMock;

    /** @var \Magento\Customer\Api\CustomerMetadataInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $customerMetadataMock;

    /** @var \Magento\Customer\Model\CustomerRegistry|\PHPUnit_Framework_MockObject_MockObject */
    private $customerRegistryMock;

    /** @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $loggerMock;

    /** @var \Magento\Framework\Encryption\EncryptorInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $encryptorMock;

    /** @var \Magento\Customer\Model\Config\Share|\PHPUnit_Framework_MockObject_MockObject */
    private $shareMock;

    /** @var \Magento\Framework\Stdlib\StringUtils|\PHPUnit_Framework_MockObject_MockObject */
    private $stringMock;

    /** @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $customerRepositoryMock;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $scopeConfigMock;

    /** @var \Magento\Framework\Mail\Template\TransportBuilder|\PHPUnit_Framework_MockObject_MockObject */
    private $transportBuilderMock;

    /** @var \Magento\Framework\Reflection\DataObjectProcessor|\PHPUnit_Framework_MockObject_MockObject */
    private $dataObjectProcessorMock;

    /** @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject */
    private $registryMock;

    /** @var \Magento\Customer\Helper\View|\PHPUnit_Framework_MockObject_MockObject */
    private $customerViewHelperMock;

    /** @var \Magento\Framework\Stdlib\DateTime|\PHPUnit_Framework_MockObject_MockObject */
    private $dateTimeMock;

    /** @var \Magento\Customer\Model\Customer|\PHPUnit_Framework_MockObject_MockObject */
    private $customerMock;

    /** @var \Magento\Framework\DataObjectFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $objectFactoryMock;

    /** @var \Magento\Framework\Api\ExtensibleDataObjectConverter|\PHPUnit_Framework_MockObject_MockObject */
    private $extensibleDataObjectConverterMock;

    /** @var \Magento\Customer\Model\Data\CustomerSecure|\PHPUnit_Framework_MockObject_MockObject */
    private $customerSecureMock;

    /** @var AuthenticationInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $authenticationMock;

    /** @var EmailNotificationInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $emailNotificationMock;

    /** @var DateTimeFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $dateTimeFactoryMock;

    /** @var AccountConfirmation|\PHPUnit_Framework_MockObject_MockObject */
    private $accountConfirmationMock;

    /** @var \Magento\Framework\Session\SessionManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $sessionManagerMock;

    /** @var \Magento\Customer\Model\ResourceModel\Visitor\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $visitorCollectionFactoryMock;

    /** @var \Magento\Framework\Session\SaveHandlerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $saveHandlerMock;

    /** @var \Magento\Customer\Model\AddressRegistry|\PHPUnit_Framework_MockObject_MockObject */
    private $addressRegistryMock;

    /** @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject */
    private $searchCriteriaBuilderMock;

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->customerFactoryMock = $this->createPartialMock(
            \Magento\Customer\Model\CustomerFactory::class,
            ['create']
        );
        $this->managerMock = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->randomMock = $this->createMock(\Magento\Framework\Math\Random::class);
        $this->validatorMock = $this->createMock(\Magento\Customer\Model\Metadata\Validator::class);
        $this->validationResultsInterfaceFactoryMock = $this->createMock(
            \Magento\Customer\Api\Data\ValidationResultsInterfaceFactory::class
        );
        $this->addressRepositoryMock = $this->createMock(\Magento\Customer\Api\AddressRepositoryInterface::class);
        $this->customerMetadataMock = $this->createMock(\Magento\Customer\Api\CustomerMetadataInterface::class);
        $this->customerRegistryMock = $this->createMock(\Magento\Customer\Model\CustomerRegistry::class);
        $this->loggerMock = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->encryptorMock = $this->createMock(\Magento\Framework\Encryption\EncryptorInterface::class);
        $this->shareMock = $this->createMock(\Magento\Customer\Model\Config\Share::class);
        $this->stringMock = $this->createMock(\Magento\Framework\Stdlib\StringUtils::class);
        $this->customerRepositoryMock = $this->createMock(\Magento\Customer\Api\CustomerRepositoryInterface::class);
        $this->scopeConfigMock = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->transportBuilderMock = $this->createMock(\Magento\Framework\Mail\Template\TransportBuilder::class);
        $this->dataObjectProcessorMock = $this->createMock(\Magento\Framework\Reflection\DataObjectProcessor::class);
        $this->registryMock = $this->createMock(\Magento\Framework\Registry::class);
        $this->customerViewHelperMock = $this->createMock(\Magento\Customer\Helper\View::class);
        $this->dateTimeMock = $this->createMock(\Magento\Framework\Stdlib\DateTime::class);
        $this->customerMock = $this->createMock(\Magento\Customer\Model\Customer::class);
        $this->objectFactoryMock = $this->createMock(\Magento\Framework\DataObjectFactory::class);
        $this->addressRegistryMock = $this->createMock(\Magento\Customer\Model\AddressRegistry::class);
        $this->extensibleDataObjectConverterMock = $this->createMock(
            \Magento\Framework\Api\ExtensibleDataObjectConverter::class
        );
        $this->authenticationMock = $this->createMock(AuthenticationInterface::class);
        $this->emailNotificationMock = $this->createMock(EmailNotificationInterface::class);

        $this->customerSecureMock = $this->getMockBuilder(\Magento\Customer\Model\Data\CustomerSecure::class)
            ->setMethods(['setRpToken', 'addData', 'setRpTokenCreatedAt', 'setData'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->accountConfirmationMock = $this->createMock(AccountConfirmation::class);
        $this->searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);

        $this->visitorCollectionFactoryMock = $this->getMockBuilder(
            \Magento\Customer\Model\ResourceModel\Visitor\CollectionFactory::class
        )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->sessionManagerMock = $this->createMock(\Magento\Framework\Session\SessionManagerInterface::class);
        $this->saveHandlerMock = $this->createMock(\Magento\Framework\Session\SaveHandlerInterface::class);

        $this->dateTimeInit();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->accountManagement = $this->objectManagerHelper->getObject(
            AccountManagement::class,
            [
                'customerFactory' => $this->customerFactoryMock,
                'eventManager' => $this->managerMock,
                'storeManager' => $this->storeManagerMock,
                'mathRandom' => $this->randomMock,
                'validator' => $this->validatorMock,
                'validationResultsDataFactory' => $this->validationResultsInterfaceFactoryMock,
                'addressRepository' => $this->addressRepositoryMock,
                'customerMetadataService' => $this->customerMetadataMock,
                'customerRegistry' => $this->customerRegistryMock,
                'logger' => $this->loggerMock,
                'encryptor' => $this->encryptorMock,
                'configShare' => $this->shareMock,
                'stringHelper' => $this->stringMock,
                'customerRepository' => $this->customerRepositoryMock,
                'scopeConfig' => $this->scopeConfigMock,
                'transportBuilder' => $this->transportBuilderMock,
                'dataProcessor' => $this->dataObjectProcessorMock,
                'registry' => $this->registryMock,
                'customerViewHelper' => $this->customerViewHelperMock,
                'dateTime' => $this->dateTimeMock,
                'customerModel' => $this->customerMock,
                'objectFactory' => $this->objectFactoryMock,
                'extensibleDataObjectConverter' => $this->extensibleDataObjectConverterMock,
                'dateTimeFactory' => $this->dateTimeFactoryMock,
                'accountConfirmation' => $this->accountConfirmationMock,
                'sessionManager' => $this->sessionManagerMock,
                'saveHandler' => $this->saveHandlerMock,
                'visitorCollectionFactory' => $this->visitorCollectionFactoryMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
                'addressRegistry' => $this->addressRegistryMock,
            ]
        );
    }

    /**
     * Init DateTimeFactory.
     *
     * @return void
     */
    private function dateTimeInit()
    {
        $dateTime = '2017-10-25 18:57:08';
        $timestamp = '1508983028';
        $dateTimeMock = $this->getMockBuilder(\DateTime::class)
            ->disableOriginalConstructor()
            ->setMethods(['format', 'getTimestamp', 'setTimestamp'])
            ->getMock();

        $dateTimeMock->expects($this->once())
            ->method('format')
            ->with(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT)
            ->willReturn($dateTime);
        $dateTimeMock->expects($this->once())
            ->method('getTimestamp')
            ->willReturn($timestamp);
        $dateTimeMock->expects($this->once())
            ->method('setTimestamp')
            ->willReturnSelf();
        $this->dateTimeFactoryMock = $this->getMockBuilder(DateTimeFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->dateTimeFactoryMock->expects($this->once())->method('create')->willReturn($dateTimeMock);
    }

    /**
     * Test for changePassword method.
     *
     * @return void
     */
    public function testChangePassword()
    {
        $customerId = 7;
        $email = 'test@example.com';
        $currentPassword = '1234567';
        $newPassword = 'abcdefg';

        $customer = $this->createMock(CustomerInterface::class);

        $customer->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($customerId);
        $customer->expects($this->once())
            ->method('getAddresses')
            ->willReturn([]);
        $this->customerRepositoryMock->expects($this->once())
            ->method('get')
            ->with($email)
            ->willReturn($customer);
        $this->customerSecureMock->expects($this->once())
            ->method('setRpToken')
            ->with(null)
            ->willReturnSelf();
        $this->customerSecureMock->expects($this->once())
            ->method('setRpTokenCreatedAt')
            ->with(null)
            ->willReturnSelf();
        $this->customerRegistryMock->expects($this->once())
            ->method('retrieveSecureData')
            ->with($customerId)
            ->willReturn($this->customerSecureMock);

        $this->scopeConfigMock->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturnMap(
                [
                    [
                        AccountManagement::XML_PATH_MINIMUM_PASSWORD_LENGTH,
                        'default',
                        null,
                        7,
                    ],
                    [
                        AccountManagement::XML_PATH_REQUIRED_CHARACTER_CLASSES_NUMBER,
                        'default',
                        null,
                        1,
                    ],
                ]
            );
        $this->stringMock->expects($this->atLeastOnce())
            ->method('strlen')
            ->with($newPassword)
            ->willReturn(7);
        $this->customerRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->with($customer);
        $this->sessionManagerMock->expects($this->atLeastOnce())->method('getSessionId');
        $visitor = $this->getMockBuilder(\Magento\Customer\Model\Visitor::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSessionId'])
            ->getMock();
        $visitor->expects($this->atLeastOnce())->method('getSessionId')
            ->willReturnOnConsecutiveCalls('session_id_1', 'session_id_2');
        $visitorCollection = $this->getMockBuilder(
            \Magento\Customer\Model\ResourceModel\Visitor\CollectionFactory::class
        )->disableOriginalConstructor()
            ->setMethods(['addFieldToFilter', 'getItems'])
            ->getMock();
        $visitorCollection->expects($this->atLeastOnce())->method('addFieldToFilter')->willReturnSelf();
        $visitorCollection->expects($this->once())->method('getItems')->willReturn([$visitor, $visitor]);
        $this->visitorCollectionFactoryMock->expects($this->once())->method('create')
            ->willReturn($visitorCollection);
        $this->saveHandlerMock->expects($this->atLeastOnce())->method('destroy')
            ->withConsecutive(
                ['session_id_1'],
                ['session_id_2']
            );

        $this->assertTrue($this->accountManagement->changePassword($email, $currentPassword, $newPassword));
    }
}
