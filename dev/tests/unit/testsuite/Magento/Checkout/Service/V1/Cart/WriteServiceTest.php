<?php
/**
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Checkout\Service\V1\Cart;

use Magento\TestFramework\Helper\ObjectManager;
use Magento\Framework\Exception\CouldNotSaveException;

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
    protected $quoteFactoryMock;

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
    protected $customerRegistryMock;

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

    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->quoteFactoryMock = $this->getMock(
            '\Magento\Sales\Model\QuoteFactory', ['create', '__wakeup'], [], '', false
        );
        $this->storeManagerMock = $this->getMock('\Magento\Framework\StoreManagerInterface');
        $this->quoteRepositoryMock = $this->getMock('\Magento\Sales\Model\QuoteRepository', [], [], '', false);
        $this->userContextMock = $this->getMock('\Magento\Authorization\Model\UserContextInterface');

        $this->storeMock = $this->getMock('\Magento\Store\Model\Store', [], [], '', false);
        $this->quoteMock =
            $this->getMock('\Magento\Sales\Model\Quote',
                [
                    'setStoreId',
                    'save',
                    'load',
                    'getId',
                    'getStoreId',
                    'getCustomerId',
                    'setCustomer',
                    'setCustomerIsGuest',
                    '__wakeup'
                ],
                [], '', false);

        $this->customerRegistryMock =
            $this->getMock('\Magento\Customer\Model\CustomerRegistry', [], [], '', false);
        $this->quoteServiceFactory = $this->getMock(
            'Magento\Sales\Model\Service\QuoteFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->service = $this->objectManager->getObject(
            '\Magento\Checkout\Service\V1\Cart\WriteService',
            [
                'quoteFactory' => $this->quoteFactoryMock,
                'storeManager' => $this->storeManagerMock,
                'customerRegistry' => $this->customerRegistryMock,
                'quoteRepository' => $this->quoteRepositoryMock,
                'userContext' => $this->userContextMock,
                'quoteServiceFactory' => $this->quoteServiceFactory
            ]
        );
    }

    public function testCreateAnonymousCart()
    {
        $storeId = 345;

        $this->userContextMock->expects($this->once())->method('getUserType')
            ->willReturn(\Magento\Authorization\Model\UserContextInterface::USER_TYPE_ADMIN);
        $this->storeManagerMock->expects($this->once())->method('getStore')->will($this->returnValue($this->storeMock));
        $this->storeMock->expects($this->once())->method('getId')->will($this->returnValue($storeId));

        $this->quoteFactoryMock->expects($this->once())->method('create')->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())->method('setStoreId')->with($storeId);
        $this->quoteMock->expects($this->once())->method('save');
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

        $customerMock = $this->getMock('\Magento\Customer\Model\Customer', [], [], '', false);
        $this->customerRegistryMock->expects($this->once())
            ->method('retrieve')->with($userId)->will($this->returnValue($customerMock));

        $this->userContextMock->expects($this->once())->method('getUserType')
            ->willReturn(\Magento\Authorization\Model\UserContextInterface::USER_TYPE_CUSTOMER);
        $this->userContextMock->expects($this->once())->method('getUserId')->willReturn($userId);
        $this->storeManagerMock->expects($this->once())->method('getStore')->will($this->returnValue($this->storeMock));
        $this->storeMock->expects($this->once())->method('getId')->will($this->returnValue($storeId));

        $customerQuoteMock = $this->getMock('\Magento\Sales\Model\Quote',
            [
                'loadByCustomer',
                'getIsActive',
                'getId',
                '__wakeup'
            ],
            [],
            '',
            false
        );
        $customerQuoteMock->expects($this->once())
            ->method('loadByCustomer')
            ->with($customerMock)
            ->will($this->returnSelf());
        $this->quoteFactoryMock->expects($this->once())->method('create')->willReturn($customerQuoteMock);
        $customerQuoteMock->expects($this->once())->method('getId')->willReturn(1);
        $customerQuoteMock->expects($this->once())->method('getIsActive')->willReturn(true);
        $this->quoteMock->expects($this->never())->method('save');

        $this->service->create();
    }

    public function testCreateCustomerCart()
    {
        $storeId = 345;
        $userId = 50;

        $customerMock = $this->getMock('\Magento\Customer\Model\Customer', [], [], '', false);
        $this->customerRegistryMock->expects($this->once())
            ->method('retrieve')->with($userId)->will($this->returnValue($customerMock));

        $this->userContextMock->expects($this->once())->method('getUserType')
            ->willReturn(\Magento\Authorization\Model\UserContextInterface::USER_TYPE_CUSTOMER);
        $this->userContextMock->expects($this->once())->method('getUserId')->willReturn($userId);
        $this->storeManagerMock->expects($this->once())->method('getStore')->will($this->returnValue($this->storeMock));
        $this->storeMock->expects($this->once())->method('getId')->will($this->returnValue($storeId));

        $customerQuoteMock = $this->getMock('\Magento\Sales\Model\Quote',
            [
                'loadByCustomer',
                'getIsActive',
                'getId',
                '__wakeup'
            ],
            [],
            '',
            false
        );
        $customerQuoteMock->expects($this->once())
            ->method('loadByCustomer')
            ->with($customerMock)
            ->will($this->returnSelf());
        $this->quoteFactoryMock->expects($this->at(0))->method('create')->willReturn($customerQuoteMock);
        $this->quoteFactoryMock->expects($this->at(1))->method('create')->willReturn($this->quoteMock);
        $customerQuoteMock->expects($this->once())->method('getId')->willReturn(1);
        $customerQuoteMock->expects($this->once())->method('getIsActive')->willReturn(false);

        $this->quoteMock->expects($this->once())->method('setStoreId')->with($storeId);
        $this->quoteMock->expects($this->once())->method('setCustomer')->with($customerMock);
        $this->quoteMock->expects($this->once())->method('setCustomerIsGuest')->with(0);
        $this->quoteMock->expects($this->once())->method('save');
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

        $this->storeManagerMock->expects($this->once())->method('getStore')->will($this->returnValue($this->storeMock));
        $this->storeMock->expects($this->once())->method('getId')->will($this->returnValue($storeId));

        $this->quoteFactoryMock->expects($this->once())->method('create')->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())->method('setStoreId')->with($storeId);
        $this->quoteMock->expects($this->once())->method('save')
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
        $this->quoteRepositoryMock->expects($this->once())->method('get')->with($cartId)
            ->will($this->returnValue($this->quoteMock));
        $customerMock = $this->getMock('\Magento\Customer\Model\Customer', [], [], '', false);
        $this->customerRegistryMock->expects($this->once())
            ->method('retrieve')->with($customerId)->will($this->returnValue($customerMock));
        $customerMock->expects($this->once())->method('getSharedStoreIds')->will($this->returnValue([11]));

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

        $this->quoteRepositoryMock->expects($this->once())->method('get')->with($cartId)
            ->will($this->returnValue($this->quoteMock));
        $this->storeManagerMock->expects($this->once())->method('getStore')->will($this->returnValue($this->storeMock));
        $this->storeMock->expects($this->once())->method('getId')->will($this->returnValue($storeId));
        $customerMock = $this->getMock('\Magento\Customer\Model\Customer', [], [], '', false);
        $this->customerRegistryMock->expects($this->once())
            ->method('retrieve')->with($customerId)->will($this->returnValue($customerMock));
        $customerMock->expects($this->once())->method('getSharedStoreIds')->will($this->returnValue([$storeId]));
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
            ->method('get')->with($cartId)->will($this->returnValue($this->quoteMock));

        $customerMock = $this->getMock('\Magento\Customer\Model\Customer', [], [], '', false);
        $this->customerRegistryMock->expects($this->once())
            ->method('retrieve')->with($customerId)->will($this->returnValue($customerMock));
        $customerMock->expects($this->once())->method('getSharedStoreIds')->will($this->returnValue([$storeId]));
        $this->quoteMock->expects($this->once())->method('getCustomerId')->will($this->returnValue(null));

        $customerQuoteMock = $this->getMock('\Magento\Sales\Model\Quote', [], [], '', false);
        $customerQuoteMock->expects($this->once())->method('loadByCustomer')->with($customerMock)
            ->will($this->returnSelf());
        $customerQuoteMock->expects($this->once())->method('getId')->will($this->returnValue(1));
        $this->quoteFactoryMock->expects($this->once())->method('create')->will($this->returnValue($customerQuoteMock));

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
        $this->quoteRepositoryMock->expects($this->once())->method('get')->with($cartId)
            ->will($this->returnValue($this->quoteMock));
        $customerMock = $this->getMock('\Magento\Customer\Model\Customer', [], [], '', false);
        $this->customerRegistryMock->expects($this->once())
            ->method('retrieve')->with($customerId)->will($this->returnValue($customerMock));

        $customerQuoteMock = $this->getMock('\Magento\Sales\Model\Quote', [], [], '', false);
        $customerQuoteMock->expects($this->once())->method('loadByCustomer')->with($customerMock)
            ->will($this->returnSelf());
        $this->quoteFactoryMock->expects($this->once())->method('create')->will($this->returnValue($customerQuoteMock));

        $customerMock->expects($this->once())->method('getSharedStoreIds')->will($this->returnValue([$storeId]));
        $this->quoteMock->expects($this->once())->method('getCustomerId')->will($this->returnValue(false));
        $this->quoteMock->expects($this->once())
            ->method('setCustomer')->with($customerMock)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('setCustomerIsGuest')->with(0)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())->method('save')->will($this->returnValue($this->quoteMock));

        $this->assertTrue($this->service->assignCustomer($cartId, $customerId));
    }

    public function testOrder()
    {
        $cartId = 123;
        $quoteService = $this->getMock('Magento\Sales\Model\Service\Quote', [], [], '', false);
        $this->quoteRepositoryMock->expects($this->once())->method('get')->with($cartId)
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
