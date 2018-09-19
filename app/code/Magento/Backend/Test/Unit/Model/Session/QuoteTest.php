<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Model\Session;

/**
 * Class QuoteTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class QuoteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Sales\Model\OrderFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderFactoryMock;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cookieMetadataFactoryMock;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cookieManagerMock;

    /**
     * @var \Magento\Framework\Session\StorageInterface
     */
    protected $storage;

    /**
     * @var \Magento\Framework\Session\ValidatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $validatorMock;

    /**
     * @var \Magento\Framework\Session\SaveHandlerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $saveHandlerMock;

    /**
     * @var \Magento\Framework\Session\Config\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionConfigMock;

    /**
     * @var \Magento\Framework\Session\SidResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sidResolverMock;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerRepositoryMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Quote\Model\QuoteFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteRepositoryMock;

    /**
     * @var \Magento\Backend\Model\Session\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quote;

    /**
     * @var \Magento\Customer\Api\GroupManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupManagementMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteFactoryMock;

    /**
     * Set up
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->customerRepositoryMock = $this->getMockForAbstractClass(
            \Magento\Customer\Api\CustomerRepositoryInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getCustomer']
        );
        $this->groupManagementMock = $this->getMockForAbstractClass(
            \Magento\Customer\Api\GroupManagementInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getDefaultGroup']
        );

        $this->scopeConfigMock = $this->getMockForAbstractClass(
            \Magento\Framework\App\Config\ScopeConfigInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getValue']
        );
        $this->quoteRepositoryMock = $this->createMock(\Magento\Quote\Api\CartRepositoryInterface::class);

        $this->requestMock = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->sidResolverMock = $this->getMockForAbstractClass(
            \Magento\Framework\Session\SidResolverInterface::class,
            [],
            '',
            false
        );
        $this->sessionConfigMock = $this->getMockForAbstractClass(
            \Magento\Framework\Session\Config\ConfigInterface::class,
            [],
            '',
            false
        );
        $this->saveHandlerMock = $this->getMockForAbstractClass(
            \Magento\Framework\Session\SaveHandlerInterface::class,
            [],
            '',
            false
        );
        $this->validatorMock = $this->getMockForAbstractClass(
            \Magento\Framework\Session\ValidatorInterface::class,
            [],
            '',
            false
        );
        $this->storage = new \Magento\Framework\Session\Storage();
        $this->cookieManagerMock = $this->createMock(\Magento\Framework\Stdlib\CookieManagerInterface::class);
        $this->cookieMetadataFactoryMock = $this->createMock(
            \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class
        );
        $this->orderFactoryMock = $this->createPartialMock(\Magento\Sales\Model\OrderFactory::class, ['create']);
        $appStateMock = $this->createMock(\Magento\Framework\App\State::class);
        $this->storeManagerMock = $this->getMockForAbstractClass(
            \Magento\Store\Model\StoreManagerInterface::class,
            [],
            '',
            false
        );

        $this->quoteFactoryMock = $this->createPartialMock(\Magento\Quote\Model\QuoteFactory::class, ['create']);

        $this->quote = $this->getMockBuilder(\Magento\Backend\Model\Session\Quote::class)
            ->setMethods(['getStoreId', 'getQuoteId', 'setQuoteId', 'hasCustomerId', 'getCustomerId'])
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
        $this->quote->expects($this->any())->method('getQuoteId')->will($this->returnValue(null));
        $this->quote->expects($this->any())->method('setQuoteId')->with($quoteId);
        $cartInterfaceMock = $this->createPartialMock(
            \Magento\Quote\Api\Data\CartInterface::class,
            [
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
                'setExtensionAttributes',
                'setIgnoreOldQty',
                'setIsSuperMode',
                'setCustomerGroupId'
            ]
        );
        $this->quoteFactoryMock->expects($this->once())->method('create')->willReturn($cartInterfaceMock);
        $this->quote->expects($this->any())->method('getStoreId')->will($this->returnValue($storeId));
        $this->quote->expects($this->any())->method('getCustomerId')->will($this->returnValue($customerId));
        $cartInterfaceMock->expects($this->atLeastOnce())->method('getId')->willReturn($quoteId);
        $defaultGroup = $this->getMockBuilder(\Magento\Customer\Api\Data\GroupInterface::class)->getMock();
        $defaultGroup->expects($this->any())->method('getId')->will($this->returnValue($customerGroupId));
        $this->groupManagementMock->expects($this->any())->method('getDefaultGroup')->willReturn($defaultGroup);

        $dataCustomerMock = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($dataCustomerMock);

        $quoteMock = $this->createPartialMock(\Magento\Quote\Model\Quote::class, [
                'setStoreId',
                'setCustomerGroupId',
                'setIsActive',
                'assignCustomer',
                'setIgnoreOldQty',
                'setIsSuperMode',
                '__wakeup'
            ]);

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
            ->will($this->returnValue($quoteId));
        $this->quote->expects($this->any())
            ->method('setQuoteId')
            ->with($quoteId);
        $this->quote->expects($this->any())
            ->method('getStoreId')
            ->will($this->returnValue($storeId));
        $this->quote->expects($this->any())
            ->method('getCustomerId')
            ->will($this->returnValue($customerId));

        $dataCustomerMock = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerRepositoryMock->expects($this->$expectedNumberOfInvokes())
            ->method('getById')
            ->with($customerId)
            ->willReturn($dataCustomerMock);

        $quoteMock = $this->createPartialMock(\Magento\Quote\Model\Quote::class, [
                'setStoreId',
                'setCustomerGroupId',
                'setIsActive',
                'getId',
                'assignCustomer',
                'setIgnoreOldQty',
                'setIsSuperMode',
                'getCustomerId',
                '__wakeup'
            ]);
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
            ->will($this->returnValue($quoteCustomerId));

        $this->quoteRepositoryMock->expects($this->once())
            ->method('get')
            ->with($quoteId)
            ->willReturn($quoteMock);

        $this->assertEquals($quoteMock, $this->quote->getQuote());
    }

    /**
     * @return array
     */
    public function getQuoteDataProvider()
    {
        return [
            'customer ids different' => [66, null, 'once'],
            'customer ids same' => [66, 66, 'never'],
        ];
    }
}
