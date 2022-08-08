<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\ValidationResultsInterfaceFactory;
use Magento\Customer\Api\SessionCleanerInterface;
use Magento\Customer\Helper\View;
use Magento\Customer\Model\AccountConfirmation;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\AddressRegistry;
use Magento\Customer\Model\AuthenticationInterface;
use Magento\Customer\Model\Config\Share;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Data\Customer;
use Magento\Customer\Model\Data\CustomerSecure;
use Magento\Customer\Model\EmailNotificationInterface;
use Magento\Customer\Model\Metadata\Validator;
use Magento\Customer\Model\ResourceModel\Visitor\CollectionFactory;
use Magento\Directory\Model\AllowedCountries;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\ExpiredException;
use Magento\Framework\Exception\State\InputMismatchException;
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
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AccountManagementTest extends TestCase
{
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
     * @var int
     */
    private $getIdCounter;

    /**
     * @var int
     */
    private $getStoreIdCounter;

    /**
     * @var int
     */
    private $getWebsiteIdCounter;

    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->customerFactory = $this->createPartialMock(CustomerFactory::class, ['create']);
        $this->manager = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
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
        $this->authenticationMock = $this->getMockBuilder(AuthenticationInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->emailNotificationMock = $this->getMockBuilder(EmailNotificationInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

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

        $this->objectManagerHelper = new ObjectManagerHelper($this);
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
                'allowedCountriesReader' => $this->allowedCountriesReader
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
        $this->getIdCounter = 0;
        $this->getStoreIdCounter = 0;
        $this->getWebsiteIdCounter = 0;
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

        $website = $this->getMockBuilder(Website::class)
            ->disableOriginalConstructor()
            ->getMock();
        $website->expects($this->once())
            ->method('getStoreIds')
            ->willReturn([1, 2, 3]);
        $customer = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
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

        $address = $this->getMockBuilder(AddressInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $store->expects($this->once())
            ->method('getId')
            ->willReturn($defaultStoreId);
        $website = $this->getMockBuilder(Website::class)
            ->disableOriginalConstructor()
            ->getMock();
        $website->expects($this->atLeastOnce())
            ->method('getStoreIds')
            ->willReturn([1, 2, 3]);
        $website->expects($this->once())
            ->method('getDefaultStore')
            ->willReturn($store);
        $customer = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
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
        $customer->expects($this->once())
            ->method('setStoreId')
            ->with($defaultStoreId);
        $customer
            ->expects($this->once())
            ->method('getAddresses')
            ->willReturn([$address]);
        $customer
            ->expects($this->once())
            ->method('setAddresses')
            ->with(null);
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

        $address = $this->getMockBuilder(AddressInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $store->expects($this->once())
            ->method('getId')
            ->willReturn($defaultStoreId);
        $website = $this->getMockBuilder(Website::class)
            ->disableOriginalConstructor()
            ->getMock();
        $website->method('getStoreIds')
            ->willReturn([1, 2, 3]);
        $website->expects($this->once())
            ->method('getDefaultStore')
            ->willReturn($store);
        $customer = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
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
        $customer->expects($this->once())
            ->method('setStoreId')
            ->with($defaultStoreId);
        $customer
            ->expects($this->once())
            ->method('getAddresses')
            ->willReturn([$address]);
        $customer
            ->expects($this->once())
            ->method('setAddresses')
            ->with(null);
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
        $this->expectException(LocalizedException::class);

        $websiteId = 1;
        $defaultStoreId = 1;
        $customerId = 1;
        $customerEmail = 'email@email.com';
        $hash = '4nj54lkj5jfi03j49f8bgujfgsd';

        $address = $this->getMockBuilder(AddressInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $address->expects($this->once())
            ->method('setCustomerId')
            ->with($customerId);
        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $store->expects($this->once())
            ->method('getId')
            ->willReturn($defaultStoreId);
        $website = $this->getMockBuilder(Website::class)
            ->disableOriginalConstructor()
            ->getMock();
        $website->method('getStoreIds')
            ->willReturn([1, 2, 3]);
        $website->expects($this->once())
            ->method('getDefaultStore')
            ->willReturn($store);
        $customer = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customer->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($customerId);
        $customer->expects($this->once())
            ->method('getEmail')
            ->willReturn($customerEmail);
        $customer->expects($this->atLeastOnce())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $customer
            ->method('getStoreId')
            ->willReturnOnConsecutiveCalls(null, null, 1);
        $customer->expects($this->once())
            ->method('setStoreId')
            ->with($defaultStoreId);
        $customer
            ->expects($this->once())
            ->method('getAddresses')
            ->willReturn([$address]);
        $customer
            ->expects($this->once())
            ->method('setAddresses')
            ->with(null);
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
        $this->customerRepository
            ->expects($this->once())
            ->method('save')
            ->with($customer, $hash)
            ->willReturn($customer);
        $exception = new InputException(
            new Phrase('Exception message')
        );
        $this->addressRepository
            ->expects($this->atLeastOnce())
            ->method('save')
            ->with($address)
            ->willThrowException($exception);
        $this->customerRepository
            ->expects($this->once())
            ->method('delete')
            ->with($customer);
        $this->allowedCountriesReader
            ->expects($this->atLeastOnce())
            ->method('getAllowedCountries')
            ->willReturn(['US' => 'US']);
        $address
            ->expects($this->atLeastOnce())
            ->method('getCountryId')
            ->willReturn('US');
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

        $customerMock = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();

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
        $website = $this->getMockBuilder(Website::class)
            ->disableOriginalConstructor()
            ->getMock();
        $website->expects($this->once())
            ->method('getStoreIds')
            ->willReturn([1, 2, 3]);
        $this->storeManager
            ->expects($this->atLeastOnce())
            ->method('getWebsite')
            ->with($websiteId)
            ->willReturn($website);

        $storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();

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

        $address = $this->getMockBuilder(AddressInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $address->expects($this->once())
            ->method('setCustomerId')
            ->with($customerId);
        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $store->expects($this->once())
            ->method('getId')
            ->willReturn($defaultStoreId);
        $store->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $website = $this->getMockBuilder(Website::class)
            ->disableOriginalConstructor()
            ->getMock();
        $website->expects($this->atLeastOnce())
            ->method('getStoreIds')
            ->willReturn([1, 2, 3]);
        $customer = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $testCase = $this;
        $customer->expects($this->any())
            ->method('getId')
            ->will($this->returnCallback(function () use ($testCase, $customerId) {
                if ($testCase->getIdCounter > 0) {
                    return $customerId;
                } else {
                    $testCase->getIdCounter += 1;
                    return null;
                }
            }));
        $customer->expects($this->atLeastOnce())
            ->method('getEmail')
            ->willReturn($customerEmail);
        $customer->expects($this->any())
            ->method('getWebsiteId')
            ->will($this->returnCallback(function () use ($testCase, $websiteId) {
                if ($testCase->getWebsiteIdCounter > 1) {
                    return $websiteId;
                } else {
                    $testCase->getWebsiteIdCounter += 1;
                    return null;
                }
            }));
        $customer->expects($this->once())
            ->method('setWebsiteId')
            ->with($websiteId);
        $customer->expects($this->any())
            ->method('getStoreId')
            ->will($this->returnCallback(function () use ($testCase, $defaultStoreId) {
                if ($testCase->getStoreIdCounter > 0) {
                    return $defaultStoreId;
                } else {
                    $testCase->getStoreIdCounter += 1;
                    return null;
                }
            }));
        $customer->expects($this->once())
            ->method('setStoreId')
            ->with($defaultStoreId);
        $customer->expects($this->once())
            ->method('getAddresses')
            ->willReturn([$address]);
        $customer->expects($this->once())
            ->method('setAddresses')
            ->with(null);
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
        $customerSecure = $this->getMockBuilder(CustomerSecure::class)
            ->addMethods(['setRpToken', 'setRpTokenCreatedAt', 'getPasswordHash'])
            ->disableOriginalConstructor()
            ->getMock();
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
        $this->allowedCountriesReader
            ->expects($this->atLeastOnce())
            ->method('getAllowedCountries')
            ->willReturn(['US' => 'US']);
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
    public function dataProviderCheckPasswordStrength(): array
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
     * @dataProvider dataProviderCheckPasswordStrength
     * @throws LocalizedException
     */
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

        $customer = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
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

        $customer = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
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
        $address = $this->getMockBuilder(AddressInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $address->expects($this->once())
            ->method('setCustomerId')
            ->with($customerId);
        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $store->expects($this->once())
            ->method('getId')
            ->willReturn($defaultStoreId);
        $store->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $website = $this->getMockBuilder(Website::class)
            ->disableOriginalConstructor()
            ->getMock();
        $website->expects($this->atLeastOnce())
            ->method('getStoreIds')
            ->willReturn([1, 2, 3]);
        $customer = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $testCase = $this;
        $customer->expects($this->any())
            ->method('getId')
            ->will($this->returnCallback(function () use ($testCase, $customerId) {
                if ($testCase->getIdCounter > 0) {
                    return $customerId;
                } else {
                    $testCase->getIdCounter += 1;
                    return null;
                }
            }));
        $customer->expects($this->atLeastOnce())
            ->method('getEmail')
            ->willReturn($customerEmail);
        $customer->expects($this->any())
            ->method('getWebsiteId')
            ->will($this->returnCallback(function () use ($testCase, $websiteId) {
                if ($testCase->getWebsiteIdCounter > 1) {
                    return $websiteId;
                } else {
                    $testCase->getWebsiteIdCounter += 1;
                    return null;
                }
            }));
        $customer->expects($this->once())
            ->method('setWebsiteId')
            ->with($websiteId);
        $customer->expects($this->any())
            ->method('getStoreId')
            ->will($this->returnCallback(function () use ($testCase, $defaultStoreId) {
                if ($testCase->getStoreIdCounter > 0) {
                    return $defaultStoreId;
                } else {
                    $testCase->getStoreIdCounter += 1;
                    return null;
                }
            }));
        $customer->expects($this->once())
            ->method('setStoreId')
            ->with($defaultStoreId);
        $customer->expects($this->once())
            ->method('getAddresses')
            ->willReturn([$address]);
        $customer->expects($this->once())
            ->method('setAddresses')
            ->with(null);
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
        $customerSecure = $this->getMockBuilder(CustomerSecure::class)
            ->addMethods(['setRpToken', 'setRpTokenCreatedAt', 'getPasswordHash'])
            ->disableOriginalConstructor()
            ->getMock();
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
        $this->allowedCountriesReader
            ->expects($this->atLeastOnce())
            ->method('getAllowedCountries')
            ->willReturn(['US' => 'US']);
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
        $requestedGroupId = 3;

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
        $address = $this->getMockBuilder(AddressInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $address->expects($this->once())
            ->method('setCustomerId')
            ->with($customerId);
        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $store->expects($this->once())
            ->method('getId')
            ->willReturn($defaultStoreId);
        $store->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $website = $this->getMockBuilder(Website::class)
            ->disableOriginalConstructor()
            ->getMock();
        $website->expects($this->atLeastOnce())
            ->method('getStoreIds')
            ->willReturn([1, 2, 3]);
        $customer = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $testCase = $this;
        $customer->expects($this->any())
            ->method('getId')
            ->will($this->returnCallback(function () use ($testCase, $customerId) {
                if ($testCase->getIdCounter > 0) {
                    return $customerId;
                } else {
                    $testCase->getIdCounter += 1;
                    return null;
                }
            }));
        $customer->expects($this->atLeastOnce())
            ->method('getGroupId')
            ->willReturn($requestedGroupId);
        $customer
            ->method('setGroupId')
            ->willReturnOnConsecutiveCalls(null, $defaultGroupId);
        $customer->expects($this->atLeastOnce())
            ->method('getEmail')
            ->willReturn($customerEmail);
        $customer->expects($this->any())
            ->method('getWebsiteId')
            ->will($this->returnCallback(function () use ($testCase, $websiteId) {
                if ($testCase->getWebsiteIdCounter > 1) {
                    return $websiteId;
                } else {
                    $testCase->getWebsiteIdCounter += 1;
                    return null;
                }
            }));
        $customer->expects($this->once())
            ->method('setWebsiteId')
            ->with($websiteId);
        $customer->expects($this->any())
            ->method('getStoreId')
            ->will($this->returnCallback(function () use ($testCase, $defaultStoreId) {
                if ($testCase->getStoreIdCounter > 0) {
                    return $defaultStoreId;
                } else {
                    $testCase->getStoreIdCounter += 1;
                    return null;
                }
            }));
        $customer->expects($this->once())
            ->method('setStoreId')
            ->with($defaultStoreId);
        $customer->expects($this->once())
            ->method('getAddresses')
            ->willReturn([$address]);
        $customer->expects($this->once())
            ->method('setAddresses')
            ->with(null);
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
        $customerSecure = $this->getMockBuilder(CustomerSecure::class)
            ->addMethods(['setRpToken', 'setRpTokenCreatedAt', 'getPasswordHash'])
            ->disableOriginalConstructor()
            ->getMock();
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
        $this->allowedCountriesReader
            ->expects($this->atLeastOnce())
            ->method('getAllowedCountries')
            ->willReturn(['US' => 'US']);
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

        $customer = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
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

        $this->store->expects($this->any())
            ->method('getId')
            ->willReturn($customerStoreId);

        $this->storeManager
            ->method('getStore')
            ->withConsecutive([], [$customerStoreId])
            ->willReturnOnConsecutiveCalls($this->store, $this->store);

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
            ->withConsecutive(
                [
                    AccountManagement::XML_PATH_REMIND_EMAIL_TEMPLATE,
                    ScopeInterface::SCOPE_STORE,
                    $customerStoreId
                ],
                [
                    AccountManagement::XML_PATH_FORGOT_EMAIL_IDENTITY,
                    ScopeInterface::SCOPE_STORE,
                    $customerStoreId
                ]
            )
            ->willReturnOnConsecutiveCalls($templateIdentifier, $sender);

        $transport = $this->getMockBuilder(TransportInterface::class)
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
        $addressModel = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->addMethods(['setShouldIgnoreValidation'])
            ->getMock();

        /** @var AddressInterface|MockObject $customer */
        $address = $this->getMockForAbstractClass(AddressInterface::class);
        $address->expects($this->once())
            ->method('getId')
            ->willReturn($addressId);

        /** @var Customer|MockObject $customer */
        $customer = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
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
        $transport = $this->getMockBuilder(TransportInterface::class)
            ->getMock();

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
        $this->customerSecure = $this->getMockBuilder(CustomerSecure::class)
            ->disableOriginalConstructor()
            ->addMethods(
                [
                    'getRpToken',
                    'getRpTokenCreatedAt',
                    'getPasswordHash',
                    'setPasswordHash',
                    'setRpToken',
                    'setRpTokenCreatedAt'
                ]
            )
            ->getMock();
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
        $this->sessionManager = $this->getMockBuilder(SessionManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->visitorCollectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->saveHandler = $this->getMockBuilder(SaveHandlerInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['destroy'])
            ->getMockForAbstractClass();

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
        $customer = $this->getMockBuilder(CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
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
        $addressModel = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->addMethods(['setShouldIgnoreValidation'])
            ->getMock();

        /** @var AddressInterface|MockObject $customer */
        $address = $this->getMockForAbstractClass(AddressInterface::class);
        $address->expects($this->any())
            ->method('getId')
            ->willReturn($addressId);

        /** @var Customer|MockObject $customer */
        $customer = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
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
     * @return void
     * @throws LocalizedException
     */
    public function testAuthenticate(): void
    {
        $username = 'login';
        $password = '1234567';
        $passwordHash = '1a2b3f4c';

        $customerData = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $customerModel = $this->getMockBuilder(\Magento\Customer\Model\Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customerModel->expects($this->once())
            ->method('updateData')
            ->willReturn($customerModel);

        $this->customerRepository
            ->expects($this->once())
            ->method('get')
            ->with($username)
            ->willReturn($customerData);

        $this->authenticationMock->expects($this->once())
            ->method('authenticate');

        $customerSecure = $this->getMockBuilder(CustomerSecure::class)
            ->addMethods(['getPasswordHash'])
            ->disableOriginalConstructor()
            ->getMock();
        $customerSecure->expects($this->any())
            ->method('getPasswordHash')
            ->willReturn($passwordHash);

        $this->customerRegistry->expects($this->any())
            ->method('retrieveSecureData')
            ->willReturn($customerSecure);

        $this->customerFactory->expects($this->once())
            ->method('create')
            ->willReturn($customerModel);

        $this->manager->expects($this->exactly(2))
            ->method('dispatch')
            ->withConsecutive(
                [
                    'customer_customer_authenticated',
                    ['model' => $customerModel, 'password' => $password]
                ],
                [
                    'customer_data_object_login', ['customer' => $customerData]
                ]
            );

        $this->assertEquals($customerData, $this->accountManagement->authenticate($username, $password));
    }

    /**
     * @param int $isConfirmationRequired
     * @param string|null $confirmation
     * @param string $expected
     *
     * @return void
     * @dataProvider dataProviderGetConfirmationStatus
     * @throws LocalizedException
     */
    public function testGetConfirmationStatus(
        $isConfirmationRequired,
        $confirmation,
        $expected
    ): void {
        $websiteId = 1;
        $customerId = 1;
        $customerEmail = 'test1@example.com';

        $customerMock = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
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
    public function dataProviderGetConfirmationStatus(): array
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

        $storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $storeMock->method('getId')
            ->willReturn($storeId);
        $this->storeManager->method('getStores')
            ->willReturn([$storeMock]);

        $customerMock = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
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
        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $store->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        //Handle address - existing and non-existing. Non-Existing should return null when call getId method
        $existingAddress = $this->getMockBuilder(AddressInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $nonExistingAddress = $this->getMockBuilder(AddressInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        //Ensure that existing address is not in use
        $this->addressRepository
            ->expects($this->atLeastOnce())
            ->method("save")
            ->withConsecutive(
                [$this->logicalNot($this->identicalTo($existingAddress))],
                [$this->identicalTo($nonExistingAddress)]
            );

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
        $customer = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
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
        $customerSecure = $this->getMockBuilder(CustomerSecure::class)
            ->addMethods(['setRpToken', 'setRpTokenCreatedAt', 'getPasswordHash'])
            ->disableOriginalConstructor()
            ->getMock();
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

        $customer
            ->expects($this->atLeastOnce())
            ->method('getAddresses')
            ->willReturn([$existingAddress, $nonExistingAddress]);

        $this->storeManager
            ->expects($this->atLeastOnce())
            ->method('getStore')
            ->willReturn($store);
        $this->share
            ->expects($this->once())
            ->method('isWebsiteScope')
            ->willReturn(true);
        $website = $this->getMockBuilder(Website::class)
            ->disableOriginalConstructor()
            ->getMock();
        $website->expects($this->once())
            ->method('getStoreIds')
            ->willReturn([1, 2, 3]);
        $this->storeManager
            ->expects($this->atLeastOnce())
            ->method('getWebsite')
            ->with($websiteId)
            ->willReturn($website);
        $this->allowedCountriesReader
            ->expects($this->atLeastOnce())
            ->method('getAllowedCountries')
            ->willReturn(['US' => 'US']);
        $existingAddress
            ->expects($this->atLeastOnce())
            ->method('getCountryId')
            ->willReturn('US');

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

        $address = $this->getMockForAbstractClass(AddressInterface::class);
        $address->expects($this->once())
            ->method('setCustomerId')
            ->with($customerId);
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
            ->will($this->returnCallback(function () use ($testCase, $customerId) {
                if ($testCase->getIdCounter > 0) {
                    return $customerId;
                } else {
                    $testCase->getIdCounter += 1;
                    return null;
                }
            }));
        $customer->expects($this->atLeastOnce())
            ->method('getEmail')
            ->willReturn($customerEmail);
        $customer->expects($this->any())
            ->method('getWebsiteId')
            ->will($this->returnCallback(function () use ($testCase, $websiteId) {
                if ($testCase->getWebsiteIdCounter > 1) {
                    return $websiteId;
                } else {
                    $testCase->getWebsiteIdCounter += 1;
                    return null;
                }
            }));
        $customer->expects($this->once())
            ->method('setWebsiteId')
            ->with($websiteId);
        $customer->expects($this->any())
            ->method('getStoreId')
            ->will($this->returnCallback(function () use ($testCase, $defaultStoreId) {
                if ($testCase->getStoreIdCounter > 0) {
                    return $defaultStoreId;
                } else {
                    $testCase->getStoreIdCounter += 1;
                    return null;
                }
            }));
        $customer->expects($this->once())
            ->method('setStoreId')
            ->with($defaultStoreId);
        $customer->expects($this->once())
            ->method('getAddresses')
            ->willReturn([$address]);
        $customer->expects($this->once())
            ->method('setAddresses')
            ->with(null);
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
        $customerSecure = $this->getMockBuilder(CustomerSecure::class)
            ->addMethods(['setRpToken', 'setRpTokenCreatedAt', 'getPasswordHash'])
            ->disableOriginalConstructor()
            ->getMock();
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
        $this->allowedCountriesReader->expects($this->atLeastOnce())
            ->method('getAllowedCountries')->willReturn(['US' => 'US']);
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
        $customerMock = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
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
        $website = $this->getMockBuilder(Website::class)
            ->disableOriginalConstructor()
            ->getMock();
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
        $customerMock = $this->getMockBuilder(CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customerMock->method('getWebsiteId')->willReturn(1);
        $customerMock->method('getStoreId')->willReturn(1);
        $storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
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

        $customerMock = $this->getMockBuilder(CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $storeMock->method('getId')
            ->willReturn(1);
        $this->storeManager->method('getStores')
            ->willReturn([$storeMock]);

        $this->assertTrue($this->accountManagement->validateCustomerStoreIdByWebsiteId($customerMock));
    }
}
