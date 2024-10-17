<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Session\SessionStartChecker;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Model\OrderFactory;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class QuoteTest extends TestCase
{
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
        $this->customerRepositoryMock = $this->getMockForAbstractClass(
            CustomerRepositoryInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getCustomer']
        );
        $this->groupManagementMock = $this->getMockForAbstractClass(
            GroupManagementInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getDefaultGroup']
        );

        $this->scopeConfigMock = $this->getMockForAbstractClass(
            ScopeConfigInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getValue']
        );
        $this->quoteRepositoryMock = $this->getMockForAbstractClass(CartRepositoryInterface::class);

        $this->requestMock = $this->createMock(Http::class);
        $this->sidResolverMock = $this->getMockForAbstractClass(
            SidResolverInterface::class,
            [],
            '',
            false
        );
        $this->sessionConfigMock = $this->getMockForAbstractClass(
            ConfigInterface::class,
            [],
            '',
            false
        );
        $this->saveHandlerMock = $this->getMockForAbstractClass(
            SaveHandlerInterface::class,
            [],
            '',
            false
        );
        $this->validatorMock = $this->getMockForAbstractClass(
            ValidatorInterface::class,
            [],
            '',
            false
        );
        $this->storage = new Storage();
        $this->cookieManagerMock = $this->getMockForAbstractClass(CookieManagerInterface::class);
        $this->cookieMetadataFactoryMock = $this->createMock(
            CookieMetadataFactory::class
        );
        $this->orderFactoryMock = $this->createPartialMock(OrderFactory::class, ['create']);
        $appStateMock = $this->createMock(State::class);
        $this->storeManagerMock = $this->getMockForAbstractClass(
            StoreManagerInterface::class,
            [],
            '',
            false
        );

        $this->quoteFactoryMock = $this->createPartialMock(QuoteFactory::class, ['create']);

        $objects = [
            [
                SessionStartChecker::class,
                $this->createMock(SessionStartChecker::class)
            ]
        ];
        $this->objectManager->prepareObjectManager($objects);

        $this->quote = $this->getMockBuilder(Quote::class)
            ->addMethods(['getStoreId', 'getQuoteId', 'setQuoteId', 'hasCustomerId', 'getCustomerId'])
            ->setConstructorArgs(
                [
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
                ]
            )
            ->getMock();
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
        $this->quote->expects($this->any())->method('getQuoteId')->willReturn(null);
        $this->quote->expects($this->any())->method('setQuoteId')->with($quoteId);
        $cartInterfaceMock = $this->getMockBuilder(CartInterface::class)
            ->addMethods(['setIgnoreOldQty', 'setIsSuperMode', 'setCustomerGroupId'])
            ->onlyMethods([
                'getId',
                'setId',
                'getCreatedAt',
                'setCreatedAt',
                'getUpdatedAt',
                'setUpdatedAt',
                'getConvertedAt',
                'setConvertedAt',
                'getIsActive',
                'setIsActive',
                'getIsVirtual',
                'getItems',
                'setItems',
                'getItemsCount',
                'setItemsCount',
                'getItemsQty',
                'setItemsQty',
                'getCustomer',
                'setCustomer',
                'getBillingAddress',
                'setBillingAddress',
                'getReservedOrderId',
                'setReservedOrderId',
                'getOrigOrderId',
                'setOrigOrderId',
                'getCurrency',
                'setCurrency',
                'getCustomerIsGuest',
                'setCustomerIsGuest',
                'getCustomerNote',
                'setCustomerNote',
                'getCustomerNoteNotify',
                'setCustomerNoteNotify',
                'getCustomerTaxClassId',
                'setCustomerTaxClassId',
                'getStoreId',
                'setStoreId',
                'getExtensionAttributes',
                'setExtensionAttributes'
            ])
            ->getMockForAbstractClass();
        $this->quoteFactoryMock->expects($this->once())->method('create')->willReturn($cartInterfaceMock);
        $this->quote->expects($this->any())->method('getStoreId')->willReturn($storeId);
        $this->quote->expects($this->any())->method('getCustomerId')->willReturn($customerId);
        $cartInterfaceMock->expects($this->atLeastOnce())->method('getId')->willReturn($quoteId);
        $defaultGroup = $this->getMockBuilder(GroupInterface::class)
            ->getMock();
        $defaultGroup->expects($this->any())->method('getId')->willReturn($customerGroupId);
        $this->groupManagementMock
            ->method('getDefaultGroup')
            ->with($storeId)
            ->willReturn($defaultGroup);

        $dataCustomerMock = $this->getMockBuilder(CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->customerRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($dataCustomerMock);

        $quoteMock = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)->addMethods([
            'setCustomerGroupId',
            'setIgnoreOldQty',
            'setIsSuperMode'
        ])
            ->onlyMethods(['setStoreId', 'setIsActive', 'assignCustomer', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

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
     * @dataProvider getQuoteDataProvider
     */
    public function testGetQuoteWithQuoteId($customerId, $quoteCustomerId, $expectedNumberOfInvokes)
    {
        $quoteId = 22;
        $storeId = 10;

        $this->quote->expects($this->any())
            ->method('getQuoteId')
            ->willReturn($quoteId);
        $this->quote->expects($this->any())
            ->method('setQuoteId')
            ->with($quoteId);
        $this->quote->expects($this->any())
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->quote->expects($this->any())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $dataCustomerMock = $this->getMockBuilder(CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->customerRepositoryMock->expects($this->$expectedNumberOfInvokes())
            ->method('getById')
            ->with($customerId)
            ->willReturn($dataCustomerMock);

        $quoteMock = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)->addMethods([
            'setCustomerGroupId',
            'setIgnoreOldQty',
            'setIsSuperMode',
            'getCustomerId'
        ])
            ->onlyMethods(['setStoreId', 'setIsActive', 'getId', 'assignCustomer', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
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
