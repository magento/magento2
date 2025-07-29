<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerQuote\Test\Unit\Plugin\LoginAsCustomerApi;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\LoginAsCustomerApi\Api\AuthenticateCustomerBySecretInterface;
use Magento\LoginAsCustomerQuote\Plugin\LoginAsCustomerApi\ProcessShoppingCartPlugin;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for ProcessShoppingCartPlugin
 */
class ProcessShoppingCartPluginTest extends TestCase
{
    /**
     * @var CustomerSession|MockObject
     */
    private $customerSession;

    /**
     * @var CheckoutSession|MockObject
     */
    private $checkoutSession;

    /**
     * @var CartRepositoryInterface|MockObject
     */
    private $quoteRepository;

    /**
     * @var Quote|MockObject
     */
    private $quote;

    /**
     * @var ProcessShoppingCartPlugin
     */
    private $plugin;

    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        $this->customerSession = $this->getMockBuilder(CustomerSession::class)->disableOriginalConstructor()->getMock();
        $this->checkoutSession = $this->getMockBuilder(CheckoutSession::class)->disableOriginalConstructor()->getMock();
        $this->quoteRepository = $this->getMockBuilder(CartRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->quote = $this->getMockBuilder(Quote::class)->disableOriginalConstructor()->getMock();
        $this->plugin = new ProcessShoppingCartPlugin(
            $this->customerSession,
            $this->checkoutSession,
            $this->quoteRepository
        );
    }

    /**
     * Test beforeExecute when if condition is true
     */
    public function testBeforeExecuteWithNoCustomerIdAndQuoteId(): void
    {
        $this->customerSession->expects($this->once())->method('getId')->willReturn(null);
        $this->checkoutSession->expects($this->exactly(2))->method('getQuote')->willReturn($this->quote);
        $this->quote->expects($this->once())->method('getId')->willReturn(123);
        $this->quote->expects($this->once())->method('removeAllItems')->willReturnSelf();
        $this->quote->expects($this->once())->method('setCustomerIsGuest')->with(0)->willReturnSelf();
        $this->quoteRepository->expects($this->once())->method('save')->with($this->quote);
        $subject = $this->createMock(AuthenticateCustomerBySecretInterface::class);
        $secret = 'test-secret';
        $result = $this->plugin->beforeExecute($subject, $secret);
        $this->assertNull($result);
    }

    /**
     * Test beforeExecute when if condition is false
     *
     * @throws LocalizedException
     */
    public function testBeforeExecuteWithCustomerId(): void
    {
        $this->customerSession->expects($this->once())->method('getId')->willReturn(456);
        $this->checkoutSession->expects($this->never())->method('getQuote');
        $this->quote->expects($this->never())->method('getId');
        $this->noGuestCart();
    }

    /**
     * Test beforeExecute when if condition is false (no quote ID)
     *
     * @throws LocalizedException
     */
    public function testBeforeExecuteWithNoQuoteId(): void
    {
        $this->customerSession->expects($this->once())->method('getId')->willReturn(null);
        $this->checkoutSession->expects($this->once())->method('getQuote')->willReturn($this->quote);
        $this->quote->expects($this->once())->method('getId')->willReturn(null);
        $this->noGuestCart();
    }

    /**
     * Expected that no guest cart exist
     *
     * @return void
     * @throws LocalizedException
     */
    private function noGuestCart(): void
    {
        $this->quote->expects($this->never())->method('removeAllItems');
        $this->quote->expects($this->never())->method('setCustomerIsGuest');
        $this->quoteRepository->expects($this->never())->method('save');
        $subject = $this->getMockBuilder(AuthenticateCustomerBySecretInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $secret = 'test-secret';
        $result = $this->plugin->beforeExecute($subject, $secret);
        $this->assertNull($result);
    }
}
