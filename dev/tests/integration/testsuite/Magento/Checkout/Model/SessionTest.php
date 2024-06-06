<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Model;

use Magento\Catalog\Api\Data\ProductTierPriceInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Quote\Model\GetQuoteByReservedOrderId;
use PHPUnit\Framework\TestCase;

/**
 * Checkout Session model test.
 *
 * @see \Magento\Checkout\Model\Session
 * @magentoDbIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SessionTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var GetQuoteByReservedOrderId
     */
    private $getQuoteByReservedOrderId;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var CartInterface
     */
    private $quote;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->customerRepository = $this->objectManager->create(CustomerRepositoryInterface::class);
        $this->customerSession = $this->objectManager->get(CustomerSession::class);
        $this->checkoutSession = $this->objectManager->get(Session::class);
        $this->getQuoteByReservedOrderId = $this->objectManager->get(GetQuoteByReservedOrderId::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
        $this->quoteRepository = $this->objectManager->get(CartRepositoryInterface::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        if ($this->quote instanceof CartInterface) {
            $this->quoteRepository->delete($this->quote);
        }
        $this->customerSession->setCustomerId(null);
        $this->checkoutSession->clearQuote();
        $this->checkoutSession->setCustomerData(null);

        parent::tearDown();
    }

    /**
     * Tests that quote items and totals are correct when product becomes unavailable.
     *
     * @magentoDataFixture Magento/Sales/_files/quote.php
     * @magentoAppIsolation enabled
     *
     * @return void
     */
    public function testGetQuoteWithUnavailableProduct(): void
    {
        $reservedOrderId = 'test01';
        $quoteGrandTotal = 10;
        $quote = $this->getQuoteByReservedOrderId->execute($reservedOrderId);
        $this->assertEquals(1, $quote->getItemsCount());
        $this->assertCount(1, $quote->getItems());
        $this->assertEquals($quoteGrandTotal, $quote->getShippingAddress()->getBaseGrandTotal());
        $product = $this->productRepository->get('simple');
        $product->setStatus(Status::STATUS_DISABLED);
        $this->productRepository->save($product);
        $this->checkoutSession->setQuoteId($quote->getId());
        $quote = $this->checkoutSession->getQuote();
        $this->assertEquals(0, $quote->getItemsCount());
        $this->assertEmpty($quote->getItems());
        $this->assertEquals(0, $quote->getShippingAddress()->getBaseGrandTotal());
    }

    /**
     * Test covers case when quote is not yet initialized and customer data is set to checkout session model.
     *
     * Expected result - quote object should be loaded and customer data should be set to it.
     *
     * @magentoDataFixture Magento/Sales/_files/quote_with_customer.php
     *
     * @return void
     */
    public function testGetQuoteNotInitializedCustomerSet(): void
    {
        $customer = $this->customerRepository->getById(1);
        $this->checkoutSession->setCustomerData($customer);
        $quote = $this->checkoutSession->getQuote();
        $this->validateCustomerDataInQuote($quote);
        $this->quoteRepository->delete($quote);
    }

    /**
     * Test covers case when quote is not yet initialized and customer data is set to customer session model.
     *
     * Expected result - quote object should be loaded and customer data should be set to it.
     *
     * @magentoDataFixture Magento/Sales/_files/quote_with_customer.php
     *
     * @return void
     */
    public function testGetQuoteNotInitializedCustomerLoggedIn(): void
    {
        $customer = $this->customerRepository->getById(1);
        $this->customerSession->setCustomerDataObject($customer);
        $quote = $this->checkoutSession->getQuote();
        $this->validateCustomerDataInQuote($quote);
        $this->quoteRepository->delete($quote);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/quote_with_customer.php
     *
     * @return void
     */
    public function testGetQuoteWithMismatchingSession(): void
    {
        $quote = $this->getQuoteByReservedOrderId->execute('test01');
        $this->checkoutSession->setQuoteId($quote->getId());
        $this->quote = $this->checkoutSession->getQuote();
        $this->assertEmpty($this->quote->getCustomerId());
        $this->assertNotEquals($quote->getId(), $this->quote->getId());
    }

    /**
     * Tes merging of customer data into initialized quote object.
     *
     * Conditions:
     * 1. Quote without customer data is set to checkout session
     * 2. Customer without associated quote is set to checkout session
     *
     * Expected result:
     * Quote which is set to checkout session should contain customer data
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @return void
     */
    public function testLoadCustomerQuoteCustomerWithoutQuote(): void
    {
        $this->quote = $this->checkoutSession->getQuote();
        $this->assertEmpty(
            $this->quote->getCustomerId(),
            'Precondition failed: Customer data must not be set to quote'
        );
        $this->assertEmpty(
            $this->quote->getCustomerEmail(),
            'Precondition failed: Customer data must not be set to quote'
        );
        self::assertEquals(
            '1',
            $this->quote->getCustomerIsGuest(),
            'Precondition failed: Customer must be as guest in quote'
        );
        $customer = $this->customerRepository->getById(1);
        $this->customerSession->setCustomerDataObject($customer);
        $this->quote = $this->checkoutSession->getQuote();
        $this->assertEmpty(
            $this->quote->getCustomerEmail(),
            'Precondition failed: Customer data must not be set to quote'
        );
        $this->checkoutSession->loadCustomerQuote();
        $this->quote = $this->checkoutSession->getQuote();
        $this->validateCustomerDataInQuote($this->quote);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Sales/_files/quote.php
     *
     * @return void
     */
    public function testGetQuoteWithProductWithTierPrice(): void
    {
        $reservedOrderId = 'test01';
        $customerGroupId = 1;
        $tierPriceQty = 1;
        $tierPriceValue = 9;
        $product = $this->productRepository->get('simple');
        $tierPrice = $this->objectManager->get(ProductTierPriceInterface::class)
            ->setCustomerGroupId($customerGroupId)
            ->setQty($tierPriceQty)
            ->setValue($tierPriceValue);
        $product->setTierPrices([$tierPrice]);
        $this->productRepository->save($product);
        $quote = $this->getQuoteByReservedOrderId->execute($reservedOrderId);
        $this->checkoutSession->setQuoteId($quote->getId());
        $quote = $this->checkoutSession->getQuote();
        $item = $quote->getItems()[0];
        $quoteProduct = $item->getProduct();
        $this->assertEquals(10, $quoteProduct->getTierPrice($tierPriceQty));
        $customer = $this->customerRepository->getById(1);
        $this->customerSession->setCustomerDataAsLoggedIn($customer);
        $quote = $this->checkoutSession->getQuote();
        $item = $quote->getItems()[0];
        $quoteProduct = $item->getProduct();
        $this->assertEquals($tierPriceValue, $quoteProduct->getTierPrice(1));
    }

    /**
     * Test covers case when quote is not yet initialized and customer is guest
     *
     * Expected result - quote object should be loaded with customer as guest
     */
    public function testGetQuoteNotInitializedGuest()
    {
        $quote = $this->checkoutSession->getQuote();
        self::assertEquals('1', $quote->getCustomerIsGuest());
    }

    /**
     * @magentoDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @magentoDataFixture Magento/Checkout/_files/quote_with_customer_without_address.php
     *
     * @return void
     */
    public function testMergeGuestQuoteWithCustomerQuote(): void
    {
        $guestQuote = $this->getQuoteByReservedOrderId->execute('test_order_with_simple_product_without_address');
        $customerQuote = $this->getQuoteByReservedOrderId->execute('test_order_with_customer_without_address');
        $this->checkoutSession->setQuoteId($guestQuote->getId());
        $this->customerSession->setCustomerId(1);
        $updatedQuote = $this->checkoutSession->loadCustomerQuote()->getQuote();
        $this->assertNull($this->getQuoteByReservedOrderId->execute('test_order_with_simple_product_without_address'));
        $this->assertEquals($customerQuote->getId(), $updatedQuote->getId());
        $this->assertCount(2, $updatedQuote->getItems());
    }

    /**
     * Ensure that quote has customer data specified in customer fixture.
     *
     * @param CartInterface $quote
     * @return void
     */
    private function validateCustomerDataInQuote(CartInterface $quote): void
    {
        $customerIdFromFixture = 1;
        $customerEmailFromFixture = 'customer@example.com';
        $customerFirstNameFromFixture = 'John';
        $this->assertEquals(
            $customerEmailFromFixture,
            $quote->getCustomerEmail(),
            'Customer email was not set to Quote correctly.'
        );
        $this->assertEquals(
            $customerIdFromFixture,
            $quote->getCustomerId(),
            'Customer ID was not set to Quote correctly.'
        );
        $this->assertEquals(
            $customerFirstNameFromFixture,
            $quote->getCustomerFirstname(),
            'Customer first name was not set to Quote correctly.'
        );
        self::assertEquals(
            '0',
            $quote->getCustomerIsGuest(),
            'Customer should not be as guest in Quote.'
        );
    }
}
