<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Persistent\Test\Unit\Model;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Persistent\Helper\Data as PersistentData;
use Magento\Persistent\Helper\Session as PersistentSession;
use Magento\Persistent\Model\QuoteIdMaskPlugin;
use Magento\Persistent\Model\QuoteManager;
use Magento\Persistent\Model\Session as PersistentSessionModel;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdMask;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class QuoteIdMaskPluginTest extends TestCase
{
    use MockCreationTrait;

    /** @var PersistentData|MockObject */
    private $persistentData;

    /** @var PersistentSession|MockObject */
    private $persistentSession;

    /** @var CustomerSession|MockObject */
    private $customerSession;

    /** @var CartRepositoryInterface|MockObject */
    private $quoteRepository;

    /** @var QuoteManager|MockObject */
    private $quoteManager;

    /** @var QuoteIdMaskPlugin */
    private $plugin;

    protected function setUp(): void
    {
        $this->persistentData = $this->createMock(PersistentData::class);
        $this->persistentSession = $this->createMock(PersistentSession::class);
        $this->customerSession = $this->createMock(CustomerSession::class);
        $this->quoteRepository = $this->createMock(CartRepositoryInterface::class);
        $this->quoteManager = $this->createMock(QuoteManager::class);

        $this->plugin = new QuoteIdMaskPlugin(
            $this->persistentData,
            $this->persistentSession,
            $this->customerSession,
            $this->quoteRepository,
            $this->quoteManager
        );
    }

    public function testAfterLoadConvertsCartOwnedByPersistentCustomer(): void
    {
        $quoteId = 42;
        $customerId = 7;
        $maskedId = 'masked-cart-id';
        $subject = $this->createPartialMockWithReflection(QuoteIdMask::class, ['getQuoteId']);
        $subject->method('getQuoteId')->willReturn($quoteId);

        $this->persistentSession->expects($this->once())->method('isPersistent')->willReturn(true);
        $this->customerSession->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->persistentData->expects($this->once())->method('isShoppingCartPersist')->willReturn(true);
        $this->quoteManager->expects($this->once())->method('isPersistent')->willReturn(true);

        $session = $this->createPartialMockWithReflection(
            PersistentSessionModel::class,
            ['getCustomerId', 'removePersistentCookie']
        );
        $session->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $session->expects($this->once())->method('removePersistentCookie')->willReturnSelf();
        $this->persistentSession->expects($this->once())->method('getSession')->willReturn($session);
        $this->persistentSession->expects($this->once())->method('setSession')->with(null);

        $quote = $this->createPartialMockWithReflection(Quote::class, ['getCustomerId']);
        $quote->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $this->quoteRepository->expects($this->once())->method('get')->with($quoteId)->willReturn($quote);
        $this->quoteManager->expects($this->once())->method('convertCustomerCartToGuest')->with($quote);
        $this->customerSession->expects($this->once())->method('setCustomerId')->with(null);
        $this->customerSession->expects($this->once())->method('setCustomerGroupId')->with(null);

        $this->assertSame($subject, $this->plugin->afterLoad($subject, $subject, $maskedId, 'masked_id'));
    }

    public function testAfterLoadDoesNotConvertCartOwnedByAnotherCustomer(): void
    {
        $quoteId = 42;
        $persistentCustomerId = 7;
        $quoteCustomerId = 8;
        $subject = $this->createPartialMockWithReflection(QuoteIdMask::class, ['getQuoteId']);
        $subject->method('getQuoteId')->willReturn($quoteId);

        $this->persistentSession->expects($this->once())->method('isPersistent')->willReturn(true);
        $this->customerSession->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->persistentData->expects($this->once())->method('isShoppingCartPersist')->willReturn(true);
        $this->quoteManager->expects($this->once())->method('isPersistent')->willReturn(true);

        $session = $this->createPartialMockWithReflection(
            PersistentSessionModel::class,
            ['getCustomerId', 'removePersistentCookie']
        );
        $session->expects($this->once())->method('getCustomerId')->willReturn($persistentCustomerId);
        $session->expects($this->never())->method('removePersistentCookie');
        $this->persistentSession->expects($this->once())->method('getSession')->willReturn($session);
        $this->persistentSession->expects($this->never())->method('setSession');

        $quote = $this->createPartialMockWithReflection(Quote::class, ['getCustomerId']);
        $quote->expects($this->once())->method('getCustomerId')->willReturn($quoteCustomerId);
        $this->quoteRepository->expects($this->once())->method('get')->with($quoteId)->willReturn($quote);
        $this->quoteManager->expects($this->never())->method('convertCustomerCartToGuest');
        $this->customerSession->expects($this->never())->method('setCustomerId');
        $this->customerSession->expects($this->never())->method('setCustomerGroupId');

        $this->plugin->afterLoad($subject, $subject, 'masked-cart-id', 'masked_id');
    }

    public function testAfterLoadIgnoresLookupByQuoteId(): void
    {
        $subject = $this->createPartialMockWithReflection(QuoteIdMask::class, ['getQuoteId']);

        $this->persistentSession->expects($this->never())->method('isPersistent');
        $this->quoteRepository->expects($this->never())->method('get');
        $this->quoteManager->expects($this->never())->method('convertCustomerCartToGuest');

        $this->plugin->afterLoad($subject, $subject, 42, 'quote_id');
    }

    public function testAfterLoadDoesNotConvertWhenPersistentCartFeatureIsDisabled(): void
    {
        $subject = $this->createPartialMockWithReflection(QuoteIdMask::class, ['getQuoteId']);
        $subject->method('getQuoteId')->willReturn(42);

        $this->persistentSession->expects($this->once())->method('isPersistent')->willReturn(true);
        $this->customerSession->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->persistentData->expects($this->once())->method('isShoppingCartPersist')->willReturn(false);
        $this->quoteRepository->expects($this->never())->method('get');
        $this->quoteManager->expects($this->never())->method('convertCustomerCartToGuest');

        $this->plugin->afterLoad($subject, $subject, 'masked-cart-id', 'masked_id');
    }

    public function testAfterLoadDoesNotConvertLoggedInCustomerCart(): void
    {
        $subject = $this->createPartialMockWithReflection(QuoteIdMask::class, ['getQuoteId']);
        $subject->method('getQuoteId')->willReturn(42);

        $this->persistentSession->expects($this->once())->method('isPersistent')->willReturn(true);
        $this->customerSession->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $this->persistentData->expects($this->never())->method('isShoppingCartPersist');
        $this->quoteRepository->expects($this->never())->method('get');
        $this->quoteManager->expects($this->never())->method('convertCustomerCartToGuest');

        $this->plugin->afterLoad($subject, $subject, 'masked-cart-id', 'masked_id');
    }

    public function testAfterLoadDoesNotConvertMissingMask(): void
    {
        $subject = $this->createPartialMockWithReflection(QuoteIdMask::class, ['getQuoteId']);
        $subject->method('getQuoteId')->willReturn(null);

        $this->persistentSession->expects($this->never())->method('isPersistent');
        $this->quoteRepository->expects($this->never())->method('get');
        $this->quoteManager->expects($this->never())->method('convertCustomerCartToGuest');

        $this->plugin->afterLoad($subject, $subject, 'unknown-mask', 'masked_id');
    }
}
