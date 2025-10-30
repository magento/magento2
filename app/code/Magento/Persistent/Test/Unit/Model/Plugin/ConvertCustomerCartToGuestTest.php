<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Persistent\Test\Unit\Model\Plugin;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Persistent\Helper\Session as PersistentSession;
use Magento\Persistent\Model\Plugin\ConvertCustomerCartToGuest;
use Magento\Persistent\Model\QuoteManager;
use Magento\Persistent\Model\Session;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteManagement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConvertCustomerCartToGuestTest extends TestCase
{
    /**
     * @var ConvertCustomerCartToGuest
     */
    private $plugin;

    /**
     * @var CustomerSession|MockObject
     */
    private $customerSessionMock;

    /**
     * @var PersistentSession|MockObject
     */
    private $persistentSessionMock;

    /**
     * @var QuoteManager|MockObject
     */
    private $quoteManagerMock;

    protected function setUp(): void
    {
        $this->customerSessionMock = $this->createMock(CustomerSession::class);
        $this->persistentSessionMock = $this->createMock(PersistentSession::class);
        $this->quoteManagerMock = $this->createMock(QuoteManager::class);
        $this->plugin = new ConvertCustomerCartToGuest(
            $this->customerSessionMock,
            $this->persistentSessionMock,
            $this->quoteManagerMock
        );
    }

    public function testBeforeSubmit(): void
    {
        $quoteManagementMock = $this->createMock(QuoteManagement::class);
        $quoteMock = $this->getMockBuilder(Quote::class)
            ->addMethods(['getIsPersistent', 'getCustomerId'])
            ->onlyMethods(['getCustomerIsGuest'])
            ->disableOriginalConstructor()
            ->getMock();
        $quoteMock->expects(self::once())->method('getIsPersistent')->willReturn(true);
        $quoteMock->expects(self::once())->method('getCustomerId')->willReturn(1);
        $quoteMock->expects(self::once())->method('getCustomerIsGuest')->willReturn(true);
        $this->customerSessionMock->expects(self::once())->method('setCustomerId')->with(null);
        $session = $this->createMock(Session::class);
        $this->persistentSessionMock->method('getSession')->willReturn($session);
        $session->expects(self::once())->method('removePersistentCookie');
        $this->quoteManagerMock->expects(self::once())->method('convertCustomerCartToGuest')->with($quoteMock);

        $this->plugin->beforeSubmit($quoteManagementMock, $quoteMock);
    }

    public function testBeforeSubmitQuoteIsNotPersistent(): void
    {
        $quoteManagementMock = $this->createMock(QuoteManagement::class);
        $quoteMock = $this->getMockBuilder(Quote::class)
            ->addMethods(['getIsPersistent'])
            ->disableOriginalConstructor()
            ->getMock();
        $quoteMock->expects(self::once())->method('getIsPersistent')->willReturn(false);
        $this->customerSessionMock->expects(self::never())->method('setCustomerId');
        $this->persistentSessionMock->expects(self::never())->method('getSession');
        $this->quoteManagerMock->expects(self::never())->method('convertCustomerCartToGuest');

        $this->plugin->beforeSubmit($quoteManagementMock, $quoteMock);
    }

    public function testBeforeSubmitQuoteWithoutCustomerId(): void
    {
        $quoteManagementMock = $this->createMock(QuoteManagement::class);
        $quoteMock = $this->getMockBuilder(Quote::class)
            ->addMethods(['getIsPersistent', 'getCustomerId'])
            ->disableOriginalConstructor()
            ->getMock();
        $quoteMock->expects(self::once())->method('getIsPersistent')->willReturn(true);
        $quoteMock->expects(self::once())->method('getCustomerId')->willReturn(null);
        $this->customerSessionMock->expects(self::never())->method('setCustomerId');
        $this->persistentSessionMock->expects(self::never())->method('getSession');
        $this->quoteManagerMock->expects(self::never())->method('convertCustomerCartToGuest');

        $this->plugin->beforeSubmit($quoteManagementMock, $quoteMock);
    }

    public function testBeforeSubmitQuoteIsGuest(): void
    {
        $quoteManagementMock = $this->createMock(QuoteManagement::class);
        $quoteMock = $this->getMockBuilder(Quote::class)
            ->addMethods(['getIsPersistent', 'getCustomerId'])
            ->onlyMethods(['getCustomerIsGuest'])
            ->disableOriginalConstructor()
            ->getMock();
        $quoteMock->expects(self::once())->method('getIsPersistent')->willReturn(true);
        $quoteMock->expects(self::once())->method('getCustomerId')->willReturn(1);
        $quoteMock->expects(self::once())->method('getCustomerIsGuest')->willReturn(false);
        $this->customerSessionMock->expects(self::never())->method('setCustomerId');
        $this->persistentSessionMock->expects(self::never())->method('getSession');
        $this->quoteManagerMock->expects(self::never())->method('convertCustomerCartToGuest');

        $this->plugin->beforeSubmit($quoteManagementMock, $quoteMock);
    }
}
