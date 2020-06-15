<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Persistent\Test\Unit\Model;

use Magento\Checkout\Model\Session;
use Magento\Customer\Model\GroupManagement;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Persistent\Helper\Data;
use Magento\Persistent\Model\QuoteManager;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartExtensionFactory;
use Magento\Quote\Api\Data\CartExtensionInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\ShippingAssignment\ShippingAssignmentProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuoteManagerTest extends TestCase
{
    /**
     * @var QuoteManager
     */
    protected $model;

    /**
     * @var \Magento\Persistent\Helper\Session|MockObject
     */
    protected $persistentSessionMock;

    /**
     * @var Data|MockObject
     */
    protected $persistentDataMock;

    /**
     * @var Session|MockObject
     */
    protected $checkoutSessionMock;

    /**
     * @var Quote|MockObject
     */
    protected $quoteMock;

    /**
     * @var MockObject
     */
    protected $sessionMock;

    /**
     * @var MockObject
     */
    protected $abstractCollectionMock;

    /**
     * @var CartRepositoryInterface|MockObject
     */
    protected $quoteRepositoryMock;
    /**
     * @var CartExtensionFactory|MockObject
     */
    private $cartExtensionFactory;
    /**
     * @var ShippingAssignmentProcessor|MockObject
     */
    private $shippingAssignmentProcessor;

    protected function setUp()
    {
        $this->persistentSessionMock = $this->createMock(\Magento\Persistent\Helper\Session::class);
        $this->sessionMock =
            $this->createPartialMock(
                \Magento\Persistent\Model\Session::class,
                [
                    'setLoadInactive',
                    'setCustomerData',
                    'clearQuote',
                    'clearStorage',
                    'getQuote',
                    'removePersistentCookie',
                    '__wakeup',
                ]
            );
        $this->persistentDataMock = $this->createMock(Data::class);
        $this->checkoutSessionMock = $this->createMock(Session::class);

        $this->abstractCollectionMock =
            $this->createMock(AbstractCollection::class);

        $this->quoteRepositoryMock = $this->createMock(CartRepositoryInterface::class);
        $this->quoteMock = $this->createPartialMock(
            Quote::class,
            [
                'getId',
                'getIsPersistent',
                'getPaymentsCollection',
                'getAddressesCollection',
                'setIsActive',
                'setCustomerId',
                'setCustomerEmail',
                'setCustomerFirstname',
                'setCustomerLastname',
                'setCustomerGroupId',
                'setIsPersistent',
                'getShippingAddress',
                'getBillingAddress',
                'collectTotals',
                'removeAllAddresses',
                'getIsActive',
                'getCustomerId',
                'isVirtual',
                'getItemsQty',
                'getExtensionAttributes',
                'setExtensionAttributes',
                '__wakeup'
            ]
        );

        $this->cartExtensionFactory = $this->createPartialMock(CartExtensionFactory::class, ['create']);
        $this->shippingAssignmentProcessor = $this->createPartialMock(ShippingAssignmentProcessor::class, ['create']);

        $this->model = new QuoteManager(
            $this->persistentSessionMock,
            $this->persistentDataMock,
            $this->checkoutSessionMock,
            $this->quoteRepositoryMock,
            $this->cartExtensionFactory,
            $this->shippingAssignmentProcessor
        );
    }

    public function testSetGuestWithEmptyQuote()
    {
        $this->checkoutSessionMock->expects($this->once())
            ->method('getQuote')->will($this->returnValue(null));
        $this->quoteMock->expects($this->never())->method('getId');

        $this->persistentSessionMock->expects($this->once())
            ->method('getSession')->will($this->returnValue($this->sessionMock));
        $this->sessionMock->expects($this->once())
            ->method('removePersistentCookie')->will($this->returnValue($this->sessionMock));

        $this->model->setGuest(false);
    }

    public function testSetGuestWithEmptyQuoteId()
    {
        $this->checkoutSessionMock->expects($this->once())
            ->method('getQuote')->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())->method('getId')->will($this->returnValue(null));
        $this->persistentDataMock->expects($this->never())->method('isShoppingCartPersist');

        $this->persistentSessionMock->expects($this->once())
            ->method('getSession')->will($this->returnValue($this->sessionMock));
        $this->sessionMock->expects($this->once())
            ->method('removePersistentCookie')->will($this->returnValue($this->sessionMock));

        $this->model->setGuest(false);
    }

    public function testSetGuestWhenShoppingCartAndQuoteAreNotPersistent()
    {
        $this->checkoutSessionMock->expects($this->once())
            ->method('getQuote')->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())->method('getId')->will($this->returnValue(11));
        $this->persistentDataMock->expects($this->once())
            ->method('isShoppingCartPersist')->will($this->returnValue(false));
        $this->quoteMock->expects($this->once())->method('getIsPersistent')->will($this->returnValue(false));
        $this->checkoutSessionMock->expects($this->once())
            ->method('clearQuote')->will($this->returnValue($this->checkoutSessionMock));
        $this->checkoutSessionMock->expects($this->once())->method('clearStorage');
        $this->quoteMock->expects($this->never())->method('getPaymentsCollection');

        $this->model->setGuest(true);
    }

    public function testSetGuest()
    {
        $this->checkoutSessionMock->expects($this->once())
            ->method('getQuote')->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())->method('getId')->will($this->returnValue(11));
        $this->persistentDataMock->expects($this->never())->method('isShoppingCartPersist');
        $this->quoteMock->expects($this->once())
            ->method('getPaymentsCollection')->will($this->returnValue($this->abstractCollectionMock));
        $this->quoteMock->expects($this->once())
            ->method('getAddressesCollection')->will($this->returnValue($this->abstractCollectionMock));
        $this->abstractCollectionMock->expects($this->exactly(2))->method('walk')->with('delete');
        $this->quoteMock->expects($this->once())
            ->method('setIsActive')->with(true)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('setCustomerId')->with(null)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('setCustomerEmail')->with(null)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('setCustomerFirstname')->with(null)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('setCustomerLastname')->with(null)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())->method('setCustomerGroupId')
            ->with(GroupManagement::NOT_LOGGED_IN_ID)
            ->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('setIsPersistent')->with(false)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('removeAllAddresses')->will($this->returnValue($this->quoteMock));
        $quoteAddressMock = $this->createMock(Address::class);
        $this->quoteMock->expects($this->once())
            ->method('getShippingAddress')->will($this->returnValue($quoteAddressMock));
        $this->quoteMock->expects($this->once())
            ->method('getBillingAddress')->will($this->returnValue($quoteAddressMock));
        $this->quoteMock->expects($this->once())->method('collectTotals')->will($this->returnValue($this->quoteMock));
        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($this->quoteMock);
        $this->persistentSessionMock->expects($this->once())
            ->method('getSession')->will($this->returnValue($this->sessionMock));
        $this->sessionMock->expects($this->once())
            ->method('removePersistentCookie')->will($this->returnValue($this->sessionMock));
        $this->quoteMock->expects($this->once())->method('isVirtual')->willReturn(false);
        $this->quoteMock->expects($this->once())->method('getItemsQty')->willReturn(1);
        $extensionAttributes = $this->createPartialMock(
            CartExtensionInterface::class,
            [
                'setShippingAssignments',
                'getShippingAssignments'
            ]
        );
        $shippingAssignment = $this->createMock(ShippingAssignmentInterface::class);
        $extensionAttributes->expects($this->once())
            ->method('setShippingAssignments')
            ->with([$shippingAssignment]);
        $this->shippingAssignmentProcessor->expects($this->once())
            ->method('create')
            ->with($this->quoteMock)
            ->willReturn($shippingAssignment);
        $this->cartExtensionFactory->expects($this->once())
            ->method('create')
            ->willReturn($extensionAttributes);
        $this->quoteMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn(null);
        $this->quoteMock->expects($this->once())
            ->method('setExtensionAttributes')
            ->with($extensionAttributes);
        $this->model->setGuest(false);
    }

    public function testExpireWithActiveQuoteAndCustomerId()
    {
        $this->checkoutSessionMock->expects($this->once())
            ->method('setLoadInactive')->will($this->returnValue($this->sessionMock));

        $this->sessionMock->expects($this->once())->method('getQuote')->will($this->returnValue($this->quoteMock));

        $this->quoteMock->expects($this->once())->method('getIsActive')->will($this->returnValue(11));
        $this->quoteMock->expects($this->once())->method('getCustomerId')->will($this->returnValue(22));

        $this->checkoutSessionMock->expects($this->once())
            ->method('setCustomerData')->with(null)->will($this->returnValue($this->sessionMock));

        $this->sessionMock->expects($this->once())
            ->method('clearQuote')->will($this->returnValue($this->sessionMock));
        $this->sessionMock->expects($this->once())
            ->method('clearStorage')->will($this->returnValue($this->sessionMock));
        $this->quoteMock->expects($this->never())->method('setIsActive');

        $this->model->expire();
    }

    public function testExpire()
    {
        $this->checkoutSessionMock->expects($this->once())
            ->method('setLoadInactive')->will($this->returnValue($this->sessionMock));
        $this->sessionMock->expects($this->once())->method('getQuote')->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())->method('getIsActive')->will($this->returnValue(0));
        $this->checkoutSessionMock->expects($this->never())->method('setCustomerData');
        $this->quoteMock->expects($this->once())
            ->method('setIsActive')
            ->with(true)
            ->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('setIsPersistent')
            ->with(false)
            ->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('setCustomerId')
            ->with(null)
            ->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('setCustomerGroupId')
            ->with(GroupManagement::NOT_LOGGED_IN_ID)
            ->will($this->returnValue($this->quoteMock));

        $this->model->expire();
    }

    public function testConvertCustomerCartToGuest()
    {
        $quoteId = 1;
        $addressArgs = ['customerAddressId' => null];
        $customerIdArgs = ['customerId' => null];
        $emailArgs = ['email' => null];

        $this->checkoutSessionMock->expects($this->once())
            ->method('getQuoteId')->willReturn($quoteId);
        $this->quoteMock->expects($this->once())->method('getId')->willReturn($quoteId);
        $this->quoteRepositoryMock->expects($this->once())->method('get')->with($quoteId)->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())
            ->method('setIsActive')->with(true)->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())
            ->method('setCustomerId')->with(null)->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())
            ->method('setCustomerEmail')->with(null)->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())
            ->method('setCustomerFirstname')->with(null)->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())
            ->method('setCustomerLastname')->with(null)->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->never())->method('setCustomerGroupId')
            ->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())
            ->method('setIsPersistent')->with(false)->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->exactly(3))
            ->method('getAddressesCollection')->willReturn($this->abstractCollectionMock);
        $this->abstractCollectionMock->expects($this->exactly(3))->method('walk')->with(
            $this->logicalOr(
                $this->equalTo('setCustomerAddressId'),
                $this->equalTo($addressArgs),
                $this->equalTo('setCustomerId'),
                $this->equalTo($customerIdArgs),
                $this->equalTo('setEmail'),
                $this->equalTo($emailArgs)
            )
        );
        $this->quoteMock->expects($this->once())->method('collectTotals')->willReturn($this->quoteMock);
        $this->persistentSessionMock->expects($this->once())
            ->method('getSession')->willReturn($this->sessionMock);
        $this->sessionMock->expects($this->once())
            ->method('removePersistentCookie')->willReturn($this->sessionMock);
        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($this->quoteMock);

        $this->model->convertCustomerCartToGuest();
    }

    public function testConvertCustomerCartToGuestWithEmptyQuote()
    {
        $this->checkoutSessionMock->expects($this->once())
            ->method('getQuoteId')->willReturn(null);
        $this->quoteRepositoryMock->expects($this->once())->method('get')->with(null)->willReturn(null);
        $this->model->convertCustomerCartToGuest();
    }

    public function testConvertCustomerCartToGuestWithEmptyQuoteId()
    {
        $this->checkoutSessionMock->expects($this->once())
            ->method('getQuoteId')->willReturn(1);
        $quoteWithNoId = $this->quoteMock = $this->createMock(Quote::class);
        $quoteWithNoId->expects($this->once())->method('getId')->willReturn(null);
        $this->quoteRepositoryMock->expects($this->once())->method('get')->with(1)->willReturn($quoteWithNoId);
        $this->quoteMock->expects($this->once())->method('getId')->willReturn(1);
        $this->model->convertCustomerCartToGuest();
    }
}
