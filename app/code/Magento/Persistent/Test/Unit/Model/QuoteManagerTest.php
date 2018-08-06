<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Persistent\Test\Unit\Model;

use Magento\Persistent\Model\QuoteManager;

class QuoteManagerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var QuoteManager
     */
    protected $model;

    /**
     * @var \Magento\Persistent\Helper\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $persistentSessionMock;

    /**
     * @var \Magento\Persistent\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $persistentDataMock;

    /**
     * @var \Magento\Checkout\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutSessionMock;

    /**
     * @var \Magento\Quote\Model\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $abstractCollectionMock;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteRepositoryMock;

    protected function setUp()
    {
        $this->persistentSessionMock = $this->createMock(\Magento\Persistent\Helper\Session::class);
        $this->sessionMock =
            $this->createPartialMock(\Magento\Persistent\Model\Session::class, [
                    'setLoadInactive',
                    'setCustomerData',
                    'clearQuote',
                    'clearStorage',
                    'getQuote',
                    'removePersistentCookie',
                    '__wakeup',
                ]);
        $this->persistentDataMock = $this->createMock(\Magento\Persistent\Helper\Data::class);
        $this->checkoutSessionMock = $this->createMock(\Magento\Checkout\Model\Session::class);

        $this->abstractCollectionMock =
            $this->createMock(\Magento\Eav\Model\Entity\Collection\AbstractCollection::class);

        $this->quoteRepositoryMock = $this->createMock(\Magento\Quote\Api\CartRepositoryInterface::class);
        $this->quoteMock = $this->createPartialMock(\Magento\Quote\Model\Quote::class, [
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
                '__wakeup'
            ]);

        $this->model = new QuoteManager(
            $this->persistentSessionMock,
            $this->persistentDataMock,
            $this->checkoutSessionMock,
            $this->quoteRepositoryMock
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
            ->with(\Magento\Customer\Model\GroupManagement::NOT_LOGGED_IN_ID)
            ->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('setIsPersistent')->with(false)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('removeAllAddresses')->will($this->returnValue($this->quoteMock));
        $quoteAddressMock = $this->createMock(\Magento\Quote\Model\Quote\Address::class);
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
            ->with(\Magento\Customer\Model\GroupManagement::NOT_LOGGED_IN_ID)
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
            ->method('getQuote')->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->exactly(2))->method('getId')->willReturn($quoteId);
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
        $this->quoteMock->expects($this->once())->method('setCustomerGroupId')
            ->with(\Magento\Customer\Model\GroupManagement::NOT_LOGGED_IN_ID)
            ->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())
            ->method('setIsPersistent')->with(false)->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->exactly(3))
            ->method('getAddressesCollection')->willReturn($this->abstractCollectionMock);
        $this->abstractCollectionMock->expects($this->exactly(3))->method('walk')->with($this->logicalOr(
            $this->equalTo('setCustomerAddressId'),
            $this->equalTo($addressArgs),
            $this->equalTo('setCustomerId'),
            $this->equalTo($customerIdArgs),
            $this->equalTo('setEmail'),
            $this->equalTo($emailArgs)
        ));
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
            ->method('getQuote')->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())->method('getId')->willReturn(null);
        $this->quoteRepositoryMock->expects($this->once())->method('get')->with(null)->willReturn(null);

        $this->model->convertCustomerCartToGuest();
    }

    public function testConvertCustomerCartToGuestWithEmptyQuoteId()
    {
        $this->checkoutSessionMock->expects($this->once())
            ->method('getQuote')->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())->method('getId')->willReturn(1);
        $quoteWithNoId = $this->quoteMock = $this->createMock(\Magento\Quote\Model\Quote::class);
        $quoteWithNoId->expects($this->once())->method('getId')->willReturn(null);
        $this->quoteRepositoryMock->expects($this->once())->method('get')->with(1)->willReturn($quoteWithNoId);
        $this->model->convertCustomerCartToGuest();
    }
}
