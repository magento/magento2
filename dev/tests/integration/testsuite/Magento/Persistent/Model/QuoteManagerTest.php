<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Persistent\Model;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\ObjectManagerInterface;
use Magento\Persistent\Helper\Session as PersistentSessionHelper;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Quote\Model\GetQuoteByReservedOrderId;
use PHPUnit\Framework\TestCase;

/**
 * Test for persistent quote manager model
 *
 * @see \Magento\Persistent\Model\QuoteManager
 * @magentoDbIsolation enabled
 */
class QuoteManagerTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var QuoteManager */
    private $model;

    /** @var CheckoutSession */
    private $checkoutSession;

    /** @var GetQuoteByReservedOrderId */
    private $getQuoteByReservedOrderId;

    /** @var PersistentSessionHelper */
    private $persistentSessionHelper;

    /** @var CartInterface */
    private $quote;

    /** @var CartRepositoryInterface */
    private $quoteRepository;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->model = $this->objectManager->get(QuoteManager::class);
        $this->checkoutSession = $this->objectManager->get(CheckoutSession::class);
        $this->getQuoteByReservedOrderId = $this->objectManager->get(GetQuoteByReservedOrderId::class);
        $this->persistentSessionHelper = $this->objectManager->get(PersistentSessionHelper::class);
        $this->quoteRepository = $this->objectManager->get(CartRepositoryInterface::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->checkoutSession->clearQuote();
        $this->checkoutSession->setCustomerData(null);
        if ($this->quote instanceof CartInterface) {
            $this->quoteRepository->delete($this->quote);
        }

        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Persistent/_files/persistent_with_customer_quote_and_cookie.php
     * @magentoConfigFixture current_store persistent/options/enabled 1
     * @magentoConfigFixture current_store persistent/options/shopping_cart 1
     *
     * @return void
     */
    public function testPersistentShoppingCartEnabled(): void
    {
        $customerQuote = $this->getQuoteByReservedOrderId->execute('test_order_with_customer_without_address');
        $this->checkoutSession->setQuoteId($customerQuote->getId());
        $this->model->setGuest(true);
        $this->quote = $this->checkoutSession->getQuote();
        $this->assertNotEquals($customerQuote->getId(), $this->quote->getId());
        $this->assertFalse($this->model->isPersistent());
        $this->assertNull($this->quote->getCustomerId());
        $this->assertNull($this->quote->getCustomerEmail());
        $this->assertNull($this->quote->getCustomerFirstname());
        $this->assertNull($this->quote->getCustomerLastname());
        $this->assertEquals(GroupInterface::NOT_LOGGED_IN_ID, $this->quote->getCustomerGroupId());
        $this->assertEmpty($this->quote->getIsPersistent());
        $this->assertNull($this->persistentSessionHelper->getSession()->getId());
    }

    /**
     * @magentoDataFixture Magento/Persistent/_files/persistent_with_customer_quote_and_cookie.php
     * @magentoConfigFixture current_store persistent/options/enabled 1
     * @magentoConfigFixture current_store persistent/options/shopping_cart 0
     *
     * @return void
     */
    public function testPersistentShoppingCartDisabled(): void
    {
        $quote = $this->getQuoteByReservedOrderId->execute('test_order_with_customer_without_address');
        $this->checkoutSession->setQuoteId($quote->getId());
        $this->model->setGuest(true);
        $this->assertNull($this->checkoutSession->getQuote()->getId());
    }
}
