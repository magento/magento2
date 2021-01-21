<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Persistent\Model\Checkout;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\DefaultConfigProvider;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\ObjectManagerInterface;
use Magento\Persistent\Helper\Session as PersistentSessionHelper;
use Magento\Persistent\Model\Session as PersistentSession;
use Magento\Persistent\Model\SessionFactory as PersistentSessionFactory;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Interception\PluginList;
use Magento\TestFramework\Quote\Model\GetQuoteByReservedOrderId;
use PHPUnit\Framework\TestCase;

/**
 * Test for checkout config provider plugin
 *
 * @see \Magento\Persistent\Model\Checkout\ConfigProviderPlugin
 * @magentoAppArea frontend
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigProviderPluginTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var DefaultConfigProvider */
    private $configProvider;

    /** @var CustomerSession */
    private $customerSession;

    /** @var CheckoutSession */
    private $checkoutSession;

    /** @var QuoteIdMask */
    private $quoteIdMask;

    /** @var PersistentSessionHelper */
    private $persistentSessionHelper;

    /** @var PersistentSession */
    private $persistentSession;

    /** @var GetQuoteByReservedOrderId */
    private $getQuoteByReservedOrderId;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->configProvider = $this->objectManager->get(DefaultConfigProvider::class);
        $this->customerSession = $this->objectManager->get(CustomerSession::class);
        $this->checkoutSession = $this->objectManager->get(CheckoutSession::class);
        $this->quoteIdMask = $this->objectManager->get(QuoteIdMaskFactory::class)->create();
        $this->persistentSessionHelper = $this->objectManager->get(PersistentSessionHelper::class);
        $this->persistentSession = $this->objectManager->get(PersistentSessionFactory::class)->create();
        $this->getQuoteByReservedOrderId = $this->objectManager->get(GetQuoteByReservedOrderId::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->customerSession->setCustomerId(null);
        $this->checkoutSession->clearQuote();
        $this->checkoutSession->setCustomerData(null);

        parent::tearDown();
    }

    /**
     * @return void
     */
    public function testPluginIsRegistered(): void
    {
        $pluginInfo = $this->objectManager->get(PluginList::class)->get(DefaultConfigProvider::class);
        $this->assertSame(ConfigProviderPlugin::class, $pluginInfo['mask_quote_id_substitutor']['instance']);
    }

    /**
     * @magentoDataFixture Magento/Persistent/_files/persistent_with_customer_quote_and_cookie.php
     * @magentoConfigFixture current_store persistent/options/enabled 1
     *
     * @return void
     */
    public function testWithNotLoggedCustomer(): void
    {
        $session = $this->persistentSession->loadByCustomerId(1);
        $this->persistentSessionHelper->setSession($session);
        $quote = $this->getQuoteByReservedOrderId->execute('test_order_with_customer_without_address');
        $this->checkoutSession->setQuoteId($quote->getId());
        $result = $this->configProvider->getConfig();
        $this->assertEquals(
            $this->quoteIdMask->load($quote->getId(), 'quote_id')->getMaskedId(),
            $result['quoteData']['entity_id']
        );
    }

    /**
     * @magentoDataFixture Magento/Persistent/_files/persistent_with_customer_quote_and_cookie.php
     * @magentoConfigFixture current_store persistent/options/enabled 1
     *
     * @return void
     */
    public function testWithLoggedCustomer(): void
    {
        $this->customerSession->setCustomerId(1);
        $session = $this->persistentSession->loadByCustomerId(1);
        $this->persistentSessionHelper->setSession($session);
        $quote = $this->getQuoteByReservedOrderId->execute('test_order_with_customer_without_address');
        $this->checkoutSession->setQuoteId($quote->getId());
        $result = $this->configProvider->getConfig();
        $this->assertEquals($quote->getId(), $result['quoteData']['entity_id']);
    }

    /**
     * @magentoDataFixture Magento/Persistent/_files/persistent_with_customer_quote_and_cookie.php
     * @magentoConfigFixture current_store persistent/options/enabled 0
     *
     * @return void
     */
    public function testPersistentDisabled(): void
    {
        $quote = $this->getQuoteByReservedOrderId->execute('test_order_with_customer_without_address');
        $this->checkoutSession->setQuoteId($quote->getId());
        $result = $this->configProvider->getConfig();
        $this->assertNull($result['quoteData']['entity_id']);
    }

    /**
     * @magentoDataFixture Magento/Persistent/_files/persistent_with_customer_quote_and_cookie.php
     * @magentoConfigFixture current_store persistent/options/enabled 1
     *
     * @return void
     */
    public function testWithoutPersistentSession(): void
    {
        $quote = $this->getQuoteByReservedOrderId->execute('test_order_with_customer_without_address');
        $this->checkoutSession->setQuoteId($quote->getId());
        $result = $this->configProvider->getConfig();
        $this->assertNull($result['quoteData']['entity_id']);
    }
}
