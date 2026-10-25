<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\ValidationResultsInterface;
use Magento\Customer\Api\Data\ValidationResultsInterfaceFactory;
use Magento\Customer\Api\SessionCleanerInterface;
use Magento\Customer\Helper\View;
use Magento\Customer\Model\AccountConfirmation;
use Magento\Customer\Model\AccountManagement\Authenticate;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\AddressFactory;
use Magento\Customer\Model\AddressRegistry;
use Magento\Customer\Model\AuthenticationInterface;
use Magento\Customer\Model\Config\Share;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Data\Customer;
use Magento\Customer\Model\Data\CustomerSecure;
use Magento\Customer\Model\EmailNotificationInterface;
use Magento\Customer\Model\Log;
use Magento\Customer\Model\Logger as CustomerLogger;
use Magento\Customer\Model\Metadata\Validator;
use Magento\Customer\Model\ResourceModel\Visitor\CollectionFactory;
use Magento\Directory\Model\AllowedCountries;
use Magento\Eav\Model\Validator\Attribute\Backend;
use Magento\Email\Model\ResourceModel\Template\CollectionFactory as TemplateCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\State\ExpiredException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Mail\TransportInterface;
use Magento\Framework\Math\Random;
use Magento\Framework\Phrase;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Registry;
use Magento\Framework\Session\SaveHandlerInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Validator\Factory as ValidatorFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class AccountManagementTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var AccountManagement
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
     * @var MockObject|Store
     */
    private $store;

    /**
     * @var MockObject|CustomerSecure
     */
    private $customerSecure;

    /**
     * @var AuthenticationInterface|MockObject
     */
    private $authenticationMock;

    /**
     * @var EmailNotificationInterface|MockObject
     */
    private $emailNotificationMock;

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
     * @var MockObject|AddressFactory
     */
    private $addressFactory;

    /**
     * @var MockObject|ValidatorFactory
     */
    private $validatorFactory;

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
     * @var SessionCleanerInterface|MockObject
     */
    private $sessionCleanerMock;

    /**
     * @var CustomerLogger|MockObject
     */
    private $customerLoggerMock;

    /**
     * @var Authenticate|MockObject
     */
    private $authenticateMock;

    /**
     * @var Backend|MockObject
     */
    private $eavValidatorMock;

    /**
     * @var int
     */
    private $getIdCounter;

    /**
     * @var int
     */
    private $getWebsiteIdCounter;

    /**
     * @var int|null
     */
    private $customerStoreId;

    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->customerFactory = $this->createMock(CustomerFactory::class);
        $this->manager = $this->createMock(ManagerInterface::class);
        $this->store = $this->createMock(Store::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->random = $this->createMock(Random::class);
        $this->validator = $this->createMock(Validator::class);
        $this->validationResultsInterfaceFactory = $this->createMock(
            ValidationResultsInterfaceFactory::class
        );
        $this->addressRepository = $this->createMock(AddressRepositoryInterface::class);
        $this->customerMetadata = $this->createMock(CustomerMetadataInterface::class);
        $this->customerRegistry = $this->createMock(CustomerRegistry::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->encryptor = $this->createMock(EncryptorInterface::class);
        $this->share = $this->createMock(Share::class);
        $this->string = $this->createMock(StringUtils::class);
        $this->customerRepository = $this->createMock(CustomerRepositoryInterface::class);
        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);
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
        $this->authenticationMock = $this->createMock(AuthenticationInterface::class);
        $this->emailNotificationMock = $this->createMock(EmailNotificationInterface::class);

        $this->customerSecure = $this->createPartialMockWithReflection(
            CustomerSecure::class,
            ['addData', 'setData', 'setRpToken', 'setRpTokenCreatedAt']
        );

        $this->dateTimeFactory = $this->createMock(DateTimeFactory::class);
        $this->accountConfirmation = $this->createMock(AccountConfirmation::class);
        $this->searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);

        $this->visitorCollectionFactory = $this->createMock(CollectionFactory::class);
        $this->sessionManager = $this->createMock(SessionManagerInterface::class);
        $this->saveHandler = $this->createMock(SaveHandlerInterface::class);
        $this->addressFactory = $this->createMock(AddressFactory::class);
        $this->validatorFactory = $this->createMock(ValidatorFactory::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $objects = [
            [
                AccountManagementInterface::class,
                $this->createMock(AccountManagementInterface::class)
            ],
            [
                CustomerInterfaceFactory::class,
                $this->createMock(CustomerInterfaceFactory::class)
            ],
            [
                DataObjectHelper::class,
                $this->createMock(DataObjectHelper::class)
            ],
            [
                StoreManagerInterface::class,
                $this->createMock(StoreManagerInterface::class)
            ],
            [
                CustomerRepositoryInterface::class,
                $this->createMock(CustomerRepositoryInterface::class)
            ],
            [
                ExtensibleDataObjectConverter::class,
                $this->createMock(ExtensibleDataObjectConverter::class)
            ],
            [
                CustomerFactory::class,
                $this->createMock(CustomerFactory::class)
            ],
            [
                Random::class,
                $this->createMock(Random::class)
            ],
            [
                EncryptorInterface::class,
                $this->createMock(EncryptorInterface::class)
            ],
            [
                MutableScopeConfigInterface::class,
                $this->createMock(MutableScopeConfigInterface::class)
            ],
            [
                TemplateCollectionFactory::class,
                $this->createMock(TemplateCollectionFactory::class)
            ],
        ];
        $this->objectManagerHelper->prepareObjectManager($objects);
        $this->accountManagement = $this->objectManagerHelper->getObject(
            AccountManagement::class,
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
                'addressFactory' => $this->addressFactory,
                'validatorFactory' => $this->validatorFactory,
            ]
        );
        $this->objectManagerHelper->setBackwardCompatibleProperty(
            $this->accountManagement,
            'authentication',
            $this->authenticationMock
        );
        $this->objectManagerHelper->setBackwardCompatibleProperty(
            $this->accountManagement,
            'emailNotification',
            $this->emailNotificationMock
        );
        $this->customerLoggerMock = $this->createMock(CustomerLogger::class);
        $this->objectManagerHelper->setBackwardCompatibleProperty(
            $this->accountManagement,
            'customerLogger',
            $this->customerLoggerMock
        );
        $this->authenticateMock = $this->createMock(Authenticate::class);
        $this->objectManagerHelper->setBackwardCompatibleProperty(
            $this->accountManagement,
            'authenticate',
            $this->authenticateMock
        );
        $this->eavValidatorMock = $this->createMock(Backend::class);
        $this->objectManagerHelper->setBackwardCompatibleProperty(
            $this->accountManagement,
            'eavValidator',
            $this->eavValidatorMock
        );
        $this->allowedCountriesReader->method('getAllowedCountries')->willReturn(['US' => 'US']);

        $this->getIdCounter = 0;
        $this->getWebsiteIdCounter = 0;
        $this->customerStoreId = null;
    }

    /**
     * @return void
     */
    public function testCreateAccountWithPasswordHashWithExistingCustomer(): void
    {
        $this->expectException(InputException::class);

        $websiteId = 1;
        $storeId = 1;
        $customerId = 1;
        $customerEmail = 'email@email.com';
        $hash = '4nj54lkj5jfi03j49f8bgujfgsd';

        $website = $this->createMock(Website::class);
        $website->expects($this->once())
            ->method('getStoreIds')
            ->willReturn([1, 2, 3]);
        $customer = $this->createMock(Customer::class);
        $customer->expects($this->once())
            ->method('getId')
            ->willReturn($customerId);
        $customer->expects($this->once())
            ->method('getEmail')
            ->willReturn($customerEmail);
        $customer->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $customer->expects($this->atLeastOnce())
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->customerRepository
            ->expects($this->once())
            ->method('get')
            ->with($customerEmail)
            ->willReturn($customer);
        $this->share
            ->expects($this->once())
            ->method('isWebsiteScope')
            ->willReturn(true);
        $this->storeManager
            ->expects($this->once())
            ->method('getWebsite')
            ->with($websiteId)
            ->willReturn($website);
        $this->accountManagement->createAccountWithPasswordHash($customer, $hash);
    }

    /**
     * @return void
     */
    public function testCreateAccountWithPasswordHashWithCustomerWithoutStoreId(): void
    {
        $this->expectException(InputMismatchException::class);

        $websiteId = 1;
        $defaultStoreId = 1;
        $customerId = 1;
        $customerEmail = 'email@email.com';
        $hash = '4nj54lkj5jfi03j49f8bgujfgsd';

        $store = $this->createMock(Store::class);
        $store->expects($this->once())
            ->method('getId')
            ->willReturn($defaultStoreId);
        $website = $this->createMock(Website::class);
        $website->expects($this->atLeastOnce())
            ->method('getStoreIds')
            ->willReturn([1, 2, 3]);
        $website->expects($this->atMost(2))
            ->method('getDefaultStore')
            ->willReturn($store);
        $customer = $this->createMock(Customer::class);
        $customer->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($customerId);
        $customer->expects($this->once())
            ->method('getEmail')
            ->willReturn($customerEmail);
        $customer->expects($this->atLeastOnce())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $customer->method('getStoreId')
            ->willReturnCallback(fn () => $this->customerStoreId);
        $customer->expects($this->once())
            ->method('setStoreId')
            ->with($defaultStoreId)
            ->willReturnCallback(function ($storeId) use ($customer) {
                $this->customerStoreId = $storeId;
                return $customer;
            });

        $address = $this->createMock(AddressInterface::class);
        $address->expects($this->atLeastOnce())->method('getCountryId')->willReturn('US');
        $customer->expects($this->once())->method('getAddresses')->willReturn([$address]);
        $customer->expects($this->once())->method('setAddresses')->with(null);
        $addressModel = $this->createMock(Address::class);
        $this->addressFactory->expects($this->once())->method('create')->willReturn($addressModel);
        $addressModel->expects($this->once())->method('updateData')->with($address)->willReturnSelf();
        $validator = $this->createMock(\Magento\Framework\Validator::class);
        $this->validatorFactory->expects($this->once())
            ->method('createValidator')
            ->with('customer_address', 'save')
            ->willReturn($validator);
        $validator->expects($this->once())->method('isValid')->with($addressModel)->willReturn(true);

        $this->customerRepository
            ->expects($this->once())
            ->method('get')
            ->with($customerEmail)
            ->willReturn($customer);
        $this->share->method('isWebsiteScope')
            ->willReturn(true);
        $this->storeManager
            ->expects($this->atLeastOnce())
            ->method('getWebsite')
            ->with($websiteId)
            ->willReturn($website);
        $exception = new AlreadyExistsException(
            new Phrase('Exception message')
        );
        $this->customerRepository
            ->expects($this->once())
            ->method('save')
            ->with($customer, $hash)
            ->willThrowException($exception);

        $this->accountManagement->createAccountWithPasswordHash($customer, $hash);
    }

    /**
     * @return void
     */
    public function testCreateAccountWithPasswordHashWithLocalizedException(): void
    {
        $this->expectException(LocalizedException::class);

        $websiteId = 1;
        $defaultStoreId = 1;
        $customerId = 1;
        $customerEmail = 'email@email.com';
        $hash = '4nj54lkj5jfi03j49f8bgujfgsd';

        $store = $this->createMock(Store::class);
        $store->expects($this->once())
            ->method('getId')
            ->willReturn($defaultStoreId);
        $website = $this->createMock(Website::class);
        $website->method('getStoreIds')
            ->willReturn([1, 2, 3]);
        $website->expects($this->atMost(2))
            ->method('getDefaultStore')
            ->willReturn($store);
        $customer = $this->createMock(Customer::class);
        $customer->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($customerId);
        $customer->expects($this->once())
            ->method('getEmail')
            ->willReturn($customerEmail);
        $customer->expects($this->atLeastOnce())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $customer->method('getStoreId')
            ->willReturnCallback(fn () => $this->customerStoreId);
        $customer->expects($this->once())
            ->method('setStoreId')
            ->with($defaultStoreId)
            ->willReturnCallback(function ($storeId) use ($customer) {
                $this->customerStoreId = $storeId;
                return $customer;
            });

        $address = $this->createMock(AddressInterface::class);
        $address->expects($this->atLeastOnce())->method('getCountryId')->willReturn('US');
        $customer->expects($this->once())->method('getAddresses')->willReturn([$address]);
        $customer->expects($this->once())->method('setAddresses')->with(null);
        $addressModel = $this->createMock(Address::class);
        $this->addressFactory->expects($this->once())->method('create')->willReturn($addressModel);
        $addressModel->expects($this->once())->method('updateData')->with($address)->willReturnSelf();
        $validator = $this->createMock(\Magento\Framework\Validator::class);
        $this->validatorFactory->expects($this->once())
            ->method('createValidator')
            ->with('customer_address', 'save')
            ->willReturn($validator);
        $validator->expects($this->once())->method('isValid')->with($addressModel)->willReturn(true);

        $this->customerRepository
            ->expects($this->once())
            ->method('get')
            ->with($customerEmail)
            ->willReturn($customer);
        $this->share->method('isWebsiteScope')
            ->willReturn(true);
        $this->storeManager
            ->expects($this->atLeastOnce())
            ->method('getWebsite')
            ->with($websiteId)
            ->willReturn($website);
        $exception = new LocalizedException(
            new Phrase('Exception message')
        );
        $this->customerRepository
            ->expects($this->once())
            ->method('save')
            ->with($customer, $hash)
            ->willThrowException($exception);

        $this->accountManagement->createAccountWithPasswordHash($customer, $hash);
    }

    /**
     * @return void
     */
    public function testCreateAccountWithPasswordHashWithAddressException(): void
    {
        $this->expectException(InputException::class);

        $websiteId = 1;
        $defaultStoreId = 1;
        $customerId = 1;
        $customerEmail = 'email@email.com';
        $hash = '4nj54lkj5jfi03j49f8bgujfgsd';

        $store = $this->createMock(Store::class);
        $store->expects($this->once())
            ->method('getId')
            ->willReturn($defaultStoreId);
        $website = $this->createMock(Website::class);
        $website->method('getStoreIds')
            ->willReturn([1, 2, 3]);
        $website->expects($this->atMost(2))
            ->method('getDefaultStore')
            ->willReturn($store);
        $customer = $this->createMock(Customer::class);
        $customer->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($customerId);
        $customer->expects($this->once())
            ->method('getEmail')
            ->willReturn($customerEmail);
        $customer->expects($this->atLeastOnce())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $customer->method('getStoreId')
            ->willReturnCallback(fn () => $this->customerStoreId);
        $customer->expects($this->once())
            ->method('setStoreId')
            ->with($defaultStoreId)
            ->willReturnCallback(function ($storeId) use ($customer) {
                $this->customerStoreId = $storeId;
                return $customer;
            });

        $address = $this->createMock(AddressInterface::class);
        $address->expects($this->atLeastOnce())->method('getCountryId')->willReturn('US');
        $customer->expects($this->once())->method('getAddresses')->willReturn([$address]);
        $customer->expects($this->once())->method('setAddresses')->with(null);
        $addressModel = $this->createMock(Address::class);
        $this->addressFactory->expects($this->once())->method('create')->willReturn($addressModel);
        $addressModel->expects($this->once())->method('updateData')->with($address)->willReturnSelf();
        $validator = $this->createMock(\Magento\Framework\Validator::class);
        $this->validatorFactory->expects($this->once())
            ->method('createValidator')
            ->with('customer_address', 'save')
            ->willReturn($validator);
        $validator->expects($this->once())->method('isValid')->willReturn(false);
        $validator->expects($this->atLeastOnce())
            ->method('getMessages')
            ->willReturn([[new Phrase('Exception message')]]);

        $this->customerRepository
            ->expects($this->once())
            ->method('get')
            ->with($customerEmail)
            ->willReturn($customer);
        $this->share->method('isWebsiteScope')
            ->willReturn(true);
        $this->storeManager
            ->expects($this->atLeastOnce())
            ->method('getWebsite')
            ->with($websiteId)
            ->willReturn($website);

        $this->customerRepository->expects($this->never())->method('save');
        $this->addressRepository->expects($this->never())->method('save');
        $this->customerRepository->expects($this->never())->method('delete');

        $this->accountManagement->createAccountWithPasswordHash($customer, $hash);
    }

    /**
     * @return void
     */
    public function testCreateAccountWithPasswordHashWithNewCustomerAndLocalizedException(): void
    {
        $this->expectException(LocalizedException::class);

        $storeId = 1;
        $storeName = 'store_name';
        $websiteId = 1;
        $hash = '4nj54lkj5jfi03j49f8bgujfgsd';

        $customerMock = $this->createMock(Customer::class);

        $customerMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(null);
        $customerMock->expects($this->atLeastOnce())
            ->method('getStoreId')
            ->willReturn($storeId);
        $customerMock->expects($this->atLeastOnce())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $customerMock->expects($this->once())
            ->method('setCreatedIn')
            ->with($storeName)
            ->willReturnSelf();
        $customerMock->expects($this->once())
            ->method('getAddresses')
            ->willReturn([]);
        $customerMock->expects($this->once())
            ->method('setAddresses')
            ->with(null)
            ->willReturnSelf();
        $this->share
            ->expects($this->once())
            ->method('isWebsiteScope')
            ->willReturn(true);
        $website = $this->createMock(Website::class);
        $website->expects($this->once())
            ->method('getStoreIds')
            ->willReturn([1, 2, 3]);
        $this->storeManager
            ->expects($this->atLeastOnce())
            ->method('getWebsite')
            ->with($websiteId)
            ->willReturn($website);

        $storeMock = $this->createMock(Store::class);

        $storeMock->expects($this->once())
            ->method('getName')
            ->willReturn($storeName);

        $this->storeManager->expects($this->exactly(1))
            ->method('getStore')
            ->with($storeId)
            ->willReturn($storeMock);
        $exception = new LocalizedException(
            new Phrase('Exception message')
        );
        $this->customerRepository
            ->expects($this->once())
            ->method('save')
            ->with($customerMock, $hash)
            ->willThrowException($exception);

        $this->accountManagement->createAccountWithPasswordHash($customerMock, $hash);
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCreateAccountWithoutPassword(): void
    {
        $websiteId = 1;
        $defaultStoreId = 1;
        $customerId = 1;
        $customerEmail = 'email@email.com';
        $newLinkToken = '2jh43j5h2345jh23lh452h345hfuzasd96ofu';

        $datetime = $this->prepareDateTimeFactory();

        $store = $this->createMock(Store::class);
        $store->expects($this->once())
            ->method('getId')
            ->willReturn($defaultStoreId);
        $store->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $website = $this->createMock(Website::class);
        $website->expects($this->atLeastOnce())
            ->method('getStoreIds')
            ->willReturn([1, 2, 3]);
        $customer = $this->createMock(Customer::class);
        $testCase = $this;
        $customer->expects($this->any())
            ->method('getId')
            ->willReturnCallback(function () use ($testCase, $customerId) {
                if ($testCase->getIdCounter > 0) {
                    return $customerId;
                } else {
                    $testCase->getIdCounter += 1;
                    return null;
                }
            });
        $customer->expects($this->atLeastOnce())
            ->method('getEmail')
            ->willReturn($customerEmail);
        $customer->expects($this->any())
            ->method('getWebsiteId')
            ->willReturnCallback(function () use ($testCase, $websiteId) {
                if ($testCase->getWebsiteIdCounter > 1) {
                    return $websiteId;
                } else {
                    $testCase->getWebsiteIdCounter += 1;
                    return null;
                }
            });
        $customer->expects($this->once())
            ->method('setWebsiteId')
            ->with($websiteId);
        $customer->method('getStoreId')
            ->willReturnCallback(fn () => $this->customerStoreId);
        $customer->expects($this->once())
            ->method('setStoreId')
            ->with($defaultStoreId)
            ->willReturnCallback(function ($storeId) use ($customer) {
                $this->customerStoreId = $storeId;
                return $customer;
            });

        $address = $this->createMock(AddressInterface::class);
        $address->expects($this->once())->method('setCustomerId')->with($customerId);
        $customer->expects($this->once())->method('getAddresses')->willReturn([$address]);
        $customer->expects($this->once())->method('setAddresses')->with(null);
        $addressModel = $this->createMock(Address::class);
        $this->addressFactory->expects($this->once())->method('create')->willReturn($addressModel);
        $addressModel->expects($this->once())->method('updateData')->with($address)->willReturnSelf();
        $validator = $this->createMock(\Magento\Framework\Validator::class);
        $this->validatorFactory->expects($this->once())
            ->method('createValidator')
            ->with('customer_address', 'save')
            ->willReturn($validator);
        $validator->expects($this->once())->method('isValid')->with($addressModel)->willReturn(true);

        $this->share->method('isWebsiteScope')
            ->willReturn(true);
        $this->storeManager->expects($this->atLeastOnce())
            ->method('getWebsite')
            ->with($websiteId)
            ->willReturn($website);
        $this->storeManager->expects($this->atLeastOnce())
            ->method('getStore')
            ->willReturn($store);
        $this->customerRepository->expects($this->atLeastOnce())
            ->method('save')
            ->willReturn($customer);
        $this->addressRepository->expects($this->atLeastOnce())
            ->method('save')
            ->with($address);
        $this->customerRepository->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($customer);
        $this->random->expects($this->once())
            ->method('getUniqueHash')
            ->willReturn($newLinkToken);
        $customerSecure = $this->createPartialMockWithReflection(
            CustomerSecure::class,
            ['setRpToken', 'setRpTokenCreatedAt', 'getPasswordHash']
        );
        $customerSecure->expects($this->any())
            ->method('setRpToken')
            ->with($newLinkToken);
        $customerSecure->expects($this->any())
            ->method('setRpTokenCreatedAt')
            ->with($datetime)
            ->willReturnSelf();
        $customerSecure->expects($this->any())
            ->method('getPasswordHash')
            ->willReturn(null);
        $this->customerRegistry->expects($this->atLeastOnce())
            ->method('retrieveSecureData')
            ->willReturn($customerSecure);
        $this->emailNotificationMock->expects($this->once())
            ->method('newAccount')
            ->willReturnSelf();
        $address
            ->expects($this->atLeastOnce())
            ->method('getCountryId')
            ->willReturn('US');

        $this->accountManagement->createAccount($customer);
    }

    /**
     * Data provider for testCreateAccountWithPasswordInputException test.
     *
     * @return array
     */
    public static function dataProviderCheckPasswordStrength(): array
    {
        return [
            [
                'testNumber' => 1,
                'password' => 'qwer',
                'minPasswordLength' => 5,
                'minCharacterSetsNum' => 1
            ],
            [
                'testNumber' => 2,
                'password' => 'wrfewqedf1',
                'minPasswordLength' => 5,
                'minCharacterSetsNum' => 3
            ]
        ];
    }

    /**
     * @param int $testNumber
     * @param string $password
     * @param int $minPasswordLength
     * @param int $minCharacterSetsNum
     *
     * @return void
     * @throws LocalizedException
     */
    #[DataProvider('dataProviderCheckPasswordStrength')]
    public function testCreateAccountWithPasswordInputException(
        $testNumber,
        $password,
        $minPasswordLength,
        $minCharacterSetsNum
    ): void {
        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->willReturnMap(
                [
                    [
                        AccountManagement::XML_PATH_MINIMUM_PASSWORD_LENGTH,
                        'default',
                        null,
                        $minPasswordLength
                    ],
                    [
                        AccountManagement::XML_PATH_REQUIRED_CHARACTER_CLASSES_NUMBER,
                        'default',
                        null,
                        $minCharacterSetsNum
                    ]
                ]
            );

        $this->string->expects($this->any())
            ->method('strlen')
            ->with($password)
            ->willReturn(iconv_strlen($password, 'UTF-8'));

        if ($testNumber == 1) {
            $this->expectException(InputException::class);
            $this->expectExceptionMessage(
                'The password needs at least ' . $minPasswordLength . ' characters. '
                . 'Create a new password and try again.'
            );
        }

        if ($testNumber == 2) {
            $this->expectException(InputException::class);
            $this->expectExceptionMessage(
                'Minimum of different classes of characters in password is ' .
                $minCharacterSetsNum . '. Classes of characters: Lower Case, Upper Case, Digits, Special Characters.'
            );
        }

        $customer = $this->createMock(Customer::class);
        $customer->expects($this->atLeastOnce())
            ->method('getEmail')
            ->willReturn('email@email.com');
        $this->accountManagement->createAccount($customer, $password);
    }

    /**
     * @return void
     */
    public function testCreateAccountInputExceptionExtraLongPassword(): void
    {
        $password = '257*chars*************************************************************************************'
            . '****************************************************************************************************'
            . '***************************************************************';

        $this->string->expects($this->any())
            ->method('strlen')
            ->with($password)
            ->willReturn(iconv_strlen($password, 'UTF-8'));

        $this->expectException(InputException::class);
        $this->expectExceptionMessage('Please enter a password with at most 256 characters.');

        $customer = $this->createMock(Customer::class);
        $customer->expects($this->atLeastOnce())
            ->method('getEmail')
            ->willReturn('email@email.com');
        $this->accountManagement->createAccount($customer, $password);
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCreateAccountWithPassword(): void
    {
        $websiteId = 1;
        $defaultStoreId = 1;
        $customerId = 1;
        $customerEmail = 'email@email.com';
        $hash = '4nj54lkj5jfi03j49f8bgujfgsd';
        $newLinkToken = '2jh43j5h2345jh23lh452h345hfuzasd96ofu';
        $templateIdentifier = 'Template Identifier';
        $sender = 'Sender';
        $password = 'wrfewqedf1';
        $minPasswordLength = 5;
        $minCharacterSetsNum = 2;

        $datetime = $this->prepareDateTimeFactory();

        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->willReturnMap(
                [
                    [
                        AccountManagement::XML_PATH_MINIMUM_PASSWORD_LENGTH,
                        'default',
                        null,
                        $minPasswordLength,
                    ],
                    [
                        AccountManagement::XML_PATH_REQUIRED_CHARACTER_CLASSES_NUMBER,
                        'default',
                        null,
                        $minCharacterSetsNum,
                    ],
                    [
                        AccountManagement::XML_PATH_REGISTER_EMAIL_TEMPLATE,
                        ScopeInterface::SCOPE_STORE,
                        $defaultStoreId,
                        $templateIdentifier,
                    ],
                    [
                        AccountManagement::XML_PATH_REGISTER_EMAIL_IDENTITY,
                        ScopeInterface::SCOPE_STORE,
                        1,
                        $sender,
                    ],
                ]
            );
        $this->string->expects($this->any())
            ->method('strlen')
            ->with($password)
            ->willReturn(iconv_strlen($password, 'UTF-8'));
        $this->encryptor->expects($this->once())
            ->method('getHash')
            ->with($password, true)
            ->willReturn($hash);
        $store = $this->createMock(Store::class);
        $store->expects($this->once())
            ->method('getId')
            ->willReturn($defaultStoreId);
        $store->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $website = $this->createMock(Website::class);
        $website->expects($this->atLeastOnce())
            ->method('getStoreIds')
            ->willReturn([1, 2, 3]);
        $customer = $this->createMock(Customer::class);
        $testCase = $this;
        $customer->expects($this->any())
            ->method('getId')
            ->willReturnCallback(function () use ($testCase, $customerId) {
                if ($testCase->getIdCounter > 0) {
                    return $customerId;
                } else {
                    $testCase->getIdCounter += 1;
                    return null;
                }
            });
        $customer->expects($this->atLeastOnce())
            ->method('getEmail')
            ->willReturn($customerEmail);
        $customer->expects($this->any())
            ->method('getWebsiteId')
            ->willReturnCallback(function () use ($testCase, $websiteId) {
                if ($testCase->getWebsiteIdCounter > 1) {
                    return $websiteId;
                } else {
                    $testCase->getWebsiteIdCounter += 1;
                    return null;
                }
            });
        $customer->expects($this->once())
            ->method('setWebsiteId')
            ->with($websiteId);
        $customer->method('getStoreId')
            ->willReturnCallback(fn () => $this->customerStoreId);
        $customer->expects($this->once())
            ->method('setStoreId')
            ->with($defaultStoreId)
            ->willReturnCallback(function ($storeId) use ($customer) {
                $this->customerStoreId = $storeId;
                return $customer;
            });

        $address = $this->createMock(AddressInterface::class);
        $address->expects($this->once())->method('setCustomerId')->with($customerId);
        $customer->expects($this->once())->method('getAddresses')->willReturn([$address]);
        $customer->expects($this->once())->method('setAddresses')->with(null);
        $addressModel = $this->createMock(Address::class);
        $this->addressFactory->expects($this->once())->method('create')->willReturn($addressModel);
        $addressModel->expects($this->once())->method('updateData')->with($address)->willReturnSelf();
        $validator = $this->createMock(\Magento\Framework\Validator::class);
        $this->validatorFactory->expects($this->once())
            ->method('createValidator')
            ->with('customer_address', 'save')
            ->willReturn($validator);
        $validator->expects($this->once())->method('isValid')->with($addressModel)->willReturn(true);

        $this->share->method('isWebsiteScope')
            ->willReturn(true);
        $this->storeManager->expects($this->atLeastOnce())
            ->method('getWebsite')
            ->with($websiteId)
            ->willReturn($website);
        $this->storeManager->expects($this->atLeastOnce())
            ->method('getStore')
            ->willReturn($store);
        $this->customerRepository->expects($this->atLeastOnce())
            ->method('save')
            ->willReturn($customer);
        $this->customerRepository->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($customer);
        $this->addressRepository->expects($this->atLeastOnce())
            ->method('save')
            ->with($address);
        $this->random->expects($this->once())
            ->method('getUniqueHash')
            ->willReturn($newLinkToken);
        $customerSecure = $this->createPartialMockWithReflection(
            CustomerSecure::class,
            ['setRpToken', 'setRpTokenCreatedAt', 'getPasswordHash']
        );
        $customerSecure->expects($this->any())
            ->method('setRpToken')
            ->with($newLinkToken);
        $customerSecure->expects($this->any())
            ->method('setRpTokenCreatedAt')
            ->with($datetime)
            ->willReturnSelf();
        $customerSecure->expects($this->any())
            ->method('getPasswordHash')
            ->willReturn($hash);
        $this->customerRegistry->expects($this->atLeastOnce())
            ->method('retrieveSecureData')
            ->willReturn($customerSecure);
        $this->emailNotificationMock->expects($this->once())
            ->method('newAccount')
            ->willReturnSelf();
        $address
            ->expects($this->atLeastOnce())
            ->method('getCountryId')
            ->willReturn('US');
        $this->accountManagement->createAccount($customer, $password);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCreateAccountWithGroupId(): void
    {
        $websiteId = 1;
        $defaultStoreId = 1;
        $customerId = 1;
        $customerEmail = 'email@email.com';
        $hash = '4nj54lkj5jfi03j49f8bgujfgsd';
        $newLinkToken = '2jh43j5h2345jh23lh452h345hfuzasd96ofu';
        $templateIdentifier = 'Template Identifier';
        $sender = 'Sender';
        $password = 'wrfewqedf1';
        $minPasswordLength = 5;
        $minCharacterSetsNum = 2;
        $defaultGroupId = 1;

        $datetime = $this->prepareDateTimeFactory();

        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->willReturnMap(
                [
                    [
                        AccountManagement::XML_PATH_MINIMUM_PASSWORD_LENGTH,
                        'default',
                        null,
                        $minPasswordLength,
                    ],
                    [
                        AccountManagement::XML_PATH_REQUIRED_CHARACTER_CLASSES_NUMBER,
                        'default',
                        null,
                        $minCharacterSetsNum,
                    ],
                    [
                        AccountManagement::XML_PATH_REGISTER_EMAIL_TEMPLATE,
                        ScopeInterface::SCOPE_STORE,
                        $defaultStoreId,
                        $templateIdentifier,
                    ],
                    [
                        AccountManagement::XML_PATH_REGISTER_EMAIL_IDENTITY,
                        ScopeInterface::SCOPE_STORE,
                        1,
                        $sender,
                    ],
                ]
            );
        $this->string->expects($this->any())
            ->method('strlen')
            ->with($password)
            ->willReturn(iconv_strlen($password, 'UTF-8'));
        $this->encryptor->expects($this->once())
            ->method('getHash')
            ->with($password, true)
            ->willReturn($hash);
        $store = $this->createMock(Store::class);
        $store->expects($this->once())
            ->method('getId')
            ->willReturn($defaultStoreId);
        $store->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $website = $this->createMock(Website::class);
        $website->expects($this->atLeastOnce())
            ->method('getStoreIds')
            ->willReturn([1, 2, 3]);
        $customer = $this->createMock(Customer::class);
        $testCase = $this;
        $customer->expects($this->any())
            ->method('getId')
            ->willReturnCallback(function () use ($testCase, $customerId) {
                if ($testCase->getIdCounter > 0) {
                    return $customerId;
                } else {
                    $testCase->getIdCounter += 1;
                    return null;
                }
            });
        $customer
            ->method('setGroupId')
            ->willReturnOnConsecutiveCalls(null, $defaultGroupId);
        $customer->expects($this->atLeastOnce())
            ->method('getEmail')
            ->willReturn($customerEmail);
        $customer->expects($this->any())
            ->method('getWebsiteId')
            ->willReturnCallback(function () use ($testCase, $websiteId) {
                if ($testCase->getWebsiteIdCounter > 1) {
                    return $websiteId;
                } else {
                    $testCase->getWebsiteIdCounter += 1;
                    return null;
                }
            });
        $customer->expects($this->once())
            ->method('setWebsiteId')
            ->with($websiteId);
        $customer->method('getStoreId')
            ->willReturnCallback(fn () => $this->customerStoreId);
        $customer->expects($this->once())
            ->method('setStoreId')
            ->with($defaultStoreId)
            ->willReturnCallback(function ($storeId) use ($customer) {
                $this->customerStoreId = $storeId;
                return $customer;
            });

        $address = $this->createMock(AddressInterface::class);
        $address->expects($this->once())->method('setCustomerId')->with($customerId);
        $customer->expects($this->once())->method('getAddresses')->willReturn([$address]);
        $customer->expects($this->once())->method('setAddresses')->with(null);
        $addressModel = $this->createMock(Address::class);
        $this->addressFactory->expects($this->once())->method('create')->willReturn($addressModel);
        $addressModel->expects($this->once())->method('updateData')->with($address)->willReturnSelf();
        $validator = $this->createMock(\Magento\Framework\Validator::class);
        $this->validatorFactory->expects($this->once())
            ->method('createValidator')
            ->with('customer_address', 'save')
            ->willReturn($validator);
        $validator->expects($this->once())->method('isValid')->with($addressModel)->willReturn(true);

        $this->share->method('isWebsiteScope')
            ->willReturn(true);
        $this->storeManager->expects($this->atLeastOnce())
            ->method('getWebsite')
            ->with($websiteId)
            ->willReturn($website);
        $this->storeManager->expects($this->atLeastOnce())
            ->method('getStore')
            ->willReturn($store);
        $this->customerRepository->expects($this->atLeastOnce())
            ->method('save')
            ->willReturn($customer);
        $this->customerRepository->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($customer);
        $this->addressRepository->expects($this->atLeastOnce())
            ->method('save')
            ->with($address);
        $this->random->expects($this->once())
            ->method('getUniqueHash')
            ->willReturn($newLinkToken);
        $customerSecure = $this->createPartialMockWithReflection(
            CustomerSecure::class,
            ['setRpToken', 'setRpTokenCreatedAt', 'getPasswordHash']
        );
        $customerSecure->expects($this->any())
            ->method('setRpToken')
            ->with($newLinkToken);
        $customerSecure->expects($this->any())
            ->method('setRpTokenCreatedAt')
            ->with($datetime)
            ->willReturnSelf();
        $customerSecure->expects($this->any())
            ->method('getPasswordHash')
            ->willReturn($hash);
        $this->customerRegistry->expects($this->atLeastOnce())
            ->method('retrieveSecureData')
            ->willReturn($customerSecure);
        $this->emailNotificationMock->expects($this->once())
            ->method('newAccount')
            ->willReturnSelf();
        $address
            ->expects($this->atLeastOnce())
            ->method('getCountryId')
            ->willReturn('US');
        $this->accountManagement->createAccount($customer, $password);
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testSendPasswordReminderEmail(): void
    {
        $customerId = 1;
        $customerStoreId = 2;
        $customerEmail = 'email@email.com';
        $customerData = ['key' => 'value'];
        $customerName = 'Customer Name';
        $templateIdentifier = 'Template Identifier';
        $sender = 'Sender';

        $customer = $this->createMock(Customer::class);
        $customer->expects($this->any())
            ->method('getStoreId')
            ->willReturn($customerStoreId);
        $customer->expects($this->any())
            ->method('getId')
            ->willReturn($customerId);
        $customer->expects($this->any())
            ->method('getEmail')
            ->willReturn($customerEmail);

        $this->store->expects($this->any())
            ->method('getId')
            ->willReturn($customerStoreId);

        $this->storeManager
            ->method('getStore')
            ->willReturnCallback(function ($arg1) use ($customerStoreId) {
                if (empty($arg1) || $arg1 == $customerStoreId) {
                    return $this->store;
                }
            });

        $this->customerRegistry->expects($this->once())
            ->method('retrieveSecureData')
            ->with($customerId)
            ->willReturn($this->customerSecure);

        $this->dataObjectProcessor->expects($this->once())
            ->method('buildOutputDataArray')
            ->with($customer, CustomerInterface::class)
            ->willReturn($customerData);

        $this->customerViewHelper->expects($this->any())
            ->method('getCustomerName')
            ->with($customer)
            ->willReturn($customerName);

        $this->customerSecure->expects($this->once())
            ->method('addData')
            ->with($customerData)
            ->willReturnSelf();
        $this->customerSecure->expects($this->once())
            ->method('setData')
            ->with('name', $customerName)
            ->willReturnSelf();

        $this->scopeConfig->method('getValue')
            ->willReturnCallback(function (...$args) use (&$callCount, $templateIdentifier, $sender, $customerStoreId) {
                $callCount++;

                switch ($callCount) {
                    case 1:
                        $expectedArgs1 = [
                            AccountManagement::XML_PATH_REMIND_EMAIL_TEMPLATE,
                            ScopeInterface::SCOPE_STORE,
                            $customerStoreId
                        ];
                        if ($args === $expectedArgs1) {
                            return $templateIdentifier;
                        }
                        break;
                    case 2:
                        $expectedArgs2 = [
                            AccountManagement::XML_PATH_FORGOT_EMAIL_IDENTITY,
                            ScopeInterface::SCOPE_STORE,
                            $customerStoreId
                        ];
                        if ($args === $expectedArgs2) {
                            return $sender;
                        }
                        break;

                }
            });

        $transport = $this->createMock(TransportInterface::class);

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
            ->with(['customer' => $this->customerSecure, 'store' => $this->store])
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

        $this->assertEquals($this->accountManagement, $this->accountManagement->sendPasswordReminderEmail($customer));
    }

    /**
     * @param string $email
     * @param string $templateIdentifier
     * @param string $sender
     * @param int $storeId
     * @param int $customerId
     * @param string $hash
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function prepareInitiatePasswordReset(
        $email,
        $templateIdentifier,
        $sender,
        $storeId,
        $customerId,
        $hash
    ): void {
        $websiteId = 1;
        $addressId = 5;
        $datetime = $this->prepareDateTimeFactory();
        $customerData = ['key' => 'value'];
        $customerName = 'Customer Name';

        $this->store->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $this->store->expects($this->any())
            ->method('getId')
            ->willReturn($storeId);
        $this->storeManager->expects($this->any())
            ->method('getStore')
            ->willReturn($this->store);

        /** @var Address|MockObject $addressModel */
        $addressModel = $this->createPartialMockWithReflection(
            Address::class,
            ['setShouldIgnoreValidation']
        );

        /** @var AddressInterface|MockObject $customer */
        $address = $this->createMock(AddressInterface::class);
        $address->expects($this->once())
            ->method('getId')
            ->willReturn($addressId);

        /** @var Customer|MockObject $customer */
        $customer = $this->createMock(Customer::class);
        $customer->expects($this->any())
            ->method('getEmail')
            ->willReturn($email);
        $customer->expects($this->any())
            ->method('getId')
            ->willReturn($customerId);
        $customer->expects($this->any())
            ->method('getStoreId')
            ->willReturn($storeId);
        $customer->expects($this->any())
            ->method('getAddresses')
            ->willReturn([$address]);
        $this->customerRepository->expects($this->once())
            ->method('get')
            ->willReturn($customer);
        $this->addressRegistryMock->expects($this->once())
            ->method('retrieve')
            ->with($addressId)
            ->willReturn($addressModel);
        $addressModel->expects($this->once())
            ->method('setShouldIgnoreValidation')
            ->with(true);
        $this->customerRepository->expects($this->once())
            ->method('get')
            ->with($email, $websiteId)
            ->willReturn($customer);
        $this->customerRepository->expects($this->once())
            ->method('save')
            ->with($customer)
            ->willReturnSelf();
        $this->random->expects($this->once())
            ->method('getUniqueHash')
            ->willReturn($hash);
        $this->customerViewHelper->expects($this->any())
            ->method('getCustomerName')
            ->with($customer)
            ->willReturn($customerName);
        $this->customerSecure->expects($this->any())
            ->method('setRpToken')
            ->with($hash)
            ->willReturnSelf();
        $this->customerSecure->expects($this->any())
            ->method('setRpTokenCreatedAt')
            ->with($datetime)
            ->willReturnSelf();
        $this->customerSecure->expects($this->any())
            ->method('addData')
            ->with($customerData)
            ->willReturnSelf();
        $this->customerSecure->expects($this->any())
            ->method('setData')
            ->with('name', $customerName)
            ->willReturnSelf();
        $this->customerRegistry->expects($this->any())
            ->method('retrieveSecureData')
            ->with($customerId)
            ->willReturn($this->customerSecure);
        $this->dataObjectProcessor->expects($this->any())
            ->method('buildOutputDataArray')
            ->with($customer, Customer::class)
            ->willReturn($customerData);

        $this->prepareEmailSend($email, $templateIdentifier, $sender, $storeId, $customerName);
    }

    /**
     * @param string $email
     * @param int $templateIdentifier
     * @param string $sender
     * @param int $storeId
     * @param string $customerName
     *
     * @return void
     */
    protected function prepareEmailSend($email, $templateIdentifier, $sender, $storeId, $customerName): void
    {
        $transport = $this->createMock(TransportInterface::class);

        $this->transportBuilder->expects($this->any())
            ->method('setTemplateIdentifier')
            ->with($templateIdentifier)
            ->willReturnSelf();
        $this->transportBuilder->expects($this->any())
            ->method('setTemplateOptions')
            ->with(['area' => Area::AREA_FRONTEND, 'store' => $storeId])
            ->willReturnSelf();
        $this->transportBuilder->expects($this->any())
            ->method('setTemplateVars')
            ->with(['customer' => $this->customerSecure, 'store' => $this->store])
            ->willReturnSelf();
        $this->transportBuilder->expects($this->any())
            ->method('setFrom')
            ->with($sender)
            ->willReturnSelf();
        $this->transportBuilder->expects($this->any())
            ->method('addTo')
            ->with($email, $customerName)
            ->willReturnSelf();
        $this->transportBuilder->expects($this->any())
            ->method('getTransport')
            ->willReturn($transport);

        $transport->expects($this->any())
            ->method('sendMessage');
    }

    /**
     * @return void
     */
    public function testInitiatePasswordResetEmailReminder(): void
    {
        $customerId = 1;

        $email = 'test@example.com';
        $template = AccountManagement::EMAIL_REMINDER;
        $templateIdentifier = 'Template Identifier';
        $sender = 'Sender';

        $storeId = 1;

        $hash = hash("sha256", uniqid(microtime() . random_int(0, PHP_INT_MAX), true));

        $this->emailNotificationMock->expects($this->once())
            ->method('passwordReminder')
            ->willReturnSelf();

        $this->prepareInitiatePasswordReset($email, $templateIdentifier, $sender, $storeId, $customerId, $hash);

        $this->assertTrue($this->accountManagement->initiatePasswordReset($email, $template));
    }

    /**
     * @return void
     */
    public function testInitiatePasswordResetEmailReset(): void
    {
        $storeId = 1;
        $customerId = 1;

        $email = 'test@example.com';
        $template = AccountManagement::EMAIL_RESET;
        $templateIdentifier = 'Template Identifier';
        $sender = 'Sender';

        $hash = hash("sha256", uniqid(microtime() . random_int(0, PHP_INT_MAX), true));

        $this->emailNotificationMock->expects($this->once())
            ->method('passwordResetConfirmation')
            ->willReturnSelf();

        $this->prepareInitiatePasswordReset($email, $templateIdentifier, $sender, $storeId, $customerId, $hash);

        $this->assertTrue($this->accountManagement->initiatePasswordReset($email, $template));
    }

    /**
     * @return void
     */
    public function testInitiatePasswordResetNoTemplate(): void
    {
        $storeId = 1;
        $customerId = 1;

        $email = 'test@example.com';
        $template = null;
        $templateIdentifier = 'Template Identifier';
        $sender = 'Sender';

        $hash = hash("sha256", uniqid(microtime() . random_int(0, PHP_INT_MAX), true));

        $this->prepareInitiatePasswordReset($email, $templateIdentifier, $sender, $storeId, $customerId, $hash);

        $this->expectException(InputException::class);
        $this->expectExceptionMessage(
            'Invalid value of "" provided for the template field. Possible values: email_reminder or email_reset.'
        );
        $this->accountManagement->initiatePasswordReset($email, $template);
    }

    /**
     * @return void
     */
    public function testValidateResetPasswordTokenBadCustomerId(): void
    {
        $this->expectException(InputException::class);
        $this->expectExceptionMessage('Invalid value of "0" provided for the customerId field');

        $this->accountManagement->validateResetPasswordLinkToken(0, '');
    }

    /**
     * @return void
     */
    public function testValidateResetPasswordTokenBadResetPasswordLinkToken(): void
    {
        $this->expectException(InputException::class);
        $this->expectExceptionMessage('"resetPasswordLinkToken" is required. Enter and try again.');

        $this->accountManagement->validateResetPasswordLinkToken(22, '');
    }

    /**
     * @return void
     */
    public function testValidateResetPasswordTokenTokenMismatch(): void
    {
        $this->expectException(InputMismatchException::class);
        $this->expectExceptionMessage('The password token is mismatched. Reset and try again.');

        $this->customerRegistry->expects($this->atLeastOnce())
            ->method('retrieveSecureData')
            ->willReturn($this->customerSecure);

        $this->accountManagement->validateResetPasswordLinkToken(22, 'newStringToken');
    }

    /**
     * @return void
     */
    public function testValidateResetPasswordTokenTokenExpired(): void
    {
        $this->expectException(ExpiredException::class);
        $this->expectExceptionMessage('The password token is expired. Reset and try again.');

        $this->reInitModel();
        $this->customerRegistry->expects($this->atLeastOnce())
            ->method('retrieveSecureData')
            ->willReturn($this->customerSecure);

        $this->accountManagement->validateResetPasswordLinkToken(22, 'newStringToken');
    }

    /**
     * @return void
     */
    public function testValidateResetPasswordToken(): void
    {
        $this->reInitModel();

        $this->customer
            ->expects($this->once())
            ->method('getResetPasswordLinkExpirationPeriod')
            ->willReturn(100000);

        $this->customerRegistry->expects($this->atLeastOnce())
            ->method('retrieveSecureData')
            ->willReturn($this->customerSecure);

        $this->assertTrue($this->accountManagement->validateResetPasswordLinkToken(22, 'newStringToken'));
    }

    /**
     * reInit $this->accountManagement object.
     *
     * @return void
     */
    private function reInitModel(): void
    {
        $this->customerSecure = $this->createPartialMockWithReflection(
            CustomerSecure::class,
            [
                'getRpToken',
                'getRpTokenCreatedAt',
                'getPasswordHash',
                'setPasswordHash',
                'setRpToken',
                'setRpTokenCreatedAt',
                'setFailuresNum',
                'setFirstFailure',
                'setLockExpires',
            ]
        );
        $this->customerSecure->expects($this->any())
            ->method('getRpToken')
            ->willReturn('newStringToken');
        $pastDateTime = '2016-10-25 00:00:00';
        $this->customerSecure->expects($this->any())
            ->method('getRpTokenCreatedAt')
            ->willReturn($pastDateTime);
        $this->customer = $this->getMockBuilder(\Magento\Customer\Model\Customer::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getResetPasswordLinkExpirationPeriod'])
            ->getMock();

        $this->prepareDateTimeFactory();
        $this->sessionManager = $this->createMock(SessionManagerInterface::class);
        $this->visitorCollectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->saveHandler = $this->createMock(SaveHandlerInterface::class);

        $dateTime = '2017-10-25 18:57:08';
        $timestamp = 1508983028;
        $dateTimeMock = $this->getMockBuilder(\DateTime::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['format', 'getTimestamp', 'setTimestamp'])
            ->getMock();

        $dateTimeMock->expects($this->any())
            ->method('format')
            ->with(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT)
            ->willReturn($dateTime);
        $dateTimeMock->expects($this->any())
            ->method('getTimestamp')
            ->willReturn($timestamp);
        $dateTimeMock->expects($this->any())
            ->method('setTimestamp')
            ->willReturnSelf();
        $dateTimeFactory = $this->getMockBuilder(DateTimeFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $dateTimeFactory->expects($this->any())->method('create')->willReturn($dateTimeMock);
        $this->sessionCleanerMock = $this->createMock(SessionCleanerInterface::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->accountManagement = $this->objectManagerHelper->getObject(
            AccountManagement::class,
            [
                'customerFactory' => $this->customerFactory,
                'customerRegistry' => $this->customerRegistry,
                'customerRepository' => $this->customerRepository,
                'customerModel' => $this->customer,
                'dateTimeFactory' => $dateTimeFactory,
                'stringHelper' => $this->string,
                'scopeConfig' => $this->scopeConfig,
                'sessionManager' => $this->sessionManager,
                'visitorCollectionFactory' => $this->visitorCollectionFactory,
                'saveHandler' => $this->saveHandler,
                'encryptor' => $this->encryptor,
                'dataProcessor' => $this->dataObjectProcessor,
                'storeManager' => $this->storeManager,
                'addressRegistry' => $this->addressRegistryMock,
                'transportBuilder' => $this->transportBuilder,
                'sessionCleaner' => $this->sessionCleanerMock
            ]
        );
        $this->objectManagerHelper->setBackwardCompatibleProperty(
            $this->accountManagement,
            'authentication',
            $this->authenticationMock
        );
    }

    /**
     * @return void
     * @throws LocalizedException
     * @throws InvalidEmailOrPasswordException
     *
     */
    public function testChangePassword(): void
    {
        $customerId = 7;
        $email = 'test@example.com';
        $currentPassword = '1234567';
        $newPassword = 'abcdefg';
        $passwordHash = '1a2b3f4c';

        $this->reInitModel();
        $customer = $this->createMock(CustomerInterface::class);
        $customer->expects($this->any())
            ->method('getId')
            ->willReturn($customerId);
        $customer->expects($this->once())
            ->method('getAddresses')
            ->willReturn([]);

        $this->customerRepository
            ->expects($this->once())
            ->method('get')
            ->with($email)
            ->willReturn($customer);

        $this->authenticationMock->expects($this->once())
            ->method('authenticate');

        $this->customerSecure->expects($this->once())
            ->method('setRpToken')
            ->with(null);
        $this->customerSecure->expects($this->once())
            ->method('setRpTokenCreatedAt')
            ->willReturnSelf();
        $this->customerSecure->expects($this->any())
            ->method('getPasswordHash')
            ->willReturn($passwordHash);

        $this->customerRegistry->expects($this->any())
            ->method('retrieveSecureData')
            ->with($customerId)
            ->willReturn($this->customerSecure);

        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->willReturnMap(
                [
                    [
                        AccountManagement::XML_PATH_MINIMUM_PASSWORD_LENGTH,
                        'default',
                        null,
                        7
                    ],
                    [
                        AccountManagement::XML_PATH_REQUIRED_CHARACTER_CLASSES_NUMBER,
                        'default',
                        null,
                        1
                    ]
                ]
            );
        $this->string->expects($this->any())
            ->method('strlen')
            ->with($newPassword)
            ->willReturn(7);

        $this->sessionCleanerMock->expects($this->once())->method('clearFor')->with($customerId)->willReturnSelf();

        $this->customerRepository
            ->expects($this->once())
            ->method('save')
            ->with($customer);

        $this->assertTrue($this->accountManagement->changePassword($email, $currentPassword, $newPassword));
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    public function testResetPassword(): void
    {
        $customerEmail = 'customer@example.com';
        $customerId = '1';
        $addressId = 5;
        $resetToken = 'newStringToken';
        $newPassword = 'new_password';

        $this->reInitModel();
        /** @var Address|MockObject $addressModel */
        $addressModel = $this->createPartialMockWithReflection(
            Address::class,
            ['setShouldIgnoreValidation']
        );

        /** @var AddressInterface|MockObject $customer */
        $address = $this->createMock(AddressInterface::class);
        $address->expects($this->any())
            ->method('getId')
            ->willReturn($addressId);

        /** @var Customer|MockObject $customer */
        $customer = $this->createMock(Customer::class);
        $customer->expects($this->any())->method('getId')->willReturn($customerId);
        $customer->expects($this->any())
            ->method('getAddresses')
            ->willReturn([$address]);
        $this->addressRegistryMock->expects($this->once())
            ->method('retrieve')
            ->with($addressId)
            ->willReturn($addressModel);
        $addressModel->expects($this->once())
            ->method('setShouldIgnoreValidation')
            ->with(true);
        $this->customerRepository->expects($this->atLeastOnce())->method('get')->with($customerEmail)
            ->willReturn($customer);
        $this->customer->expects($this->atLeastOnce())->method('getResetPasswordLinkExpirationPeriod')
            ->willReturn(100000);
        $this->string->expects($this->any())->method('strlen')->willReturnCallback(
            function ($string) {
                return strlen($string);
            }
        );
        $this->customerRegistry->expects($this->atLeastOnce())->method('retrieveSecureData')
            ->willReturn($this->customerSecure);

        $this->customerSecure->expects($this->once())->method('setRpToken')->with(null);
        $this->customerSecure->expects($this->once())->method('setRpTokenCreatedAt')->with(null);
        $this->customerSecure->expects($this->any())->method('setPasswordHash')->willReturn(null);
        $this->customerSecure->expects($this->once())->method('setFailuresNum')->with(0);
        $this->customerSecure->expects($this->once())->method('setFirstFailure')->with(null);
        $this->customerSecure->expects($this->once())->method('setLockExpires')->with(null);
        $this->sessionCleanerMock->expects($this->once())->method('clearFor')->with($customerId)->willReturnSelf();

        $this->assertTrue($this->accountManagement->resetPassword($customerEmail, $resetToken, $newPassword));
    }

    /**
     * @return void
     * @throws InvalidEmailOrPasswordException
     * @throws LocalizedException
     */
    public function testChangePasswordException(): void
    {
        $email = 'test@example.com';
        $currentPassword = '1234567';
        $newPassword = 'abcdefg';

        $exception = new NoSuchEntityException(
            new Phrase('Exception message')
        );
        $this->customerRepository
            ->expects($this->once())
            ->method('get')
            ->with($email)
            ->willThrowException($exception);

        $this->expectException(InvalidEmailOrPasswordException::class);
        $this->expectExceptionMessage('Invalid login or password.');

        $this->accountManagement->changePassword($email, $currentPassword, $newPassword);
    }

    /**
     * @param int $isConfirmationRequired
     * @param string|null $confirmation
     * @param string $expected
     *
     * @return void
     * @throws LocalizedException
     */
    #[DataProvider('dataProviderGetConfirmationStatus')]
    public function testGetConfirmationStatus(
        $isConfirmationRequired,
        $confirmation,
        $expected
    ): void {
        $websiteId = 1;
        $customerId = 1;
        $customerEmail = 'test1@example.com';

        $customerMock = $this->createMock(Customer::class);
        $customerMock->expects($this->once())
            ->method('getId')
            ->willReturn($customerId);
        $customerMock->expects($this->any())
            ->method('getConfirmation')
            ->willReturn($confirmation);
        $customerMock->expects($this->once())
            ->method('getEmail')
            ->willReturn($customerEmail);
        $customerMock->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn($websiteId);

        $this->accountConfirmation->expects($this->once())
            ->method('isConfirmationRequired')
            ->with($websiteId, $customerId, $customerEmail)
            ->willReturn((bool)$isConfirmationRequired);

        $this->customerRepository->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($customerMock);

        $this->assertEquals($expected, $this->accountManagement->getConfirmationStatus($customerId));
    }

    /**
     * @return array
     */
    public static function dataProviderGetConfirmationStatus(): array
    {
        return [
            [0, null, AccountManagement::ACCOUNT_CONFIRMATION_NOT_REQUIRED],
            [0, null, AccountManagement::ACCOUNT_CONFIRMATION_NOT_REQUIRED],
            [0, null, AccountManagement::ACCOUNT_CONFIRMATION_NOT_REQUIRED],
            [1, null, AccountManagement::ACCOUNT_CONFIRMED],
            [1, 'test', AccountManagement::ACCOUNT_CONFIRMATION_REQUIRED]
        ];
    }

    /**
     * @return void
     */
    public function testCreateAccountWithPasswordHashForGuestException(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Exception message');

        $storeId = 1;
        $websiteId = 1;
        $hash = '4nj54lkj5jfi03j49f8bgujfgsd';

        $storeMock = $this->createMock(Store::class);
        $storeMock->method('getId')
            ->willReturn($storeId);
        $this->storeManager->method('getStores')
            ->willReturn([$storeMock]);

        $customerMock = $this->createMock(Customer::class);
        $customerMock->method('getStoreId')->willReturn($storeId);
        $customerMock->method('getWebsiteId')->willReturn($websiteId);
        $customerMock->method('getId')->willReturnOnConsecutiveCalls(null, 1);

        $this->customerRepository
            ->expects($this->once())
            ->method('save')
            ->with($customerMock, $hash)
            ->willThrowException(new LocalizedException(__('Exception message')));

        $this->accountManagement->createAccountWithPasswordHash($customerMock, $hash);
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCreateAccountWithPasswordHashWithCustomerAddresses(): void
    {
        $websiteId = 1;
        $addressId = 2;
        $customerId = null;
        $storeId = 1;
        $hash = '4nj54lkj5jfi03j49f8bgujfgsd';

        $this->prepareDateTimeFactory();

        //Handle store
        $store = $this->createMock(Store::class);
        $store->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn($websiteId);

        //Handle address - existing and non-existing. Non-Existing should return null when call getId method
        $existingAddress = $this->createMock(AddressInterface::class);
        $existingAddress->expects($this->atLeastOnce())
            ->method('getCountryId')
            ->willReturn('US');
        $nonExistingAddress = $this->createMock(AddressInterface::class);
        $nonExistingAddress->expects($this->atLeastOnce())
            ->method('getCountryId')
            ->willReturn('US');
        //Ensure that existing address is not in use
        $this->addressRepository->expects($this->exactly(2))
            ->method('save')
            ->willReturnArgument(0);

        $existingAddress
            ->expects($this->any())
            ->method("getId")
            ->willReturn($addressId);
        //Expects that id for existing address should be unset
        $existingAddress
            ->expects($this->once())
            ->method("setId")
            ->with(null);
        //Handle Customer calls
        $customer = $this->createMock(Customer::class);
        $customer
            ->expects($this->atLeastOnce())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $customer
            ->expects($this->atLeastOnce())
            ->method('getStoreId')
            ->willReturn($storeId);
        $customer
            ->expects($this->any())
            ->method("getId")
            ->willReturn($customerId);
        //Return Customer from customer repository
        $this->customerRepository
            ->expects($this->atLeastOnce())
            ->method('save')
            ->willReturn($customer);
        $this->customerRepository
            ->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($customer);
        $customerSecure = $this->createPartialMockWithReflection(
            CustomerSecure::class,
            ['setRpToken', 'setRpTokenCreatedAt', 'getPasswordHash']
        );
        $customerSecure->expects($this->once())
            ->method('setRpToken')
            ->with($hash);

        $customerSecure->expects($this->any())
            ->method('getPasswordHash')
            ->willReturn($hash);

        $this->customerRegistry->expects($this->any())
            ->method('retrieveSecureData')
            ->with($customerId)
            ->willReturn($customerSecure);

        $this->random->expects($this->once())
            ->method('getUniqueHash')
            ->willReturn($hash);

        $customer->expects($this->atLeastOnce())
            ->method('getAddresses')
            ->willReturn([$existingAddress, $nonExistingAddress]);
        $customer->expects($this->once())->method('setAddresses')->with(null);
        $existingAddressModel = $this->createMock(Address::class);
        $nonExistingAddressModel = $this->createMock(Address::class);
        $this->addressFactory->expects($this->exactly(2))
            ->method('create')
            ->willReturnOnConsecutiveCalls($existingAddressModel, $nonExistingAddressModel);
        $existingAddressModel->expects($this->once())
            ->method('updateData')
            ->with($existingAddress)
            ->willReturnSelf();
        $nonExistingAddressModel->expects($this->once())
            ->method('updateData')
            ->with($nonExistingAddress)
            ->willReturnSelf();
        $validator = $this->createMock(\Magento\Framework\Validator::class);
        $this->validatorFactory->expects($this->once())
            ->method('createValidator')
            ->with('customer_address', 'save')
            ->willReturn($validator);
        $validator->expects($this->exactly(2))->method('isValid')->willReturn(true);

        $this->storeManager
            ->expects($this->atLeastOnce())
            ->method('getStore')
            ->willReturn($store);
        $this->share
            ->expects($this->atLeastOnce())
            ->method('isWebsiteScope')
            ->willReturn(true);
        $website = $this->createMock(Website::class);
        $website->expects($this->once())
            ->method('getStoreIds')
            ->willReturn([1, 2, 3]);
        $this->storeManager
            ->expects($this->atLeastOnce())
            ->method('getWebsite')
            ->with($websiteId)
            ->willReturn($website);

        $this->assertSame($customer, $this->accountManagement->createAccountWithPasswordHash($customer, $hash));
    }

    /**
     * @return string
     */
    private function prepareDateTimeFactory(): string
    {
        $dateTime = '2017-10-25 18:57:08';
        $timestamp = 1508983028;
        $dateTimeMock = $this->createMock(\DateTime::class);
        $dateTimeMock->expects($this->any())
            ->method('format')
            ->with(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT)
            ->willReturn($dateTime);

        $dateTimeMock
            ->expects($this->any())
            ->method('getTimestamp')
            ->willReturn($timestamp);

        $this->dateTimeFactory
            ->expects($this->any())
            ->method('create')
            ->willReturn($dateTimeMock);

        return $dateTime;
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return void
     * @throws LocalizedException
     */
    public function testCreateAccountUnexpectedValueException(): void
    {
        $websiteId = 1;
        $defaultStoreId = 1;
        $customerId = 1;
        $customerEmail = 'email@email.com';
        $newLinkToken = '2jh43j5h2345jh23lh452h345hfuzasd96ofu';
        $exception = new \UnexpectedValueException('Template file was not found');

        $datetime = $this->prepareDateTimeFactory();

        $store = $this->createMock(Store::class);
        $store->expects($this->once())
            ->method('getId')
            ->willReturn($defaultStoreId);
        $store->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $website = $this->createMock(Website::class);
        $website->method('getStoreIds')
            ->willReturn([1, 2, 3]);
        $customer = $this->createMock(Customer::class);
        $testCase = $this;
        $customer->expects($this->any())
            ->method('getId')
            ->willReturnCallback(function () use ($testCase, $customerId) {
                if ($testCase->getIdCounter > 0) {
                    return $customerId;
                } else {
                    $testCase->getIdCounter += 1;
                    return null;
                }
            });
        $customer->expects($this->atLeastOnce())
            ->method('getEmail')
            ->willReturn($customerEmail);
        $customer->expects($this->any())
            ->method('getWebsiteId')
            ->willReturnCallback(function () use ($testCase, $websiteId) {
                if ($testCase->getWebsiteIdCounter > 1) {
                    return $websiteId;
                } else {
                    $testCase->getWebsiteIdCounter += 1;
                    return null;
                }
            });
        $customer->expects($this->once())
            ->method('setWebsiteId')
            ->with($websiteId);
        $customer->method('getStoreId')
            ->willReturnCallback(fn () => $this->customerStoreId);
        $customer->expects($this->once())
            ->method('setStoreId')
            ->with($defaultStoreId)
            ->willReturnCallback(function ($storeId) use ($customer) {
                $this->customerStoreId = $storeId;
                return $customer;
            });

        $address = $this->createMock(AddressInterface::class);
        $address->expects($this->once())->method('setCustomerId')->with($customerId);
        $customer->expects($this->once())->method('getAddresses')->willReturn([$address]);
        $customer->expects($this->once())->method('setAddresses')->with(null);
        $addressModel = $this->createMock(Address::class);
        $this->addressFactory->expects($this->once())->method('create')->willReturn($addressModel);
        $addressModel->expects($this->once())->method('updateData')->with($address)->willReturnSelf();
        $validator = $this->createMock(\Magento\Framework\Validator::class);
        $this->validatorFactory->expects($this->once())
            ->method('createValidator')
            ->with('customer_address', 'save')
            ->willReturn($validator);
        $validator->expects($this->once())->method('isValid')->with($addressModel)->willReturn(true);

        $this->share->method('isWebsiteScope')
            ->willReturn(true);
        $this->storeManager->expects($this->atLeastOnce())
            ->method('getWebsite')
            ->with($websiteId)
            ->willReturn($website);
        $this->storeManager->expects($this->atLeastOnce())
            ->method('getStore')
            ->willReturn($store);
        $this->customerRepository->expects($this->atLeastOnce())
            ->method('save')
            ->willReturn($customer);
        $this->addressRepository->expects($this->atLeastOnce())
            ->method('save')
            ->with($address);
        $this->customerRepository->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($customer);
        $this->random->expects($this->once())
            ->method('getUniqueHash')
            ->willReturn($newLinkToken);
        $customerSecure = $this->createPartialMockWithReflection(
            CustomerSecure::class,
            ['setRpToken', 'setRpTokenCreatedAt', 'getPasswordHash']
        );
        $customerSecure->expects($this->any())
            ->method('setRpToken')
            ->with($newLinkToken);
        $customerSecure->expects($this->any())
            ->method('setRpTokenCreatedAt')
            ->with($datetime)
            ->willReturnSelf();
        $customerSecure->expects($this->any())
            ->method('getPasswordHash')
            ->willReturn(null);
        $this->customerRegistry->expects($this->atLeastOnce())
            ->method('retrieveSecureData')
            ->willReturn($customerSecure);
        $this->emailNotificationMock->expects($this->once())
            ->method('newAccount')
            ->willThrowException($exception);
        $this->logger->expects($this->once())->method('error')->with($exception);
        $address->expects($this->atLeastOnce())->method('getCountryId')->willReturn('US');
        $this->accountManagement->createAccount($customer);
    }

    /**
     * @return void
     */
    public function testCreateAccountWithStoreNotInWebsite(): void
    {
        $this->expectException(LocalizedException::class);

        $storeId = 1;
        $websiteId = 1;
        $hash = '4nj54lkj5jfi03j49f8bgujfgsd';
        $customerMock = $this->createMock(Customer::class);
        $customerMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(null);
        $customerMock->expects($this->atLeastOnce())
            ->method('getStoreId')
            ->willReturn($storeId);
        $customerMock->expects($this->atLeastOnce())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $this->share
            ->expects($this->once())
            ->method('isWebsiteScope')
            ->willReturn(true);
        $website = $this->createMock(Website::class);
        $website->expects($this->once())
            ->method('getStoreIds')
            ->willReturn([2, 3]);
        $this->storeManager
            ->expects($this->atLeastOnce())
            ->method('getWebsite')
            ->with($websiteId)
            ->willReturn($website);
        $this->accountManagement->createAccountWithPasswordHash($customerMock, $hash);
    }

    /**
     * Test for validating customer store id by customer website id.
     *
     * @return void
     * @throws LocalizedException
     */
    public function testValidateCustomerStoreIdByWebsiteId(): void
    {
        $customerMock = $this->createMock(CustomerInterface::class);
        $customerMock->method('getWebsiteId')->willReturn(1);
        $customerMock->method('getStoreId')->willReturn(1);
        $storeMock = $this->createMock(Store::class);
        $storeMock->method('getId')
            ->willReturn(1);
        $this->storeManager->method('getStores')
            ->willReturn([$storeMock]);

        $this->assertTrue($this->accountManagement->validateCustomerStoreIdByWebsiteId($customerMock));
    }

    /**
     * Test for validating customer store id by customer website id with Exception.
     *
     * @return void
     */
    public function testValidateCustomerStoreIdByWebsiteIdException(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('The store view is not in the associated website.');

        $customerMock = $this->createMock(CustomerInterface::class);
        $storeMock = $this->createMock(Store::class);
        $storeMock->method('getId')
            ->willReturn(1);
        $this->storeManager->method('getStores')
            ->willReturn([$storeMock]);

        $this->assertTrue($this->accountManagement->validateCustomerStoreIdByWebsiteId($customerMock));
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    public function testCompanyAdminWebsiteDoesNotHaveStore(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('The store view is not in the associated website.');

        $websiteId = 1;
        $customerId = 1;
        $customerEmail = 'email@email.com';
        $hash = '4nj54lkj5jfi03j49f8bgujfgsd';

        $website = $this->createMock(Website::class);
        $website->method('getStoreIds')
            ->willReturn([]);
        $website->expects($this->atMost(1))
            ->method('getDefaultStore')
            ->willReturn(null);
        $customer = $this->createMock(Customer::class);
        $customer->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($customerId);
        $customer->expects($this->once())
            ->method('getEmail')
            ->willReturn($customerEmail);
        $customer->expects($this->atLeastOnce())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $customer->method('getStoreId')
            ->willReturnOnConsecutiveCalls(null, null, 1);
        $this->customerRepository
            ->expects($this->once())
            ->method('get')
            ->with($customerEmail)
            ->willReturn($customer);
        $this->share->method('isWebsiteScope')
            ->willReturn(true);
        $this->storeManager
            ->expects($this->atLeastOnce())
            ->method('getWebsite')
            ->with($websiteId)
            ->willReturn($website);
        $this->accountManagement->createAccountWithPasswordHash($customer, $hash);
    }

    /**
     * Test that customer is deleted in secure area when address save throws InputException
     *
     * @covers \Magento\Customer\Model\AccountManagement::createAccountWithPasswordHash()
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCreateAccountWithPasswordHashDeletesCustomerOnAddressInputException(): void
    {
        $websiteId = 1;
        $storeId = 1;
        $customerId = 1;
        $hash = '4nj54lkj5jfi03j49f8bgujfgsd';
        $inputException = new InputException(__('Invalid address data'));

        $store = $this->createMock(Store::class);
        $store->method('getWebsiteId')->willReturn($websiteId);
        $store->method('getName')->willReturn('Default Store View');

        $website = $this->createMock(Website::class);
        $website->method('getStoreIds')->willReturn([1, 2, 3]);

        $address = $this->createMock(AddressInterface::class);
        $address->method('getCountryId')->willReturn('US');
        $address->method('getId')->willReturn(null);

        $customer = $this->createMock(Customer::class);
        $getIdCallCount = 0;
        $customer->expects($this->any())
            ->method('getId')
            ->willReturnCallback(function () use (&$getIdCallCount, $customerId) {
                $getIdCallCount++;
                return $getIdCallCount <= 2 ? null : $customerId;
            });
        $customer->method('getWebsiteId')->willReturn($websiteId);
        $customer->method('getStoreId')->willReturn($storeId);
        $customer->expects($this->once())
            ->method('getAddresses')
            ->willReturn([$address]);
        $customer->expects($this->once())
            ->method('setAddresses')
            ->with(null);

        $addressModel = $this->createMock(Address::class);
        $this->addressFactory->expects($this->once())
            ->method('create')
            ->willReturn($addressModel);
        $addressModel->expects($this->once())
            ->method('updateData')
            ->with($address)
            ->willReturnSelf();
        $validator = $this->createMock(\Magento\Framework\Validator::class);
        $this->validatorFactory->expects($this->once())
            ->method('createValidator')
            ->with('customer_address', 'save')
            ->willReturn($validator);
        $validator->expects($this->once())
            ->method('isValid')
            ->with($addressModel)
            ->willReturn(true);

        $this->share->method('isWebsiteScope')->willReturn(true);
        $this->storeManager->method('getWebsite')
            ->with($websiteId)->willReturn($website);
        $this->storeManager->method('getStore')
            ->willReturn($store);
        $this->allowedCountriesReader->method('getAllowedCountries')
            ->willReturn(['US']);

        $this->customerRepository->expects($this->once())
            ->method('save')
            ->with($customer, $hash)
            ->willReturn($customer);

        $address->expects($this->once())
            ->method('setCustomerId')
            ->with($customerId);
        $this->addressRepository->expects($this->once())
            ->method('save')
            ->with($address)
            ->willThrowException($inputException);

        $this->registry->expects($this->once())
            ->method('registry')
            ->with('isSecureArea')
            ->willReturn(null);
        $this->registry->expects($this->exactly(2))
            ->method('unregister')
            ->with('isSecureArea');
        $registerCallCount = 0;
        $this->registry->expects($this->exactly(2))
            ->method('register')
            ->willReturnCallback(function (string $key, $value) use (&$registerCallCount) {
                $registerCallCount++;
                $this->assertSame('isSecureArea', $key);
                if ($registerCallCount === 1) {
                    $this->assertTrue($value);
                } else {
                    $this->assertNull($value);
                }
            });

        $this->customerRepository->expects($this->once())
            ->method('delete')
            ->with($customer);

        $this->expectException(InputException::class);
        $this->expectExceptionMessage('Invalid address data');

        $this->accountManagement->createAccountWithPasswordHash($customer, $hash);
    }

    /**
     * Test that existing isSecureArea registry value is restored after customer deletion
     *
     * @covers \Magento\Customer\Model\AccountManagement::createAccountWithPasswordHash()
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCreateAccountWithPasswordHashRestoresSecureAreaRegistryOnAddressInputException(): void
    {
        $websiteId = 1;
        $storeId = 1;
        $customerId = 1;
        $hash = '4nj54lkj5jfi03j49f8bgujfgsd';
        $originalSecureAreaValue = true;
        $inputException = new InputException(__('Invalid address'));

        $store = $this->createMock(Store::class);
        $store->method('getWebsiteId')->willReturn($websiteId);
        $store->method('getName')->willReturn('Default Store View');

        $website = $this->createMock(Website::class);
        $website->method('getStoreIds')->willReturn([1, 2, 3]);

        $address = $this->createMock(AddressInterface::class);
        $address->method('getCountryId')->willReturn('US');
        $address->method('getId')->willReturn(null);

        $customer = $this->createMock(Customer::class);
        $getIdCallCount = 0;
        $customer->expects($this->any())
            ->method('getId')
            ->willReturnCallback(function () use (&$getIdCallCount, $customerId) {
                $getIdCallCount++;
                return $getIdCallCount <= 2 ? null : $customerId;
            });
        $customer->method('getWebsiteId')->willReturn($websiteId);
        $customer->method('getStoreId')->willReturn($storeId);
        $customer->expects($this->once())
            ->method('getAddresses')
            ->willReturn([$address]);
        $customer->expects($this->once())
            ->method('setAddresses')
            ->with(null);

        $addressModel = $this->createMock(Address::class);
        $this->addressFactory->expects($this->once())
            ->method('create')
            ->willReturn($addressModel);
        $addressModel->expects($this->once())
            ->method('updateData')
            ->with($address)
            ->willReturnSelf();
        $validator = $this->createMock(\Magento\Framework\Validator::class);
        $this->validatorFactory->expects($this->once())
            ->method('createValidator')
            ->with('customer_address', 'save')
            ->willReturn($validator);
        $validator->expects($this->once())
            ->method('isValid')
            ->with($addressModel)
            ->willReturn(true);

        $this->share->method('isWebsiteScope')->willReturn(true);
        $this->storeManager->method('getWebsite')
            ->with($websiteId)->willReturn($website);
        $this->storeManager->method('getStore')
            ->willReturn($store);
        $this->allowedCountriesReader->method('getAllowedCountries')
            ->willReturn(['US']);

        $this->customerRepository->expects($this->once())
            ->method('save')
            ->with($customer, $hash)
            ->willReturn($customer);

        $address->expects($this->once())
            ->method('setCustomerId')
            ->with($customerId);
        $this->addressRepository->expects($this->once())
            ->method('save')
            ->with($address)
            ->willThrowException($inputException);

        $this->registry->expects($this->once())
            ->method('registry')
            ->with('isSecureArea')
            ->willReturn($originalSecureAreaValue);
        $this->registry->expects($this->exactly(2))
            ->method('unregister')
            ->with('isSecureArea');
        $registerCallCount = 0;
        $this->registry->expects($this->exactly(2))
            ->method('register')
            ->willReturnCallback(
                function (string $key, $value) use (&$registerCallCount, $originalSecureAreaValue) {
                    $registerCallCount++;
                    $this->assertSame('isSecureArea', $key);
                    if ($registerCallCount === 1) {
                        $this->assertTrue($value);
                    } else {
                        $this->assertSame($originalSecureAreaValue, $value);
                    }
                }
            );

        $this->customerRepository->expects($this->once())
            ->method('delete')
            ->with($customer);

        $this->expectException(InputException::class);
        $this->expectExceptionMessage('Invalid address');

        $this->accountManagement->createAccountWithPasswordHash($customer, $hash);
    }

    /**
     * Test that resendConfirmation throws InvalidTransitionException when confirmation is not needed
     *
     * @covers \Magento\Customer\Model\AccountManagement::resendConfirmation()
     * @return void
     */
    public function testResendConfirmationThrowsExceptionWhenConfirmationNotNeeded(): void
    {
        $email = 'customer@example.com';
        $websiteId = 1;

        $customer = $this->createMock(CustomerInterface::class);
        $customer->expects($this->once())
            ->method('getConfirmation')
            ->willReturn(null);

        $this->customerRepository->expects($this->once())
            ->method('get')
            ->with($email, $websiteId)
            ->willReturn($customer);

        $this->emailNotificationMock->expects($this->never())
            ->method('newAccount');

        $this->expectException(InvalidTransitionException::class);
        $this->expectExceptionMessage("Confirmation isn't needed.");

        $this->accountManagement->resendConfirmation($email, $websiteId);
    }

    /**
     * Test that resendConfirmation returns true when confirmation email is sent successfully
     *
     * @covers \Magento\Customer\Model\AccountManagement::resendConfirmation()
     * @return void
     */
    public function testResendConfirmationReturnsTrueOnSuccess(): void
    {
        $email = 'customer@example.com';
        $websiteId = 1;
        $storeId = 1;
        $redirectUrl = 'http://example.com/redirect';
        $confirmationToken = 'abc123confirmation';

        $customer = $this->createMock(CustomerInterface::class);
        $customer->expects($this->once())
            ->method('getConfirmation')
            ->willReturn($confirmationToken);

        $this->customerRepository->expects($this->once())
            ->method('get')
            ->with($email, $websiteId)
            ->willReturn($customer);

        $this->store->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);
        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($this->store);

        $this->emailNotificationMock->expects($this->once())
            ->method('newAccount')
            ->with(
                $customer,
                AccountManagement::NEW_ACCOUNT_EMAIL_CONFIRMATION,
                $redirectUrl,
                $storeId
            );

        $this->assertTrue(
            $this->accountManagement->resendConfirmation($email, $websiteId, $redirectUrl)
        );
    }

    /**
     * Test that resendConfirmation returns false when mail sending fails
     *
     * @covers \Magento\Customer\Model\AccountManagement::resendConfirmation()
     * @return void
     */
    public function testResendConfirmationReturnsFalseOnMailException(): void
    {
        $email = 'customer@example.com';
        $websiteId = 1;
        $storeId = 1;
        $confirmationToken = 'abc123confirmation';
        $mailException = new MailException(__('Unable to send mail'));

        $customer = $this->createMock(CustomerInterface::class);
        $customer->expects($this->once())
            ->method('getConfirmation')
            ->willReturn($confirmationToken);

        $this->customerRepository->expects($this->once())
            ->method('get')
            ->with($email, $websiteId)
            ->willReturn($customer);

        $this->store->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);
        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($this->store);

        $this->emailNotificationMock->expects($this->once())
            ->method('newAccount')
            ->with(
                $customer,
                AccountManagement::NEW_ACCOUNT_EMAIL_CONFIRMATION,
                '',
                $storeId
            )
            ->willThrowException($mailException);

        $this->logger->expects($this->once())
            ->method('critical')
            ->with($mailException);

        $this->assertFalse(
            $this->accountManagement->resendConfirmation($email, $websiteId)
        );
    }

    /**
     * Test that activate throws InvalidTransitionException when the account is already active
     *
     * @covers \Magento\Customer\Model\AccountManagement::activate()
     * @return void
     */
    public function testActivateThrowsExceptionWhenAccountAlreadyActive(): void
    {
        $email = 'customer@example.com';
        $confirmationKey = 'confirmKey123';

        $customer = $this->createMock(Customer::class);
        $customer->expects($this->once())
            ->method('getConfirmation')
            ->willReturn(null);

        $this->customerRepository->expects($this->once())
            ->method('get')
            ->with($email)
            ->willReturn($customer);

        $this->customerRepository->expects($this->never())
            ->method('save');

        $this->expectException(InvalidTransitionException::class);
        $this->expectExceptionMessage('The account is already active.');

        $this->accountManagement->activate($email, $confirmationKey);
    }

    /**
     * Test that activate throws InputMismatchException when the confirmation key is invalid
     *
     * @covers \Magento\Customer\Model\AccountManagement::activate()
     * @return void
     */
    public function testActivateThrowsExceptionWhenConfirmationKeyInvalid(): void
    {
        $email = 'customer@example.com';
        $confirmationKey = 'wrongKey';
        $storedConfirmation = 'correctKey';

        $customer = $this->createMock(Customer::class);
        $customer->expects($this->exactly(2))
            ->method('getConfirmation')
            ->willReturn($storedConfirmation);

        $this->customerRepository->expects($this->once())
            ->method('get')
            ->with($email)
            ->willReturn($customer);

        $this->customerRepository->expects($this->never())
            ->method('save');

        $this->expectException(InputMismatchException::class);
        $this->expectExceptionMessage(
            'The confirmation token is invalid. Verify the token and try again.'
        );

        $this->accountManagement->activate($email, $confirmationKey);
    }

    /**
     * Test that activate succeeds and sends or skips confirmation email based on prior login
     *
     * @covers \Magento\Customer\Model\AccountManagement::activate()
     * @param string|null $lastLoginAt
     * @param bool $expectsEmail
     * @return void
     */
    #[DataProvider('activateSuccessDataProvider')]
    public function testActivateSuccessEmailBehaviorBasedOnPriorLogin(
        ?string $lastLoginAt,
        bool $expectsEmail
    ): void {
        $email = 'customer@example.com';
        $confirmationKey = 'correctKey';
        $customerId = 5;
        $storeId = 1;

        $customer = $this->createMock(Customer::class);
        $customer->expects($this->exactly(2))
            ->method('getConfirmation')
            ->willReturn($confirmationKey);
        $customer->expects($this->once())
            ->method('setConfirmation')
            ->with(null);
        $customer->expects($this->once())
            ->method('setData')
            ->with('ignore_validation_flag', true);
        $customer->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($customerId);

        $this->customerRepository->expects($this->once())
            ->method('get')
            ->with($email)
            ->willReturn($customer);
        $this->customerRepository->expects($this->once())
            ->method('save')
            ->with($customer);

        $logMock = $this->createMock(Log::class);
        $logMock->expects($this->once())
            ->method('getLastLoginAt')
            ->willReturn($lastLoginAt);
        $this->customerLoggerMock->expects($this->once())
            ->method('get')
            ->with($customerId)
            ->willReturn($logMock);

        if ($expectsEmail) {
            $this->store->expects($this->once())
                ->method('getId')
                ->willReturn($storeId);
            $this->storeManager->expects($this->once())
                ->method('getStore')
                ->willReturn($this->store);
            $this->emailNotificationMock->expects($this->once())
                ->method('newAccount')
                ->with($customer, 'confirmed', '', $storeId);
        } else {
            $this->emailNotificationMock->expects($this->never())
                ->method('newAccount');
        }

        $this->assertSame($customer, $this->accountManagement->activate($email, $confirmationKey));
    }

    /**
     * Data provider for testActivateSuccessEmailBehaviorBasedOnPriorLogin
     *
     * @return array<string, array{string|null, bool}>
     */
    public static function activateSuccessDataProvider(): array
    {
        return [
            'no_prior_login_sends_email' => [null, true],
            'has_prior_login_skips_email' => ['2025-01-15 10:30:00', false],
        ];
    }

    /**
     * Test that activateById throws the correct exception for invalid activation states
     *
     * @covers \Magento\Customer\Model\AccountManagement::activateById()
     * @param string|null $confirmation
     * @param string $confirmationKey
     * @param int $getConfirmationCallCount
     * @param string $expectedException
     * @param string $expectedMessage
     * @return void
     */
    #[DataProvider('activateByIdExceptionDataProvider')]
    public function testActivateByIdThrowsExceptionForInvalidState(
        ?string $confirmation,
        string $confirmationKey,
        int $getConfirmationCallCount,
        string $expectedException,
        string $expectedMessage
    ): void {
        $customerId = 5;

        $customer = $this->createMock(Customer::class);
        $customer->expects($this->exactly($getConfirmationCallCount))
            ->method('getConfirmation')
            ->willReturn($confirmation);

        $this->customerRepository->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($customer);

        $this->customerRepository->expects($this->never())
            ->method('save');

        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedMessage);

        $this->accountManagement->activateById($customerId, $confirmationKey);
    }

    /**
     * Test that activateById successfully activates customer and sends confirmation email
     *
     * @covers \Magento\Customer\Model\AccountManagement::activateById()
     * @return void
     */
    public function testActivateByIdSuccessReturnsCustomer(): void
    {
        $customerId = 5;
        $confirmationKey = 'correctKey';
        $storeId = 1;

        $customer = $this->createMock(Customer::class);
        $customer->expects($this->exactly(2))
            ->method('getConfirmation')
            ->willReturn($confirmationKey);
        $customer->expects($this->once())
            ->method('setConfirmation')
            ->with(null);
        $customer->expects($this->once())
            ->method('setData')
            ->with('ignore_validation_flag', true);
        $customer->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($customerId);

        $this->customerRepository->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($customer);
        $this->customerRepository->expects($this->once())
            ->method('save')
            ->with($customer);

        $logMock = $this->createMock(Log::class);
        $logMock->expects($this->once())
            ->method('getLastLoginAt')
            ->willReturn(null);
        $this->customerLoggerMock->expects($this->once())
            ->method('get')
            ->with($customerId)
            ->willReturn($logMock);

        $this->store->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);
        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($this->store);

        $this->emailNotificationMock->expects($this->once())
            ->method('newAccount')
            ->with($customer, 'confirmed', '', $storeId);

        $this->assertSame(
            $customer,
            $this->accountManagement->activateById($customerId, $confirmationKey)
        );
    }

    /**
     * Data provider for testActivateByIdThrowsExceptionForInvalidState
     *
     * @return array<string, array{string|null, string, int, string, string}>
     */
    public static function activateByIdExceptionDataProvider(): array
    {
        return [
            'already_active_account' => [
                null,
                'anyKey',
                1,
                InvalidTransitionException::class,
                'The account is already active.',
            ],
            'invalid_confirmation_key' => [
                'storedKey',
                'wrongKey',
                2,
                InputMismatchException::class,
                'The confirmation token is invalid. Verify the token and try again.',
            ],
        ];
    }

    /**
     * Test that authenticate delegates to Authenticate::execute and returns the customer
     *
     * @covers \Magento\Customer\Model\AccountManagement::authenticate()
     * @return void
     */
    public function testAuthenticateReturnsCustomerOnSuccess(): void
    {
        $username = 'customer@example.com';
        $password = 'securePassword1';

        $customer = $this->createMock(CustomerInterface::class);

        $this->authenticateMock->expects($this->once())
            ->method('execute')
            ->with($username, $password)
            ->willReturn($customer);

        $this->assertSame($customer, $this->accountManagement->authenticate($username, $password));
    }

    /**
     * Test that authenticate propagates exceptions thrown by Authenticate::execute
     *
     * @covers \Magento\Customer\Model\AccountManagement::authenticate()
     * @return void
     */
    public function testAuthenticatePropagatesException(): void
    {
        $username = 'customer@example.com';
        $password = 'wrongPassword';

        $this->authenticateMock->expects($this->once())
            ->method('execute')
            ->with($username, $password)
            ->willThrowException(new InvalidEmailOrPasswordException(__('Invalid login or password.')));

        $this->expectException(InvalidEmailOrPasswordException::class);
        $this->expectExceptionMessage('Invalid login or password.');

        $this->accountManagement->authenticate($username, $password);
    }

    /**
     * Test that getDefaultBillingAddress/getDefaultShippingAddress returns matching address
     *
     * @covers \Magento\Customer\Model\AccountManagement::getDefaultBillingAddress()
     * @covers \Magento\Customer\Model\AccountManagement::getDefaultShippingAddress()
     * @param string $method
     * @param string $customerGetter
     * @return void
     */
    #[DataProvider('defaultAddressMethodsDataProvider')]
    public function testGetDefaultAddressReturnsMatchingAddress(
        string $method,
        string $customerGetter
    ): void {
        $customerId = 1;
        $defaultAddressId = 10;

        $matchingAddress = $this->createMock(AddressInterface::class);
        $matchingAddress->method('getId')->willReturn($defaultAddressId);

        $otherAddress = $this->createMock(AddressInterface::class);
        $otherAddress->method('getId')->willReturn(99);

        $customer = $this->createMock(CustomerInterface::class);
        $customer->expects($this->once())
            ->method($customerGetter)
            ->willReturn($defaultAddressId);
        $customer->expects($this->once())
            ->method('getAddresses')
            ->willReturn([$otherAddress, $matchingAddress]);

        $this->customerRepository->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($customer);

        $this->assertSame($matchingAddress, $this->accountManagement->$method($customerId));
    }

    /**
     * Test that getDefaultBillingAddress/getDefaultShippingAddress returns null when not set
     *
     * @covers \Magento\Customer\Model\AccountManagement::getDefaultBillingAddress()
     * @covers \Magento\Customer\Model\AccountManagement::getDefaultShippingAddress()
     * @param string $method
     * @param string $customerGetter
     * @return void
     */
    #[DataProvider('defaultAddressMethodsDataProvider')]
    public function testGetDefaultAddressReturnsNullWhenNotSet(
        string $method,
        string $customerGetter
    ): void {
        $customerId = 1;

        $address = $this->createMock(AddressInterface::class);
        $address->method('getId')->willReturn(5);

        $customer = $this->createMock(CustomerInterface::class);
        $customer->expects($this->once())
            ->method($customerGetter)
            ->willReturn(null);
        $customer->expects($this->once())
            ->method('getAddresses')
            ->willReturn([$address]);

        $this->customerRepository->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($customer);

        $this->assertNull($this->accountManagement->$method($customerId));
    }

    /**
     * Data provider for default address tests
     *
     * @return array<string, array{string, string}>
     */
    public static function defaultAddressMethodsDataProvider(): array
    {
        return [
            'billing' => ['getDefaultBillingAddress', 'getDefaultBilling'],
            'shipping' => ['getDefaultShippingAddress', 'getDefaultShipping'],
        ];
    }

    /**
     * @covers \Magento\Customer\Model\AccountManagement::changePasswordById()
     *
     * @return void
     */
    public function testChangePasswordByIdThrowsExceptionWhenCustomerNotFound(): void
    {
        $customerId = 999;
        $currentPassword = 'oldPass123';
        $newPassword = 'newPass456';

        $this->customerRepository
            ->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willThrowException(new NoSuchEntityException(new Phrase('No such entity.')));

        $this->expectException(InvalidEmailOrPasswordException::class);
        $this->expectExceptionMessage('Invalid login or password.');

        $this->accountManagement->changePasswordById($customerId, $currentPassword, $newPassword);
    }

    /**
     * @covers \Magento\Customer\Model\AccountManagement::changePasswordById()
     *
     * @return void
     */
    public function testChangePasswordByIdThrowsExceptionWhenCurrentPasswordInvalid(): void
    {
        $customerId = 7;
        $currentPassword = 'wrongPass';
        $newPassword = 'newPass456';

        $customerMock = $this->createMock(CustomerInterface::class);
        $customerMock->expects($this->once())
            ->method('getId')
            ->willReturn($customerId);

        $this->customerRepository
            ->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($customerMock);

        $this->authenticationMock->expects($this->once())
            ->method('authenticate')
            ->with($customerId, $currentPassword)
            ->willThrowException(new InvalidEmailOrPasswordException(new Phrase('Original auth error')));

        $this->expectException(InvalidEmailOrPasswordException::class);
        $this->expectExceptionMessage("The password doesn't match this account. Verify the password and try again.");

        $this->accountManagement->changePasswordById($customerId, $currentPassword, $newPassword);
    }

    /**
     * @covers \Magento\Customer\Model\AccountManagement::changePasswordById()
     *
     * @return void
     * @throws LocalizedException
     * @throws InvalidEmailOrPasswordException
     */
    public function testChangePasswordByIdSuccess(): void
    {
        $customerId = 7;
        $currentPassword = '1234567';
        $newPassword = 'abcdefg';
        $passwordHash = '1a2b3f4c';

        $this->reInitModel();
        $customer = $this->createMock(CustomerInterface::class);
        $customer->expects($this->any())
            ->method('getId')
            ->willReturn($customerId);
        $customer->expects($this->once())
            ->method('getAddresses')
            ->willReturn([]);

        $this->customerRepository
            ->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($customer);

        $this->authenticationMock->expects($this->once())
            ->method('authenticate');

        $this->customerSecure->expects($this->once())
            ->method('setRpToken')
            ->with(null);
        $this->customerSecure->expects($this->once())
            ->method('setRpTokenCreatedAt')
            ->willReturnSelf();
        $this->customerSecure->expects($this->any())
            ->method('getPasswordHash')
            ->willReturn($passwordHash);

        $this->customerRegistry->expects($this->any())
            ->method('retrieveSecureData')
            ->with($customerId)
            ->willReturn($this->customerSecure);

        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->willReturnMap(
                [
                    [
                        AccountManagement::XML_PATH_MINIMUM_PASSWORD_LENGTH,
                        'default',
                        null,
                        7
                    ],
                    [
                        AccountManagement::XML_PATH_REQUIRED_CHARACTER_CLASSES_NUMBER,
                        'default',
                        null,
                        1
                    ]
                ]
            );
        $this->string->expects($this->any())
            ->method('strlen')
            ->with($newPassword)
            ->willReturn(7);

        $this->sessionCleanerMock->expects($this->once())->method('clearFor')->with($customerId)->willReturnSelf();

        $this->customerRepository
            ->expects($this->once())
            ->method('save')
            ->with($customer);

        $this->assertTrue($this->accountManagement->changePasswordById($customerId, $currentPassword, $newPassword));
    }

    /**
     * @covers \Magento\Customer\Model\AccountManagement::validate()
     *
     * @return void
     */
    public function testValidateReturnsValidResultWhenEavValidationPasses(): void
    {
        $customerMock = $this->createMock(CustomerInterface::class);
        $existingAddresses = [$this->createMock(AddressInterface::class)];

        $customerMock->expects($this->once())
            ->method('getAddresses')
            ->willReturn($existingAddresses);
        $customerMock->expects($this->exactly(2))
            ->method('setAddresses')
            ->willReturnCallback(function (array $addresses) use ($customerMock, $existingAddresses) {
                static $callCount = 0;
                $callCount++;
                if ($callCount === 1) {
                    $this->assertSame([], $addresses);
                } else {
                    $this->assertSame($existingAddresses, $addresses);
                }
                return $customerMock;
            });

        $customerModelMock = $this->createMock(\Magento\Customer\Model\Customer::class);
        $customerModelMock->expects($this->once())
            ->method('updateData')
            ->with($customerMock)
            ->willReturn($customerModelMock);
        $this->customerFactory->expects($this->once())
            ->method('create')
            ->willReturn($customerModelMock);

        $this->eavValidatorMock->expects($this->once())
            ->method('isValid')
            ->with($customerModelMock)
            ->willReturn(true);

        $validationResultsMock = $this->createMock(ValidationResultsInterface::class);
        $this->validationResultsInterfaceFactory->expects($this->once())
            ->method('create')
            ->willReturn($validationResultsMock);
        $validationResultsMock->expects($this->once())
            ->method('setIsValid')
            ->with(true)
            ->willReturn($validationResultsMock);
        $validationResultsMock->expects($this->once())
            ->method('setMessages')
            ->with([])
            ->willReturn($validationResultsMock);

        $result = $this->accountManagement->validate($customerMock);
        $this->assertSame($validationResultsMock, $result);
    }

    /**
     * @covers \Magento\Customer\Model\AccountManagement::validate()
     *
     * @return void
     */
    public function testValidateReturnsInvalidResultWhenEavValidationFails(): void
    {
        $customerMock = $this->createMock(CustomerInterface::class);

        $customerMock->expects($this->once())
            ->method('getAddresses')
            ->willReturn([]);
        $customerMock->expects($this->exactly(2))
            ->method('setAddresses')
            ->with([])
            ->willReturn($customerMock);

        $customerModelMock = $this->createMock(\Magento\Customer\Model\Customer::class);
        $customerModelMock->expects($this->once())
            ->method('updateData')
            ->with($customerMock)
            ->willReturn($customerModelMock);
        $this->customerFactory->expects($this->once())
            ->method('create')
            ->willReturn($customerModelMock);

        $validationErrors = [
            'firstname' => ['First name is required.'],
            'lastname' => ['Last name is required.'],
        ];
        $this->eavValidatorMock->expects($this->once())
            ->method('isValid')
            ->with($customerModelMock)
            ->willReturn(false);
        $this->eavValidatorMock->expects($this->exactly(2))
            ->method('getMessages')
            ->willReturn($validationErrors);

        $mergedMessages = ['First name is required.', 'Last name is required.'];
        $validationResultsMock = $this->createMock(ValidationResultsInterface::class);
        $this->validationResultsInterfaceFactory->expects($this->once())
            ->method('create')
            ->willReturn($validationResultsMock);
        $validationResultsMock->expects($this->once())
            ->method('setIsValid')
            ->with(false)
            ->willReturn($validationResultsMock);
        $validationResultsMock->expects($this->once())
            ->method('setMessages')
            ->with($mergedMessages)
            ->willReturn($validationResultsMock);

        $result = $this->accountManagement->validate($customerMock);
        $this->assertSame($validationResultsMock, $result);
    }

    /**
     * @covers \Magento\Customer\Model\AccountManagement::isReadonly()
     *
     * @param bool $deleteable
     * @param bool $expectedReadonly
     * @return void
     */
    #[DataProvider('isReadonlyDataProvider')]
    public function testIsReadonly(bool $deleteable, bool $expectedReadonly): void
    {
        $customerId = 1;

        $customerSecure = new CustomerSecure(['deleteable' => $deleteable]);

        $this->customerRegistry->expects($this->once())
            ->method('retrieveSecureData')
            ->with($customerId)
            ->willReturn($customerSecure);

        $this->assertSame($expectedReadonly, $this->accountManagement->isReadonly($customerId));
    }

    /**
     * @return array<string, array{bool, bool}>
     */
    public static function isReadonlyDataProvider(): array
    {
        return [
            'deleteable_customer_is_not_readonly' => [true, false],
            'non_deleteable_customer_is_readonly' => [false, true],
        ];
    }

    /**
     * @covers \Magento\Customer\Model\AccountManagement::sendPasswordResetConfirmationEmail()
     *
     * @return void
     */
    public function testSendPasswordResetConfirmationEmailSuccess(): void
    {
        $customerId = 1;
        $email = 'test@example.com';
        $storeId = 2;
        $customerName = 'John Doe';
        $templateIdentifier = 'forgot_password_template';
        $sender = 'support_identity';
        $customerData = ['firstname' => 'John', 'lastname' => 'Doe'];

        $customerMock = $this->createMock(CustomerInterface::class);
        $customerMock->expects($this->any())
            ->method('getId')
            ->willReturn($customerId);
        $customerMock->expects($this->any())
            ->method('getEmail')
            ->willReturn($email);

        $this->store->expects($this->any())
            ->method('getId')
            ->willReturn($storeId);
        $this->storeManager->expects($this->any())
            ->method('getStore')
            ->willReturn($this->store);

        $this->customerRegistry->expects($this->once())
            ->method('retrieveSecureData')
            ->with($customerId)
            ->willReturn($this->customerSecure);
        $this->dataObjectProcessor->expects($this->once())
            ->method('buildOutputDataArray')
            ->with($customerMock, CustomerInterface::class)
            ->willReturn($customerData);
        $this->customerSecure->expects($this->once())
            ->method('addData')
            ->with($customerData)
            ->willReturnSelf();
        $this->customerSecure->expects($this->once())
            ->method('setData')
            ->with('name', $customerName)
            ->willReturnSelf();
        $this->customerViewHelper->expects($this->any())
            ->method('getCustomerName')
            ->with($customerMock)
            ->willReturn($customerName);

        $this->scopeConfig->expects($this->exactly(2))
            ->method('getValue')
            ->willReturnCallback(function (string $path) use ($templateIdentifier, $sender) {
                if ($path === AccountManagement::XML_PATH_FORGOT_EMAIL_TEMPLATE) {
                    return $templateIdentifier;
                }
                if ($path === AccountManagement::XML_PATH_FORGOT_EMAIL_IDENTITY) {
                    return $sender;
                }
                return null;
            });

        $transport = $this->createMock(TransportInterface::class);
        $this->transportBuilder->expects($this->once())
            ->method('setTemplateIdentifier')
            ->with($templateIdentifier)
            ->willReturnSelf();
        $this->transportBuilder->expects($this->once())
            ->method('setTemplateOptions')
            ->with(['area' => Area::AREA_FRONTEND, 'store' => $storeId])
            ->willReturnSelf();
        $this->transportBuilder->expects($this->once())
            ->method('setTemplateVars')
            ->with(['customer' => $this->customerSecure, 'store' => $this->store])
            ->willReturnSelf();
        $this->transportBuilder->expects($this->once())
            ->method('setFrom')
            ->with($sender)
            ->willReturnSelf();
        $this->transportBuilder->expects($this->once())
            ->method('addTo')
            ->with($email, $customerName)
            ->willReturnSelf();
        $this->transportBuilder->expects($this->once())
            ->method('getTransport')
            ->willReturn($transport);
        $transport->expects($this->once())
            ->method('sendMessage');

        $result = $this->accountManagement->sendPasswordResetConfirmationEmail($customerMock);
        $this->assertSame($this->accountManagement, $result);
    }

    /**
     * @covers \Magento\Customer\Model\AccountManagement::getPasswordHash()
     *
     * @return void
     */
    public function testGetPasswordHash(): void
    {
        $password = 'mySecretPassword';
        $expectedHash = 'a1b2c3d4e5f6';

        $this->encryptor->expects($this->once())
            ->method('getHash')
            ->with($password, true)
            ->willReturn($expectedHash);

        $this->assertSame($expectedHash, $this->accountManagement->getPasswordHash($password));
    }
}
