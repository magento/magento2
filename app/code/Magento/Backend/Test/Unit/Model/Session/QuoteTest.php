<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Model\Session;

use Magento\Backend\Model\Session\Quote;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\State;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Session\Config\ConfigInterface;
use Magento\Framework\Session\SaveHandlerInterface;
use Magento\Framework\Session\SidResolverInterface;
use Magento\Framework\Session\Storage;
use Magento\Framework\Session\StorageInterface;
use Magento\Framework\Session\ValidatorInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Session\SessionStartChecker;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote as QuoteModel;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Model\OrderFactory;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class QuoteTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManagerMock;

    /**
     * @var OrderFactory|MockObject
     */
    protected $orderFactoryMock;

    /**
     * @var CookieMetadataFactory|MockObject
     */
    protected $cookieMetadataFactoryMock;

    /**
     * @var CookieManagerInterface|MockObject
     */
    protected $cookieManagerMock;

    /**
     * @var StorageInterface
     */
    protected $storage;

    /**
     * @var ValidatorInterface|MockObject
     */
    protected $validatorMock;

    /**
     * @var SaveHandlerInterface|MockObject
     */
    protected $saveHandlerMock;

    /**
     * @var ConfigInterface|MockObject
     */
    protected $sessionConfigMock;

    /**
     * @var SidResolverInterface|MockObject
     */
    protected $sidResolverMock;

    /**
     * @var Http|MockObject
     */
    protected $requestMock;

    /**
     * @var CustomerRepositoryInterface|MockObject
     */
    protected $customerRepositoryMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var QuoteFactory|MockObject
     */
    protected $quoteRepositoryMock;

    /**
     * @var Quote|MockObject
     */
    protected $quote;

    /**
     * @var GroupManagementInterface|MockObject
     */
    protected $groupManagementMock;

    /**
     * @var MockObject
     */
    protected $quoteFactoryMock;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManagerMock;

    /**
     * Set up
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->customerRepositoryMock = $this->createMock(CustomerRepositoryInterface::class);

        $this->groupManagementMock = $this->createMock(GroupManagementInterface::class);

        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);

        $this->quoteRepositoryMock = $this->createMock(CartRepositoryInterface::class);

        $this->requestMock = $this->createMock(Http::class);
        $this->sidResolverMock = $this->createMock(SidResolverInterface::class);

        $this->sessionConfigMock = $this->createMock(ConfigInterface::class);

        $this->saveHandlerMock = $this->createMock(SaveHandlerInterface::class);

        $this->validatorMock = $this->createMock(ValidatorInterface::class);

        $this->storage = new Storage();
        $this->cookieManagerMock = $this->createMock(CookieManagerInterface::class);
        $this->cookieMetadataFactoryMock = $this->createMock(
            CookieMetadataFactory::class
        );
        $this->orderFactoryMock = $this->createPartialMock(OrderFactory::class, ['create']);
        $appStateMock = $this->createMock(State::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);

        $this->quoteFactoryMock = $this->createPartialMock(QuoteFactory::class, ['create']);

        $objects = [
            [
                SessionStartChecker::class,
                $this->createMock(SessionStartChecker::class)
            ]
        ];
        $this->objectManager->prepareObjectManager($objects);

        // Create a partial mock with the magic methods using reflection
        $mockBuilder = $this->getMockBuilder(Quote::class);
        $mockBuilder->setConstructorArgs([
            'request' => $this->requestMock,
            'sidResolver' => $this->sidResolverMock,
            'sessionConfig' => $this->sessionConfigMock,
            'saveHandler' => $this->saveHandlerMock,
            'validator' => $this->validatorMock,
            'storage' => $this->storage,
            'cookieManager' => $this->cookieManagerMock,
            'cookieMetadataFactory' => $this->cookieMetadataFactoryMock,
            'appState' => $appStateMock,
            'customerRepository' => $this->customerRepositoryMock,
            'quoteRepository' => $this->quoteRepositoryMock,
            'orderFactory' => $this->orderFactoryMock,
            'storeManager' => $this->storeManagerMock,
            'groupManagement' => $this->groupManagementMock,
            'quoteFactory' => $this->quoteFactoryMock
        ]);

        // Use reflection to set methods property for magic methods
        $builderReflection = new \ReflectionClass($mockBuilder);
        $methodsProperty = $builderReflection->getProperty('methods');
        $methodsProperty->setValue(
            $mockBuilder,
            ['getStoreId', 'getQuoteId', 'setQuoteId', 'hasCustomerId', 'getCustomerId']
        );

        $this->quote = $mockBuilder->getMock();
    }

    /**
     * Run test getQuote method
     *
     * @return void
     */
    public function testGetQuoteWithoutQuoteId()
    {
        $quoteId = 22;
        $storeId = 10;
        $customerId = 66;
        $customerGroupId = 77;
        $this->quote->method('getQuoteId')->willReturn(null);
        $this->quote->method('setQuoteId')->with($quoteId);
        $cartInterfaceMock = $this->createPartialMockWithReflection(
            CartInterface::class,
            [
                'setIgnoreOldQty', 'setIsSuperMode', 'setCustomerGroupId',
                'getId', 'setId', 'getCreatedAt', 'setCreatedAt', 'getUpdatedAt', 'setUpdatedAt',
                'getConvertedAt', 'setConvertedAt', 'getIsActive', 'setIsActive', 'getIsVirtual',
                'getItems', 'setItems', 'getItemsCount', 'setItemsCount', 'getItemsQty', 'setItemsQty',
                'getCustomer', 'setCustomer', 'getBillingAddress', 'setBillingAddress',
                'getReservedOrderId', 'setReservedOrderId', 'getOrigOrderId', 'setOrigOrderId',
                'getCurrency', 'setCurrency', 'getCustomerIsGuest', 'setCustomerIsGuest',
                'getCustomerNote', 'setCustomerNote', 'getCustomerNoteNotify', 'setCustomerNoteNotify',
                'getCustomerTaxClassId', 'setCustomerTaxClassId', 'getStoreId', 'setStoreId',
                'getExtensionAttributes', 'setExtensionAttributes'
            ]
        );
        $this->quoteFactoryMock->expects($this->once())->method('create')->willReturn($cartInterfaceMock);
        $this->quote->method('getStoreId')->willReturn($storeId);
        $this->quote->expects($this->any())->method('getCustomerId')->willReturn($customerId);
        $cartInterfaceMock->method('getId')->willReturn($quoteId);
        $defaultGroup = $this->createMock(GroupInterface::class);
        $defaultGroup->method('getId')->willReturn($customerGroupId);
        $this->groupManagementMock
            ->method('getDefaultGroup')
            ->with($storeId)
            ->willReturn($defaultGroup);

        $dataCustomerMock = $this->createMock(CustomerInterface::class);
        $this->customerRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($dataCustomerMock);

        $quoteMock = $this->createPartialMockWithReflection(
            QuoteModel::class,
            [
                'setCustomerGroupId', 'setIgnoreOldQty', 'setIsSuperMode', 'setStoreId',
                'setIsActive', 'assignCustomer', '__wakeup'
            ]
        );

        $this->quoteRepositoryMock->expects($this->once())->method('get')->willReturn($quoteMock);
        $cartInterfaceMock->expects($this->once())->method('setCustomerGroupId')->with($customerGroupId)
            ->willReturnSelf();
        $quoteMock->expects($this->once())->method('assignCustomer')->with($dataCustomerMock);
        $quoteMock->expects($this->once())->method('setIgnoreOldQty')->with(true);
        $quoteMock->expects($this->once())->method('setIsSuperMode')->with(true);
        $this->assertEquals($quoteMock, $this->quote->getQuote());
    }

    /**
     * Run test getQuote method
     *
     * @return void
     */
    #[DataProvider('getQuoteDataProvider')]
    public function testGetQuoteWithQuoteId($customerId, $quoteCustomerId, $expectedNumberOfInvokes)
    {
        $quoteId = 22;
        $storeId = 10;

        $this->quote->method('getQuoteId')
            ->willReturn($quoteId);
        $this->quote->method('setQuoteId')
            ->with($quoteId);
        $this->quote->method('getStoreId')
            ->willReturn($storeId);
        $this->quote->method('getCustomerId')
            ->willReturn($customerId);

        $dataCustomerMock = $this->createMock(CustomerInterface::class);
        $this->customerRepositoryMock->expects($this->$expectedNumberOfInvokes())
            ->method('getById')
            ->with($customerId)
            ->willReturn($dataCustomerMock);

        $quoteMock = $this->createPartialMockWithReflection(
            QuoteModel::class,
            [
                'setCustomerGroupId', 'setIgnoreOldQty', 'setIsSuperMode', 'getCustomerId',
                'setStoreId', 'setIsActive', 'getId', 'assignCustomer', '__wakeup'
            ]
        );
        $quoteMock->expects($this->once())
            ->method('setStoreId')
            ->with($storeId);
        $quoteMock->expects($this->$expectedNumberOfInvokes())
            ->method('assignCustomer')
            ->with($dataCustomerMock);
        $quoteMock->expects($this->once())
            ->method('setIgnoreOldQty')
            ->with(true);
        $quoteMock->expects($this->once())
            ->method('setIsSuperMode')
            ->with(true);
        $quoteMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($quoteCustomerId);

        $this->quoteRepositoryMock->expects($this->once())
            ->method('get')
            ->with($quoteId)
            ->willReturn($quoteMock);

        $this->assertEquals($quoteMock, $this->quote->getQuote());
    }

    /**
     * @return array
     */
    public static function getQuoteDataProvider()
    {
        return [
            'customer ids different' => [66, null, 'once'],
            'customer ids same' => [66, 66, 'never'],
        ];
    }
}
