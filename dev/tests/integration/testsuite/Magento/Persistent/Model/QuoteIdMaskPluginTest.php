<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Persistent\Model;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\ObjectManagerInterface;
use Magento\Persistent\Helper\Session as PersistentSessionHelper;
use Magento\Persistent\Model\SessionFactory as PersistentSessionFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Quote\Model\GetQuoteByReservedOrderId;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for persistent cart conversion at the masked cart ID boundary.
 *
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuoteIdMaskPluginTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var CustomerSession */
    private $customerSession;

    /** @var CheckoutSession */
    private $checkoutSession;

    /** @var PersistentSessionHelper */
    private $persistentSessionHelper;

    /** @var PersistentSessionFactory */
    private $persistentSessionFactory;

    /** @var QuoteIdMaskFactory */
    private $quoteIdMaskFactory;

    /** @var CartRepositoryInterface */
    private $quoteRepository;

    /** @var GetQuoteByReservedOrderId */
    private $getQuoteByReservedOrderId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->customerSession = $this->objectManager->get(CustomerSession::class);
        $this->checkoutSession = $this->objectManager->get(CheckoutSession::class);
        $this->persistentSessionHelper = $this->objectManager->get(PersistentSessionHelper::class);
        $this->persistentSessionFactory = $this->objectManager->get(PersistentSessionFactory::class);
        $this->quoteIdMaskFactory = $this->objectManager->get(QuoteIdMaskFactory::class);
        $this->quoteRepository = $this->objectManager->get(CartRepositoryInterface::class);
        $this->getQuoteByReservedOrderId = $this->objectManager->get(GetQuoteByReservedOrderId::class);
    }

    protected function tearDown(): void
    {
        $this->customerSession->setCustomerId(null);
        $this->customerSession->setCustomerGroupId(null);
        $this->checkoutSession->clearQuote();
        $this->checkoutSession->setCustomerData(null);
        $this->persistentSessionHelper->setSession(null);

        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Persistent/_files/persistent_with_customer_quote_and_cookie.php
     * @magentoConfigFixture current_store persistent/options/enabled 1
     * @magentoConfigFixture current_store persistent/options/shopping_cart 1
     */
    public function testMaskedIdLoadConvertsPersistentCustomersOwnQuoteToGuest(): void
    {
        $session = $this->persistentSessionFactory->create()->loadByCustomerId(1);
        $this->persistentSessionHelper->setSession($session);

        $quote = $this->getQuoteByReservedOrderId->execute('test_order_with_customer_without_address');
        $quoteId = (int) $quote->getId();
        $this->checkoutSession->setQuoteId($quoteId);

        // Loading the checkout quote creates the mask for a remembered-but-logged-out customer.
        $this->checkoutSession->getQuote();
        $maskedId = $this->quoteIdMaskFactory->create()->load($quoteId, 'quote_id')->getMaskedId();
        $this->assertNotEmpty($maskedId);

        // The plugin runs at the boundary used by APSB26-73 guest-cart services.
        $this->quoteIdMaskFactory->create()->load($maskedId, 'masked_id');

        $convertedQuote = $this->quoteRepository->get($quoteId);
        $this->assertNull($convertedQuote->getCustomerId());
        $this->assertTrue((bool) $convertedQuote->getCustomerIsGuest());
        $this->assertFalse((bool) $convertedQuote->getIsPersistent());
        $this->assertFalse($this->persistentSessionHelper->isPersistent());
    }

    /**
     * @magentoDataFixture Magento/Persistent/_files/persistent_with_customer_quote_and_cookie.php
     * @magentoConfigFixture current_store persistent/options/enabled 1
     * @magentoConfigFixture current_store persistent/options/shopping_cart 1
     */
    public function testMaskedIdLoadDoesNotConvertQuoteWhenPersistentCustomerDoesNotOwnIt(): void
    {
        $session = $this->persistentSessionFactory->create()->loadByCustomerId(1);
        $this->persistentSessionHelper->setSession($session);

        $quote = $this->getQuoteByReservedOrderId->execute('test_order_with_customer_without_address');
        $quoteId = (int) $quote->getId();
        $this->checkoutSession->setQuoteId($quoteId);
        $this->checkoutSession->getQuote();
        $maskedId = $this->quoteIdMaskFactory->create()->load($quoteId, 'quote_id')->getMaskedId();
        $this->assertNotEmpty($maskedId);

        // Keep a valid persistent session, but make its in-memory owner differ from the quote owner.
        $session->setCustomerId(999);
        $this->persistentSessionHelper->setSession($session);

        $this->quoteIdMaskFactory->create()->load($maskedId, 'masked_id');

        $unchangedQuote = $this->quoteRepository->get($quoteId);
        $this->assertSame(1, (int) $unchangedQuote->getCustomerId());
        $this->assertTrue($this->persistentSessionHelper->isPersistent());
    }
}
