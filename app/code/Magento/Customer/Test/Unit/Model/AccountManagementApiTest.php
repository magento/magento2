<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\ValidationResultsInterfaceFactory;
use Magento\Customer\Api\SessionCleanerInterface;
use Magento\Customer\Helper\View;
use Magento\Customer\Model\AccountConfirmation;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\AccountManagement\Authenticate;
use Magento\Customer\Model\AccountManagementApi;
use Magento\Customer\Model\AddressRegistry;
use Magento\Customer\Model\AuthenticationInterface;
use Magento\Customer\Model\Config\Share;
use Magento\Customer\Model\Customer\CredentialsValidator;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Data\CustomerSecure;
use Magento\Customer\Model\EmailNotificationInterface;
use Magento\Customer\Model\ForgotPasswordToken\GetCustomerByToken;
use Magento\Customer\Model\Logger as CustomerLogger;
use Magento\Customer\Model\Metadata\Validator;
use Magento\Customer\Model\ResourceModel\Visitor\CollectionFactory;
use Magento\Directory\Model\AllowedCountries;
use Magento\Eav\Model\Validator\Attribute\Backend;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Authorization;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Math\Random;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Registry;
use Magento\Framework\Session\SaveHandlerInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test for validating anonymous request for synchronous operations containing group id.
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AccountManagementApiTest extends TestCase
{
    /**
     * @var AccountManagement
     */
    private $accountManagementMock;

    /**
     * @var AccountManagementApi
     */
    private $accountManagement;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var CustomerFactory|MockObject
     */
    private $customerFactory;

    /**
     * @var ManagerInterface|MockObject
     */
    private $manager;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var Random|MockObject
     */
    private $random;

    /**
     * @var Validator|MockObject
     */
    private $validator;

    /**
     * @var ValidationResultsInterfaceFactory|MockObject
     */
    private $validationResultsInterfaceFactory;

    /**
     * @var AddressRepositoryInterface|MockObject
     */
    private $addressRepository;

    /**
     * @var CustomerMetadataInterface|MockObject
     */
    private $customerMetadata;

    /**
     * @var CustomerRegistry|MockObject
     */
    private $customerRegistry;

    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    /**
     * @var EncryptorInterface|MockObject
     */
    private $encryptor;

    /**
     * @var Share|MockObject
     */
    private $share;

    /**
     * @var StringUtils|MockObject
     */
    private $string;

    /**
     * @var CustomerRepositoryInterface|MockObject
     */
    private $customerRepository;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfig;

    /**
     * @var TransportBuilder|MockObject
     */
    private $transportBuilder;

    /**
     * @var DataObjectProcessor|MockObject
     */
    private $dataObjectProcessor;

    /**
     * @var Registry|MockObject
     */
    private $registry;

    /**
     * @var View|MockObject
     */
    private $customerViewHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime|MockObject
     */
    private $dateTime;

    /**
     * @var \Magento\Customer\Model\Customer|MockObject
     */
    private $customer;

    /**
     * @var DataObjectFactory|MockObject
     */
    private $objectFactory;

    /**
     * @var ExtensibleDataObjectConverter|MockObject
     */
    private $extensibleDataObjectConverter;

    /**
     * @var DateTimeFactory|MockObject
     */
    private $dateTimeFactory;

    /**
     * @var AccountConfirmation|MockObject
     */
    private $accountConfirmation;

    /**
     * @var MockObject|SessionManagerInterface
     */
    private $sessionManager;

    /**
     * @var  MockObject|CollectionFactory
     */
    private $visitorCollectionFactory;

    /**
     * @var MockObject|SaveHandlerInterface
     */
    private $saveHandler;

    /**
     * @var MockObject|AddressRegistry
     */
    private $addressRegistryMock;

    /**
     * @var MockObject|SearchCriteriaBuilder
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var AllowedCountries|MockObject
     */
    private $allowedCountriesReader;

    /**
     * @var Authorization|MockObject
     */
    private $authorizationMock;

    /**
     * @var CustomerSecure|MockObject
     */
    private $customerSecure;

    /**
     * @var StoreInterface|MockObject
     */
    private $storeMock;

    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->customerFactory = $this->createPartialMock(CustomerFactory::class, ['create']);
        $this->manager = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->random = $this->createMock(Random::class);
        $this->validator = $this->createMock(Validator::class);
        $this->validationResultsInterfaceFactory = $this->createMock(
            ValidationResultsInterfaceFactory::class
        );
        $this->addressRepository = $this->getMockForAbstractClass(AddressRepositoryInterface::class);
        $this->customerMetadata = $this->getMockForAbstractClass(CustomerMetadataInterface::class);
        $this->customerRegistry = $this->createMock(CustomerRegistry::class);

        $this->logger = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->encryptor = $this->getMockForAbstractClass(EncryptorInterface::class);
        $this->share = $this->createMock(Share::class);
        $this->string = $this->createMock(StringUtils::class);
        $this->customerRepository = $this->getMockForAbstractClass(CustomerRepositoryInterface::class);
        $this->scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->transportBuilder = $this->createMock(TransportBuilder::class);
        $this->dataObjectProcessor = $this->createMock(DataObjectProcessor::class);
        $this->registry = $this->createMock(Registry::class);
        $this->customerViewHelper = $this->createMock(View::class);
        $this->dateTime = $this->createMock(\Magento\Framework\Stdlib\DateTime::class);
        $this->customer = $this->createMock(\Magento\Customer\Model\Customer::class);
        $this->objectFactory = $this->createMock(DataObjectFactory::class);
        $this->addressRegistryMock = $this->createMock(AddressRegistry::class);
        $this->extensibleDataObjectConverter = $this->createMock(
            ExtensibleDataObjectConverter::class
        );
        $this->allowedCountriesReader = $this->createMock(AllowedCountries::class);
        $this->customerSecure = $this->getMockBuilder(CustomerSecure::class)
            ->onlyMethods(['addData', 'setData'])
            ->addMethods(['setRpToken', 'setRpTokenCreatedAt'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->dateTimeFactory = $this->createMock(DateTimeFactory::class);
        $this->accountConfirmation = $this->createMock(AccountConfirmation::class);
        $this->searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);

        $this->visitorCollectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->sessionManager = $this->getMockBuilder(SessionManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->saveHandler = $this->getMockBuilder(SaveHandlerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->authorizationMock = $this->createMock(Authorization::class);
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $objects = [
            [
                CredentialsValidator::class,
                $this->createMock(CredentialsValidator::class)
            ],
            [
                DateTimeFactory::class,
                $this->createMock(DateTimeFactory::class)
            ],
            [
                AccountConfirmation::class,
                $this->createMock(AccountConfirmation::class)
            ],
            [
                SearchCriteriaBuilder::class,
                $this->createMock(SearchCriteriaBuilder::class)
            ],
            [
                AddressRegistry::class,
                $this->createMock(AddressRegistry::class)
            ],
            [
                GetCustomerByToken::class,
                $this->createMock(GetCustomerByToken::class)
            ],
            [
                AllowedCountries::class,
                $this->createMock(AllowedCountries::class)
            ],
            [
                SessionCleanerInterface::class,
                $this->createMock(SessionCleanerInterface::class)
            ],
            [
                AuthorizationInterface::class,
                $this->createMock(AuthorizationInterface::class)
            ],
            [
                AuthenticationInterface::class,
                $this->createMock(AuthenticationInterface::class)
            ],
            [
                Backend::class,
                $this->createMock(Backend::class)
            ],
            [
                CustomerLogger::class,
                $this->createMock(CustomerLogger::class)
            ],
            [
                Authenticate::class,
                $this->createMock(Authenticate::class)
            ],
            [
                EmailNotificationInterface::class,
                $this->createMock(EmailNotificationInterface::class)
            ]
        ];
        $this->objectManagerHelper->prepareObjectManager($objects);
        $this->accountManagement = $this->objectManagerHelper->getObject(
            AccountManagementApi::class,
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
                'extensibleDataObjectConverter' => $this->extensibleDataObjectConverter,
                'dateTimeFactory' => $this->dateTimeFactory,
                'accountConfirmation' => $this->accountConfirmation,
                'sessionManager' => $this->sessionManager,
                'saveHandler' => $this->saveHandler,
                'visitorCollectionFactory' => $this->visitorCollectionFactory,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
                'addressRegistry' => $this->addressRegistryMock,
                'allowedCountriesReader' => $this->allowedCountriesReader,
                'authorization' => $this->authorizationMock
            ]
        );
        $this->accountManagementMock = $this->createMock(AccountManagement::class);

        $this->storeMock = $this->getMockBuilder(
            StoreInterface::class
        )->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Verify that only authorized request will be able to change groupId
     *
     * @param int $groupId
     * @param int $customerId
     * @param bool $isAllowed
     * @param int $willThrowException
     * @return void
     * @throws AuthorizationException
     * @throws LocalizedException
     * @dataProvider customerDataProvider
     */
    public function testBeforeCreateAccount(
        int $groupId,
        int $customerId,
        bool $isAllowed,
        int $willThrowException
    ): void {
        if ($willThrowException) {
            $this->expectException(AuthorizationException::class);
        }
        $this->authorizationMock
            ->expects($this->once())
            ->method('isAllowed')
            ->with('Magento_Customer::manage')
            ->willReturn($isAllowed);

        $customer =  $this->getMockBuilder(CustomerInterface::class)
            ->addMethods(['setData'])
            ->getMockForAbstractClass();
        $customer->method('getGroupId')->willReturn($groupId);
        $customer->method('getId')->willReturn($customerId);
        $customer->method('getWebsiteId')->willReturn(2);
        $customer->method('getStoreId')->willReturn(1);
        $customer->method('setData')->willReturn(1);
        $customer->method('getEmail')->willReturn('email@email.com');

        $this->customerRepository->method('get')->willReturn($customer);
        $this->customerRepository->method('getById')->with($customerId)->willReturn($customer);
        $this->customerRepository->method('save')->willReturn($customer);

        if (!$willThrowException) {
            $this->accountManagementMock->method('createAccountWithPasswordHash')->willReturn($customer);
            $this->storeMock->expects($this->any())->method('getId')->willReturnOnConsecutiveCalls(2, 1);
            $this->random->method('getUniqueHash')->willReturn('testabc');
            $date = $this->getMockBuilder(\DateTime::class)
                ->disableOriginalConstructor()
                ->getMock();
            $this->dateTimeFactory->expects(static::once())
                ->method('create')
                ->willReturn($date);
            $date->expects(static::once())
                ->method('format')
                ->with('Y-m-d H:i:s')
                ->willReturn('2015-01-01 00:00:00');
            $this->customerRegistry->method('retrieveSecureData')->willReturn($this->customerSecure);
            $this->storeManager->method('getStores')
                ->willReturn([$this->storeMock]);
        }
        $this->accountManagement->createAccount($customer);
    }

    /**
     * @return array
     */
    public static function customerDataProvider(): array
    {
        return [
            [3, 1, false, 1],
            [3, 1, true, 0]
        ];
    }
}
