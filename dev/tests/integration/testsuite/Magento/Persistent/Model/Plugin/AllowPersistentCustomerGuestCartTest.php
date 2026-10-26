<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Persistent\Model\Plugin;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Persistent\Helper\Session as PersistentSessionHelper;
use Magento\Persistent\Model\Session as PersistentSession;
use Magento\Quote\Api\GuestCartRepositoryInterface;
use Magento\Quote\Model\GuestCart\GetGuestCart;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Interception\PluginList;
use Magento\TestFramework\Quote\Model\GetQuoteByReservedOrderId;
use PHPUnit\Framework\TestCase;

/**
 * Verifies that a persistent customer's cart is accessible through guest checkout endpoints.
 *
 * @see \Magento\Persistent\Model\Plugin\AllowPersistentCustomerGuestCart
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 */
class AllowPersistentCustomerGuestCartTest extends TestCase
{
    /** @var PersistentSessionHelper */
    private $persistentSessionHelper;

    /** @var GuestCartRepositoryInterface */
    private $guestCartRepository;

    /** @var QuoteIdMaskFactory */
    private $quoteIdMaskFactory;

    /** @var GetQuoteByReservedOrderId */
    private $getQuoteByReservedOrderId;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $objectManager = Bootstrap::getObjectManager();
        $this->persistentSessionHelper = $objectManager->get(PersistentSessionHelper::class);
        $this->guestCartRepository = $objectManager->get(GuestCartRepositoryInterface::class);
        $this->quoteIdMaskFactory = $objectManager->get(QuoteIdMaskFactory::class);
        $this->getQuoteByReservedOrderId = $objectManager->get(GetQuoteByReservedOrderId::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->persistentSessionHelper->setSession(null);

        parent::tearDown();
    }

    /**
     * @return void
     */
    public function testPluginIsRegisteredOnGetGuestCart(): void
    {
        $pluginInfo = Bootstrap::getObjectManager()->get(PluginList::class)->get(GetGuestCart::class);
        $this->assertSame(
            AllowPersistentCustomerGuestCart::class,
            $pluginInfo['persistent_allow_guest_cart_access']['instance']
        );
    }

    /**
     * Verifies that a remembered shopper can access their cart through guest endpoints after session expiry.
     *
     * @magentoDataFixture Magento/Persistent/_files/persistent_with_customer_quote_and_cookie.php
     * @magentoConfigFixture current_store persistent/options/enabled 1
     * @magentoConfigFixture current_store persistent/options/shopping_cart 1
     *
     * @return void
     */
    public function testRememberedShopperCanReadTheirOwnCartThroughGuestEndpoint(): void
    {
        $this->persistentSessionHelper->setSession(
            Bootstrap::getObjectManager()->create(PersistentSession::class)->loadByCustomerId(1)
        );

        $quote = $this->getQuoteByReservedOrderId->execute('test_order_with_customer_without_address');
        $maskedId = $this->createMaskedId((int) $quote->getId());

        $cart = $this->guestCartRepository->get($maskedId);

        $this->assertEquals($quote->getId(), $cart->getId());
    }

    /**
     * Verifies that a cart cannot be accessed without an active persistent session for its owner.
     *
     * @magentoDataFixture Magento/Persistent/_files/persistent_with_customer_quote_and_cookie.php
     * @magentoConfigFixture current_store persistent/options/enabled 1
     * @magentoConfigFixture current_store persistent/options/shopping_cart 1
     *
     * @return void
     */
    public function testCartIsStillRejectedWithoutAnActivePersistentSession(): void
    {
        $quote = $this->getQuoteByReservedOrderId->execute('test_order_with_customer_without_address');
        $maskedId = $this->createMaskedId((int) $quote->getId());

        $this->expectException(NoSuchEntityException::class);

        $this->guestCartRepository->get($maskedId);
    }

    /**
     * @param int $quoteId
     * @return string
     */
    private function createMaskedId(int $quoteId): string
    {
        $quoteIdMask = $this->quoteIdMaskFactory->create();
        $quoteIdMask->setQuoteId($quoteId)->save();

        return $quoteIdMask->getMaskedId();
    }
}
