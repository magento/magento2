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

    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->quoteFactoryMock = $this->getMock(
            '\Magento\Sales\Model\QuoteFactory', ['create', '__wakeup'], [], '', false
        );
        $this->storeManagerMock = $this->getMock('\Magento\Store\Model\StoreManagerInterface', [], [], '', false);

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
        $this->service = $this->objectManager->getObject(
            '\Magento\Checkout\Service\V1\Cart\WriteService',
            [
                'quoteFactory' => $this->quoteFactoryMock,
                'storeManager' => $this->storeManagerMock,
                'customerRegistry' => $this->customerRegistryMock
            ]
        );
    }

    public function testCreate()
    {
        $storeId = 345;

        $this->storeManagerMock->expects($this->once())->method('getStore')->will($this->returnValue($this->storeMock));
        $this->storeMock->expects($this->once())->method('getId')->will($this->returnValue($storeId));

        $this->quoteFactoryMock->expects($this->once())->method('create')->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())->method('setStoreId')->with($storeId);
        $this->quoteMock->expects($this->once())->method('save');

        $this->service->create();
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
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage There is no cart with provided ID.
     */
    public function testAssignCustomerNoSuchEntityExceptionByCartId()
    {
        $cartId = 956;
        $customerId = 125;
        $storeId = 12;

        $this->storeManagerMock->expects($this->once())->method('getStore')->will($this->returnValue($this->storeMock));
        $this->storeMock->expects($this->once())->method('getId')->will($this->returnValue($storeId));
        $this->quoteFactoryMock->expects($this->once())->method('create')->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('load')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())->method('getId')->will($this->returnValue(33));
        $customerRegistryMock =
            $this->getMock('\Magento\Customer\Model\CustomerRegistry', [], [], '', false);
        $customerRegistryMock->expects($this->never())->method('retrieve');

        $this->service->assignCustomer($cartId, $customerId);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage There is no cart with provided ID.
     */
    public function testAssignCustomerNoSuchEntityExceptionByStoreId()
    {
        $cartId = 956;
        $customerId = 125;
        $storeId = 12;

        $this->storeManagerMock->expects($this->once())->method('getStore')->will($this->returnValue($this->storeMock));
        $this->storeMock->expects($this->once())->method('getId')->will($this->returnValue($storeId));
        $this->quoteFactoryMock->expects($this->once())->method('create')->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('load')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())->method('getId')->will($this->returnValue($cartId));
        $this->quoteMock->expects($this->once())->method('getStoreId')->will($this->returnValue(99));

        $this->customerRegistryMock->expects($this->never())->method('retrieve');

        $this->service->assignCustomer($cartId, $customerId);
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
        $this->quoteFactoryMock->expects($this->once())->method('create')->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('load')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())->method('getId')->will($this->returnValue($cartId));
        $this->quoteMock->expects($this->once())->method('getStoreId')->will($this->returnValue($storeId));
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

        $this->storeManagerMock->expects($this->once())->method('getStore')->will($this->returnValue($this->storeMock));
        $this->storeMock->expects($this->once())->method('getId')->will($this->returnValue($storeId));
        $this->quoteFactoryMock->expects($this->once())->method('create')->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('load')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())->method('getId')->will($this->returnValue($cartId));
        $this->quoteMock->expects($this->once())->method('getStoreId')->will($this->returnValue($storeId));
        $customerMock = $this->getMock('\Magento\Customer\Model\Customer', [], [], '', false);
        $this->customerRegistryMock->expects($this->once())
            ->method('retrieve')->with($customerId)->will($this->returnValue($customerMock));
        $customerMock->expects($this->once())->method('getSharedStoreIds')->will($this->returnValue([$storeId]));
        $this->quoteMock->expects($this->once())->method('getCustomerId')->will($this->returnValue($customerId));
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
        $this->quoteFactoryMock->expects($this->at(0))->method('create')->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('load')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())->method('getId')->will($this->returnValue($cartId));
        $this->quoteMock->expects($this->once())->method('getStoreId')->will($this->returnValue($storeId));
        $customerMock = $this->getMock('\Magento\Customer\Model\Customer', [], [], '', false);
        $this->customerRegistryMock->expects($this->once())
            ->method('retrieve')->with($customerId)->will($this->returnValue($customerMock));

        $customerQuoteMock = $this->getMock('\Magento\Sales\Model\Quote', [], [], '', false);
        $customerQuoteMock->expects($this->once())->method('loadByCustomer')->with($customerMock)
            ->will($this->returnSelf());
        $this->quoteFactoryMock->expects($this->at(1))->method('create')->will($this->returnValue($customerQuoteMock));

        $customerMock->expects($this->once())->method('getSharedStoreIds')->will($this->returnValue([$storeId]));
        $this->quoteMock->expects($this->once())->method('getCustomerId')->will($this->returnValue(false));
        $this->quoteMock->expects($this->once())
            ->method('setCustomer')->with($customerMock)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('setCustomerIsGuest')->with(0)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())->method('save')->will($this->returnValue($this->quoteMock));

        $this->assertTrue($this->service->assignCustomer($cartId, $customerId));
    }

}