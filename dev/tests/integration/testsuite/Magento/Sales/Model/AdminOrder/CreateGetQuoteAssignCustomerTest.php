<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\AdminOrder;

use Magento\Backend\Model\Session\Quote as AdminQuoteSession;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use PHPUnit\Framework\TestCase;

class CreateGetQuoteAssignCustomerTest extends TestCase
{
    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->fixtures = $this->objectManager->get(DataFixtureStorageManager::class)->getStorage();
    }

    /**
     * Ensures Create::getQuote() assigns fresh CustomerInterface to the quote
     * when a customerId is present on the quote.
     *
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     */
    #[
        DataFixture(CustomerFixture::class, as: 'customer')
    ]
    public function testGetQuoteAssignsFreshCustomerDataWhenCustomerIdPresent(): void
    {
        /** @var StoreManagerInterface $storeManager */
        $storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $storeId = (int)$storeManager->getStore()->getId();

        /** @var CustomerRepositoryInterface $customerRepo */
        $customerRepo = $this->objectManager->get(CustomerRepositoryInterface::class);

        /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
        $customer = $this->fixtures->get('customer');
        $customer = $customerRepo->getById((int)$customer->getId()); // ensure latest

        /** @var QuoteFactory $quoteFactory */
        $quoteFactory = $this->objectManager->get(QuoteFactory::class);
        /** @var CartRepositoryInterface $quoteRepo */
        $quoteRepo = $this->objectManager->get(CartRepositoryInterface::class);
        /** @var AdminQuoteSession $session */
        $session = $this->objectManager->get(AdminQuoteSession::class);

        // Create a quote with stale customer-related fields
        $quote = $quoteFactory->create();
        $quote->setStoreId($storeId);
        $quote->setCustomerId((int)$customer->getId());
        $quote->setCustomerEmail('stale@example.com');
        $quoteRepo->save($quote);

        $session->setQuoteId((int)$quote->getId());

        /** @var Create $create */
        $create = $this->objectManager->create(Create::class);

        $result = $create->getQuote();

        // Assert quote was refreshed from repository via assignCustomer()
        $this->assertSame((int)$customer->getId(), (int)$result->getCustomerId());
        $this->assertSame($customer->getEmail(), $result->getCustomerEmail());
    }

    /**
     * Ensures Create::getQuote() does not assign a customer when no customerId is present.
     *
     * @magentoAppArea adminhtml
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testGetQuoteDoesNotAssignWhenNoCustomerId(): void
    {
        $session = $this->objectManager->get(\Magento\Backend\Model\Session\Quote::class);
        $session->unsQuoteId();
        $session->unsCustomerId();

        $storeManager = $this->objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
        $storeId = (int)$storeManager->getStore()->getId();

        $quoteFactory = $this->objectManager->get(\Magento\Quote\Model\QuoteFactory::class);

        // Create a quote without a customer
        $quote = $quoteFactory->create();
        $quote->setStoreId($storeId);
        $quote->setCustomerId(null);
        $quote->setCustomerEmail('stale@example.com');

        /** @var \Magento\Sales\Model\AdminOrder\Create $create */
        $create = $this->objectManager->create(\Magento\Sales\Model\AdminOrder\Create::class);

        // Bypass session state by injecting the quote directly
        $create->setQuote($quote);

        $result = $create->getQuote();

        $this->assertSame($quote, $result);
        $this->assertEmpty($result->getCustomerId());
        $this->assertSame('stale@example.com', $result->getCustomerEmail());
    }
}
