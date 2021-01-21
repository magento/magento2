<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Persistent\Test\Unit\Model\Checkout;

class GuestPaymentInformationManagementPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Persistent\Helper\Data|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $persistentHelperMock;

    /**
     * @var \Magento\Persistent\Helper\Session|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $persistentSessionMock;

    /**
     * @var \Magento\Checkout\Model\Session|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $checkoutSessionMock;

    /**
     * @var \Magento\Persistent\Model\QuoteManager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $quoteManagerMock;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $customerSessionMock;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $cartRepositoryMock;

    /**
     * @var \Magento\Persistent\Model\Checkout\GuestPaymentInformationManagementPlugin
     */
    protected $plugin;

    /**
     * @var \Magento\Checkout\Model\GuestPaymentInformationManagement|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $subjectMock;

    protected function setUp(): void
    {
        $this->persistentHelperMock = $this->createMock(\Magento\Persistent\Helper\Data::class);
        $this->persistentSessionMock = $this->createMock(\Magento\Persistent\Helper\Session::class);
        $this->checkoutSessionMock = $this->createMock(\Magento\Checkout\Model\Session::class);
        $this->quoteManagerMock = $this->createMock(\Magento\Persistent\Model\QuoteManager::class);
        $this->customerSessionMock = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->cartRepositoryMock = $this->createMock(
            \Magento\Quote\Api\CartRepositoryInterface::class
        );
        $this->subjectMock = $this->createMock(
            \Magento\Checkout\Model\GuestPaymentInformationManagement::class
        );

        $this->plugin = new \Magento\Persistent\Model\Checkout\GuestPaymentInformationManagementPlugin(
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
         * @var \Magento\Quote\Api\Data\PaymentInterface|\PHPUnit\Framework\MockObject\MockObject $paymentInterfaceMock
         */
        $paymentInterfaceMock = $this->createMock(\Magento\Quote\Api\Data\PaymentInterface::class);

        $this->persistentHelperMock->expects($this->once())->method('isShoppingCartPersist')->willReturn(true);
        $this->persistentSessionMock->expects($this->once())->method('isPersistent')->willReturn(true);
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->quoteManagerMock->expects($this->once())->method('isPersistent')->willReturn(true);
        $this->customerSessionMock->expects($this->once())->method('setCustomerId')->with(null);
        $this->customerSessionMock->expects($this->once())->method('setCustomerGroupId')->with(null);
        $this->quoteManagerMock->expects($this->once())->method('convertCustomerCartToGuest');

        /** @var \Magento\Quote\Api\Data\CartInterface|\PHPUnit\Framework\MockObject\MockObject $quoteMock */
        $quoteMock = $this->getMockForAbstractClass(
            \Magento\Quote\Api\Data\CartInterface::class,
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
        /** @var \Magento\Framework\Data\Collection|\PHPUnit\Framework\MockObject\MockObject $collectionMock */
        $collectionMock = $this->createMock(\Magento\Framework\Data\Collection::class);
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
         * @var \Magento\Quote\Api\Data\PaymentInterface|\PHPUnit\Framework\MockObject\MockObject $paymentInterfaceMock
         */
        $paymentInterfaceMock = $this->createMock(\Magento\Quote\Api\Data\PaymentInterface::class);

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
         * @var \Magento\Quote\Api\Data\PaymentInterface|\PHPUnit\Framework\MockObject\MockObject $paymentInterfaceMock
         */
        $paymentInterfaceMock = $this->createMock(\Magento\Quote\Api\Data\PaymentInterface::class);

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
         * @var \Magento\Quote\Api\Data\PaymentInterface|\PHPUnit\Framework\MockObject\MockObject $paymentInterfaceMock
         */
        $paymentInterfaceMock = $this->createMock(\Magento\Quote\Api\Data\PaymentInterface::class);

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
         * @var \Magento\Quote\Api\Data\PaymentInterface|\PHPUnit\Framework\MockObject\MockObject $paymentInterfaceMock
         */
        $paymentInterfaceMock = $this->createMock(\Magento\Quote\Api\Data\PaymentInterface::class);

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
