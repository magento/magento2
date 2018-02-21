<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Persistent\Test\Unit\Model\Checkout;

/**
 * Test for Magento\Persistent\Model\Checkout\GuestPaymentInformationManagementPlugin class.
 */
class GuestPaymentInformationManagementPluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Persistent\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    private $persistentHelperMock;

    /**
     * @var \Magento\Persistent\Helper\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    private $persistentSessionMock;

    /**
     * @var \Magento\Checkout\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    private $checkoutSessionMock;

    /**
     * @var \Magento\Persistent\Model\QuoteManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteManagerMock;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerSessionMock;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartRepositoryMock;

    /**
     * @var \Magento\Persistent\Model\Checkout\GuestPaymentInformationManagementPlugin
     */
    private $plugin;

    /**
     * @var \Magento\Checkout\Model\GuestPaymentInformationManagement|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subjectMock;

    /**
     * @var \Magento\Quote\Api\Data\PaymentInterface|\PHPUnit_Framework_MockObject_MockObject $paymentInterfaceMock
     */
    private $paymentInterfaceMock;

    /**
     * Cart id.
     *
     * @var int $cartId
     */
    private $cartId;

    /**
     * Guest email address.
     *
     * @var string $email
     */
    private $email;

    protected function setUp()
    {
        $this->persistentHelperMock = $this->getMock(\Magento\Persistent\Helper\Data::class, [], [], '', false);
        $this->persistentSessionMock = $this->getMock(\Magento\Persistent\Helper\Session::class, [], [], '', false);
        $this->checkoutSessionMock = $this->getMock(\Magento\Checkout\Model\Session::class, [], [], '', false);
        $this->quoteManagerMock = $this->getMock(\Magento\Persistent\Model\QuoteManager::class, [], [], '', false);
        $this->customerSessionMock = $this->getMock(\Magento\Customer\Model\Session::class, [], [], '', false);
        $this->cartRepositoryMock = $this->getMock(
            \Magento\Quote\Api\CartRepositoryInterface::class,
            [],
            [],
            '',
            false
        );
        $this->subjectMock = $this->getMock(
            \Magento\Checkout\Model\GuestPaymentInformationManagement::class,
            [],
            [],
            '',
            false
        );
        $this->paymentInterfaceMock = $this->getMock(
            \Magento\Quote\Api\Data\PaymentInterface::class,
            [],
            [],
            '',
            false
        );
        $this->cartId = 1;
        $this->email = 'guest@example.com';

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
        $walkMethod = 'setEmail';
        $walkArgs = ['email' => $this->email];

        $this->persistentHelperMock->expects($this->once())->method('isShoppingCartPersist')->willReturn(true);
        $this->persistentSessionMock->expects($this->once())->method('isPersistent')->willReturn(true);
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->quoteManagerMock->expects($this->once())->method('isPersistent')->willReturn(true);
        $this->customerSessionMock->expects($this->once())->method('setCustomerId')->with(null);
        $this->customerSessionMock->expects($this->once())->method('setCustomerGroupId')->with(null);
        $this->quoteManagerMock->expects($this->once())->method('convertCustomerCartToGuest');

        /** @var \Magento\Quote\Api\Data\CartInterface|\PHPUnit_Framework_MockObject_MockObject $quoteMock */
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
        $this->checkoutSessionMock->expects($this->once())->method('getQuote')->willReturn($quoteMock);
        $quoteMock->expects($this->once())->method('getId')->willReturn($this->cartId);
        $this->cartRepositoryMock->expects($this->once())->method('get')->with($this->cartId)->willReturn($quoteMock);
        $quoteMock->expects($this->once())->method('setCustomerEmail')->with($this->email);
        /** @var \Magento\Framework\Data\Collection|\PHPUnit_Framework_MockObject_MockObject $collectionMock */
        $collectionMock = $this->getMock(\Magento\Framework\Data\Collection::class, [], [], '', false);
        $quoteMock->expects($this->once())->method('getAddressesCollection')->willReturn($collectionMock);
        $collectionMock->expects($this->once())->method('walk')->with($walkMethod, $walkArgs);
        $this->cartRepositoryMock->expects($this->once())->method('save')->with($quoteMock);

        $this->plugin->beforeSavePaymentInformationAndPlaceOrder(
            $this->subjectMock,
            $this->cartId,
            $this->email,
            $this->paymentInterfaceMock,
            null
        );
    }

    public function testBeforeSavePaymentInformationAndPlaceOrderShoppingCartNotPersistentState()
    {
        $this->persistentHelperMock->expects($this->once())->method('isShoppingCartPersist')->willReturn(false);
        $this->persistentSessionMock->expects($this->once())->method('isPersistent')->willReturn(true);
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->willReturn(false);

        $this->plugin->beforeSavePaymentInformationAndPlaceOrder(
            $this->subjectMock,
            $this->cartId,
            $this->email,
            $this->paymentInterfaceMock,
            null
        );
    }

    public function testBeforeSavePaymentInformationAndPlaceOrderPersistentSessionNotPersistentState()
    {
        $this->persistentSessionMock->expects($this->once())->method('isPersistent')->willReturn(false);

        $this->plugin->beforeSavePaymentInformationAndPlaceOrder(
            $this->subjectMock,
            $this->cartId,
            $this->email,
            $this->paymentInterfaceMock,
            null
        );
    }

    public function testBeforeSavePaymentInformationAndPlaceOrderCustomerSessionInLoggedInState()
    {
        $this->persistentSessionMock->expects($this->once())->method('isPersistent')->willReturn(true);
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->willReturn(true);

        $this->plugin->beforeSavePaymentInformationAndPlaceOrder(
            $this->subjectMock,
            $this->cartId,
            $this->email,
            $this->paymentInterfaceMock,
            null
        );
    }

    public function testBeforeSavePaymentInformationAndPlaceOrderQuoteManagerNotInPersistentState()
    {
        $this->persistentHelperMock->expects($this->once())->method('isShoppingCartPersist')->willReturn(true);
        $this->persistentSessionMock->expects($this->once())->method('isPersistent')->willReturn(true);
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->quoteManagerMock->expects($this->once())->method('isPersistent')->willReturn(false);

        $this->plugin->beforeSavePaymentInformationAndPlaceOrder(
            $this->subjectMock,
            $this->cartId,
            $this->email,
            $this->paymentInterfaceMock,
            null
        );
    }
}
