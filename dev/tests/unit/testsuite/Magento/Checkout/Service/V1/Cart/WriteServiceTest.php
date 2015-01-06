<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Checkout\Service\V1\Cart;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\TestFramework\Helper\ObjectManager;

/**
 * Class WriteServiceTest
 */
class WriteServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Checkout\Service\V1\Cart\WriteService
     */
    protected $service;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $userContextMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteServiceFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerFactoryMock;

    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->storeManagerMock = $this->getMock('\Magento\Store\Model\StoreManagerInterface');
        $this->quoteRepositoryMock = $this->getMock('\Magento\Sales\Model\QuoteRepository', [], [], '', false);
        $this->userContextMock = $this->getMock('\Magento\Authorization\Model\UserContextInterface');

        $this->storeMock = $this->getMock('\Magento\Store\Model\Store', [], [], '', false);
        $this->quoteMock =
            $this->getMock('\Magento\Sales\Model\Quote',
                [
                    'setStoreId',
                    'getId',
                    'getStoreId',
                    'getCustomerId',
                    'setCustomer',
                    'setCustomerIsGuest',
                    '__wakeup',
                ],
                [], '', false);

        $this->customerRepositoryMock = $this->getMock(
            '\Magento\Customer\Api\CustomerRepositoryInterface', [], [], '', false
        );

        $this->customerFactoryMock = $this->getMock(
            'Magento\Customer\Model\CustomerFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->quoteServiceFactory = $this->getMock(
            'Magento\Sales\Model\Service\QuoteFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->service = $this->objectManager->getObject(
            'Magento\Checkout\Service\V1\Cart\WriteService',
            [
                'storeManager' => $this->storeManagerMock,
                'customerRepository' => $this->customerRepositoryMock,
                'quoteRepository' => $this->quoteRepositoryMock,
                'userContext' => $this->userContextMock,
                'quoteServiceFactory' => $this->quoteServiceFactory,
                'customerModelFactory' => $this->customerFactoryMock
            ]
        );
    }

    public function testCreateAnonymousCart()
    {
        $storeId = 345;

        $this->userContextMock->expects($this->once())->method('getUserType')
            ->willReturn(\Magento\Authorization\Model\UserContextInterface::USER_TYPE_ADMIN);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->will($this->returnValue($this->storeMock));
        $this->storeMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($storeId));

        $this->quoteRepositoryMock->expects($this->once())->method('create')->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())->method('setStoreId')->with($storeId);
        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($this->quoteMock);
        $this->quoteMock->expects($this->once())->method('getId')->willReturn(100);
        $this->assertEquals(100, $this->service->create());
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Cannot create quote
     */
    public function testCreateCustomerCartWhenCustomerHasActiveCart()
    {
        $storeId = 345;
        $userId = 50;

        $customerMock = $this->getMockForAbstractClass(
            'Magento\Customer\Api\Data\CustomerInterface',
            [],
            '',
            false
        );
        $this->customerRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($userId)
            ->will($this->returnValue($customerMock));

        $this->userContextMock->expects($this->once())->method('getUserType')
            ->willReturn(\Magento\Authorization\Model\UserContextInterface::USER_TYPE_CUSTOMER);
        $this->userContextMock->expects($this->any())->method('getUserId')->willReturn($userId);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->will($this->returnValue($this->storeMock));
        $this->storeMock->expects($this->once())->method('getId')->will($this->returnValue($storeId));

        $customerQuoteMock = $this->getMock('\Magento\Sales\Model\Quote',
            [
                'getIsActive',
                'getId',
                '__wakeup'
            ],
            [],
            '',
            false
        );
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActiveForCustomer')
            ->with($userId)
            ->willReturn($customerQuoteMock);
        $this->quoteRepositoryMock->expects($this->never())->method('save')->with($this->quoteMock);

        $this->service->create();
    }

    public function testCreateCustomerCart()
    {
        $storeId = 345;
        $userId = 50;

        $customerMock = $this->getMockForAbstractClass(
            'Magento\Customer\Api\Data\CustomerInterface',
            [],
            '',
            false
        );
        $this->customerRepositoryMock->expects($this->once())
            ->method('getById')->with($userId)->will($this->returnValue($customerMock));
        $this->userContextMock->expects($this->once())->method('getUserType')
            ->willReturn(\Magento\Authorization\Model\UserContextInterface::USER_TYPE_CUSTOMER);
        $this->userContextMock->expects($this->any())->method('getUserId')->willReturn($userId);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->will($this->returnValue($this->storeMock));
        $this->storeMock->expects($this->once())->method('getId')->will($this->returnValue($storeId));

        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActiveForCustomer')
            ->with($userId)
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException());
        $this->quoteRepositoryMock->expects($this->once())->method('create')->willReturn($this->quoteMock);

        $this->quoteMock->expects($this->once())->method('setStoreId')->with($storeId);
        $this->quoteMock->expects($this->once())->method('setCustomer')->with($customerMock);
        $this->quoteMock->expects($this->once())->method('setCustomerIsGuest')->with(0);
        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($this->quoteMock);
        $this->quoteMock->expects($this->once())->method('getId')->willReturn(100);
        $this->assertEquals(100, $this->service->create());
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Cannot create quote
     */
    public function testCreateWithException()
    {
        $storeId = 345;

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->will($this->returnValue($this->storeMock));
        $this->storeMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($storeId));

        $this->quoteRepositoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('setStoreId')
            ->with($storeId);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('save')
            ->will($this->throwException(new CouldNotSaveException('Cannot create quote')));

        $this->service->create();
    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Cannot assign customer to the given cart. The cart belongs to different store.
     */
    public function testAssignCustomerStateExceptionWithStoreId()
    {
        $cartId = 956;
        $customerId = 125;
        $storeId = 12;

        $this->storeManagerMock->expects($this->once())->method('getStore')->will($this->returnValue($this->storeMock));
        $this->storeMock->expects($this->once())->method('getId')->will($this->returnValue($storeId));
        $this->quoteRepositoryMock->expects($this->once())->method('getActive')->with($cartId)
            ->will($this->returnValue($this->quoteMock));
        $customerMock = $this->getMockForAbstractClass(
            'Magento\Customer\Api\Data\CustomerInterface',
            [],
            '',
            false
        );
        $this->customerRepositoryMock->expects($this->once())
            ->method('getById')->with($customerId)->will($this->returnValue($customerMock));
        $customerModelMock = $this->getMockBuilder('Magento\Customer\Model\Customer')
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getSharedStoreIds'])
            ->getMock();
        $this->customerFactoryMock->expects($this->once())->method('create')->willReturn($customerModelMock);
        $customerModelMock->expects($this->once())
            ->method('load')
            ->with($customerId)
            ->willReturnSelf();
        $customerModelMock->expects($this->once())->method('getSharedStoreIds')->will(
            $this->returnValue([11])
        );

        $this->service->assignCustomer($cartId, $customerId);
    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Cannot assign customer to the given cart. The cart is not anonymous.
     */
    public function testAssignCustomerStateExceptionWithCustomerId()
    {
        $cartId = 956;
        $customerId = 125;
        $storeId = 12;

        $this->quoteRepositoryMock->expects($this->once())->method('getActive')->with($cartId)
            ->will($this->returnValue($this->quoteMock));
        $this->storeManagerMock->expects($this->once())->method('getStore')->will($this->returnValue($this->storeMock));
        $this->storeMock->expects($this->once())->method('getId')->will($this->returnValue($storeId));
        $customerMock = $this->getMockForAbstractClass(
            'Magento\Customer\Api\Data\CustomerInterface',
            [],
            '',
            false
        );
        $this->customerRepositoryMock->expects($this->once())
            ->method('getById')->with($customerId)->will($this->returnValue($customerMock));

        $customerModelMock = $this->getMockBuilder('Magento\Customer\Model\Customer')
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getSharedStoreIds'])
            ->getMock();
        $this->customerFactoryMock->expects($this->once())->method('create')->willReturn($customerModelMock);
        $customerModelMock->expects($this->once())
            ->method('load')
            ->with($customerId)
            ->willReturnSelf();
        $customerModelMock->expects($this->once())->method('getSharedStoreIds')->will(
            $this->returnValue([$storeId])
        );
        $this->quoteMock->expects($this->once())->method('getCustomerId')->will($this->returnValue($customerId));
        $this->quoteMock->expects($this->never())->method('setCustomer');

        $this->service->assignCustomer($cartId, $customerId);
    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Cannot assign customer to the given cart. Customer already has active cart.
     */
    public function testAssignCustomerStateExceptionWithAlreadyAssignedCustomer()
    {
        $cartId = 956;
        $customerId = 125;
        $storeId = 12;

        $this->storeManagerMock->expects($this->once())->method('getStore')->will($this->returnValue($this->storeMock));
        $this->storeMock->expects($this->once())->method('getId')->will($this->returnValue($storeId));
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quoteMock));

        $customerModelMock = $this->getMockBuilder('Magento\Customer\Model\Customer')
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getSharedStoreIds'])
            ->getMock();
        $this->customerFactoryMock->expects($this->once())->method('create')->willReturn($customerModelMock);
        $customerModelMock->expects($this->once())->method('getSharedStoreIds')->will($this->returnValue([$storeId]));
        $customerModelMock->expects($this->once())
            ->method('load')
            ->with($customerId)
            ->willReturnSelf();
        $this->quoteMock->expects($this->once())->method('getCustomerId')->will($this->returnValue(null));

        $customerQuoteMock = $this->getMock('\Magento\Sales\Model\Quote', [], [], '', false);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getForCustomer')
            ->with($customerId)
            ->will($this->returnValue($customerQuoteMock));

        $this->quoteMock->expects($this->never())->method('setCustomer');

        $this->service->assignCustomer($cartId, $customerId);
    }

    public function testAssignCustomer()
    {
        $cartId = 956;
        $customerId = 125;
        $storeId = 12;

        $this->storeManagerMock->expects($this->once())->method('getStore')->will($this->returnValue($this->storeMock));
        $this->storeMock->expects($this->once())->method('getId')->will($this->returnValue($storeId));
        $this->quoteRepositoryMock->expects($this->once())->method('getActive')->with($cartId)
            ->will($this->returnValue($this->quoteMock));
        $customerMock = $this->getMockForAbstractClass(
            'Magento\Customer\Api\Data\CustomerInterface',
            [],
            '',
            false,
            true,
            true
        );
        $this->customerRepositoryMock->expects($this->once())
            ->method('getById')->with($customerId)->will($this->returnValue($customerMock));

        $this->quoteRepositoryMock->expects($this->once())
            ->method('getForCustomer')
            ->with($customerId)
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException());

        $customerModelMock = $this->getMockBuilder('Magento\Customer\Model\Customer')
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getSharedStoreIds'])
            ->getMock();
        $this->customerFactoryMock->expects($this->once())->method('create')->willReturn($customerModelMock);
        $customerModelMock->expects($this->once())->method('getSharedStoreIds')->will($this->returnValue([$storeId]));
        $customerModelMock->expects($this->once())
            ->method('load')
            ->with($customerId)
            ->willReturnSelf();
        $this->quoteMock->expects($this->once())->method('getCustomerId')->will($this->returnValue(false));
        $this->quoteMock->expects($this->once())
            ->method('setCustomer')->with($customerMock)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('setCustomerIsGuest')->with(0)->will($this->returnValue($this->quoteMock));
        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($this->quoteMock);

        $this->assertTrue($this->service->assignCustomer($cartId, $customerId));
    }

    public function testOrder()
    {
        $cartId = 123;
        $quoteService = $this->getMock('Magento\Sales\Model\Service\Quote', [], [], '', false);
        $this->quoteRepositoryMock->expects($this->once())->method('getActive')->with($cartId)
            ->will($this->returnValue($this->quoteMock));
        $this->quoteServiceFactory->expects($this->once())->method('create')->with(['quote' => $this->quoteMock])
            ->will($this->returnValue($quoteService));
        $orderMock = $this->getMock('Magento\Sales\Model\Order', [], [], '', false);
        $orderMock->expects($this->any())->method('getId')->will($this->returnValue(5));
        $quoteService->expects($this->once())->method('submitOrderWithDataObject')
            ->will($this->returnValue($orderMock));
        $this->assertEquals(5, $this->service->order($cartId));
    }
}
