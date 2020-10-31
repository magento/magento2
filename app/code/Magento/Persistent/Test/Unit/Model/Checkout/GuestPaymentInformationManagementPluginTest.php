<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Persistent\Test\Unit\Model\Checkout;

use Magento\Checkout\Model\GuestPaymentInformationManagement;
use Magento\Framework\Data\Collection;
use Magento\Persistent\Helper\Data;
use Magento\Persistent\Helper\Session;
use Magento\Persistent\Model\Checkout\GuestPaymentInformationManagementPlugin;
use Magento\Persistent\Model\QuoteManager;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GuestPaymentInformationManagementPluginTest extends TestCase
{
    /**
     * @var Data|MockObject
     */
    protected $persistentHelperMock;

    /**
     * @var Session|MockObject
     */
    protected $persistentSessionMock;

    /**
     * @var \Magento\Checkout\Model\Session|MockObject
     */
    protected $checkoutSessionMock;

    /**
     * @var QuoteManager|MockObject
     */
    protected $quoteManagerMock;

    /**
     * @var \Magento\Customer\Model\Session|MockObject
     */
    protected $customerSessionMock;

    /**
     * @var CartRepositoryInterface|MockObject
     */
    protected $cartRepositoryMock;

    /**
     * @var GuestPaymentInformationManagementPlugin
     */
    protected $plugin;

    /**
     * @var GuestPaymentInformationManagement|MockObject
     */
    protected $subjectMock;

    protected function setUp(): void
    {
        $this->persistentHelperMock = $this->createMock(Data::class);
        $this->persistentSessionMock = $this->createMock(Session::class);
        $this->checkoutSessionMock = $this->createMock(\Magento\Checkout\Model\Session::class);
        $this->quoteManagerMock = $this->createMock(QuoteManager::class);
        $this->customerSessionMock = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->cartRepositoryMock = $this->createMock(
            CartRepositoryInterface::class
        );
        $this->subjectMock = $this->createMock(
            GuestPaymentInformationManagement::class
        );

        $this->plugin = new GuestPaymentInformationManagementPlugin(
            $this->persistentHelperMock,
            $this->persistentSessionMock,
            $this->customerSessionMock,
            $this->checkoutSessionMock,
            $this->quoteManagerMock,
            $this->cartRepositoryMock
        );
    }

    public function testBeforeSavePaymentInformationAndPlaceOrderCartConvertsToGuest()
    {
        $cartId = '1';
        $email = 'guest@example.com';
        $walkMethod = 'setEmail';
        $walkArgs = ['email' => $email];
        /**
         * @var PaymentInterface|MockObject $paymentInterfaceMock
         */
        $paymentInterfaceMock = $this->getMockForAbstractClass(PaymentInterface::class);

        $this->persistentHelperMock->expects($this->once())->method('isShoppingCartPersist')->willReturn(true);
        $this->persistentSessionMock->expects($this->once())->method('isPersistent')->willReturn(true);
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->quoteManagerMock->expects($this->once())->method('isPersistent')->willReturn(true);
        $this->customerSessionMock->expects($this->once())->method('setCustomerId')->with(null);
        $this->customerSessionMock->expects($this->once())->method('setCustomerGroupId')->with(null);
        $this->quoteManagerMock->expects($this->once())->method('convertCustomerCartToGuest');

        /** @var CartInterface|MockObject $quoteMock */
        $quoteMock = $this->getMockForAbstractClass(
            CartInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['setCustomerEmail', 'getAddressesCollection'],
            false
        );
        $this->checkoutSessionMock->method('getQuoteId')->willReturn($cartId);
        $this->cartRepositoryMock->expects($this->once())->method('get')->with($cartId)->willReturn($quoteMock);
        $quoteMock->expects($this->once())->method('setCustomerEmail')->with($email);
        /** @var Collection|MockObject $collectionMock */
        $collectionMock = $this->createMock(Collection::class);
        $quoteMock->expects($this->once())->method('getAddressesCollection')->willReturn($collectionMock);
        $collectionMock->expects($this->once())->method('walk')->with($walkMethod, $walkArgs);
        $this->cartRepositoryMock->expects($this->once())->method('save')->with($quoteMock);

        $this->plugin->beforeSavePaymentInformation(
            $this->subjectMock,
            $cartId,
            $email,
            $paymentInterfaceMock,
            null
        );
    }

    public function testBeforeSavePaymentInformationAndPlaceOrderShoppingCartNotPersistentState()
    {
        $cartId = '1';
        $email = 'guest@example.com';

        /**
         * @var PaymentInterface|MockObject $paymentInterfaceMock
         */
        $paymentInterfaceMock = $this->getMockForAbstractClass(PaymentInterface::class);

        $this->persistentHelperMock->expects($this->once())->method('isShoppingCartPersist')->willReturn(false);
        $this->persistentSessionMock->expects($this->once())->method('isPersistent')->willReturn(true);
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->willReturn(false);

        $this->plugin->beforeSavePaymentInformation(
            $this->subjectMock,
            $cartId,
            $email,
            $paymentInterfaceMock,
            null
        );
    }

    public function testBeforeSavePaymentInformationAndPlaceOrderPersistentSessionNotPersistentState()
    {
        $cartId = '1';
        $email = 'guest@example.com';

        /**
         * @var PaymentInterface|MockObject $paymentInterfaceMock
         */
        $paymentInterfaceMock = $this->getMockForAbstractClass(PaymentInterface::class);

        $this->persistentSessionMock->expects($this->once())->method('isPersistent')->willReturn(false);

        $this->plugin->beforeSavePaymentInformation(
            $this->subjectMock,
            $cartId,
            $email,
            $paymentInterfaceMock,
            null
        );
    }

    public function testBeforeSavePaymentInformationAndPlaceOrderCustomerSessionInLoggedInState()
    {
        $cartId = '1';
        $email = 'guest@example.com';

        /**
         * @var PaymentInterface|MockObject $paymentInterfaceMock
         */
        $paymentInterfaceMock = $this->getMockForAbstractClass(PaymentInterface::class);

        $this->persistentSessionMock->expects($this->once())->method('isPersistent')->willReturn(true);
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->willReturn(true);

        $this->plugin->beforeSavePaymentInformation(
            $this->subjectMock,
            $cartId,
            $email,
            $paymentInterfaceMock,
            null
        );
    }

    public function testBeforeSavePaymentInformationAndPlaceOrderQuoteManagerNotInPersistentState()
    {
        $cartId = '1';
        $email = 'guest@example.com';

        /**
         * @var PaymentInterface|MockObject $paymentInterfaceMock
         */
        $paymentInterfaceMock = $this->getMockForAbstractClass(PaymentInterface::class);

        $this->persistentHelperMock->expects($this->once())->method('isShoppingCartPersist')->willReturn(true);
        $this->persistentSessionMock->expects($this->once())->method('isPersistent')->willReturn(true);
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->quoteManagerMock->expects($this->once())->method('isPersistent')->willReturn(false);

        $this->plugin->beforeSavePaymentInformation(
            $this->subjectMock,
            $cartId,
            $email,
            $paymentInterfaceMock,
            null
        );
    }
}
