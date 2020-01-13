<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Model;

use Magento\Catalog\Api\Data\ProductTierPriceInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class SessionTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SessionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
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
     * @return void
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->customerRepository = $this->objectManager->create(CustomerRepositoryInterface::class);
        $this->customerSession = $this->objectManager->get(CustomerSession::class);
        $this->checkoutSession = $this->objectManager->create(Session::class);
    }

    /**
     * Tests that quote items and totals are correct when product becomes unavailable.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Sales/_files/quote.php
     * @magentoAppIsolation enabled
     */
    public function testGetQuoteWithUnavailableProduct()
    {
        $reservedOrderId = 'test01';
        $quoteGrandTotal = 10;

        $quote = $this->getQuote($reservedOrderId);
        $this->assertEquals(1, $quote->getItemsCount());
        $this->assertCount(1, $quote->getItems());
        $this->assertEquals($quoteGrandTotal, $quote->getShippingAddress()->getBaseGrandTotal());

        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get('simple');
        $product->setStatus(Status::STATUS_DISABLED);
        $productRepository->save($product);
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
     */
    public function testGetQuoteNotInitializedCustomerSet()
    {
        $customer = $this->customerRepository->getById(1);
        $this->checkoutSession->setCustomerData($customer);

        /** Execute SUT */
        $quote = $this->checkoutSession->getQuote();
        $this->_validateCustomerDataInQuote($quote);
    }

    /**
     * Test covers case when quote is not yet initialized and customer data is set to customer session model.
     *
     * Expected result - quote object should be loaded and customer data should be set to it.
     *
     * @magentoDataFixture Magento/Sales/_files/quote_with_customer.php
     * @magentoAppIsolation enabled
     */
    public function testGetQuoteNotInitializedCustomerLoggedIn()
    {
        $customer = $this->customerRepository->getById(1);
        $this->customerSession->setCustomerDataObject($customer);

        /** Execute SUT */
        $quote = $this->checkoutSession->getQuote();
        $this->_validateCustomerDataInQuote($quote);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/quote_with_customer.php
     * @magentoAppIsolation enabled
     */
    public function testGetQuoteWithMismatchingSession()
    {
        /** @var Quote $quote */
        $quote = Bootstrap::getObjectManager()->create(Quote::class);
        /** @var \Magento\Quote\Model\ResourceModel\Quote $quoteResource */
        $quoteResource = Bootstrap::getObjectManager()->create(\Magento\Quote\Model\ResourceModel\Quote::class);
        $quoteResource->load($quote, 'test01', 'reserved_order_id');

        // Customer on quote is not logged in
        $this->checkoutSession->setQuoteId($quote->getId());

        $sessionQuote = $this->checkoutSession->getQuote();
        $this->assertEmpty($sessionQuote->getCustomerId());
        $this->assertNotEquals($quote->getId(), $sessionQuote->getId());
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
     * @magentoAppIsolation enabled
     */
    public function testLoadCustomerQuoteCustomerWithoutQuote()
    {
        $quote = $this->checkoutSession->getQuote();
        $this->assertEmpty($quote->getCustomerId(), 'Precondition failed: Customer data must not be set to quote');
        $this->assertEmpty($quote->getCustomerEmail(), 'Precondition failed: Customer data must not be set to quote');

        $customer = $this->customerRepository->getById(1);
        $this->customerSession->setCustomerDataObject($customer);

        /** Ensure that customer data is still unavailable before SUT invocation */
        $quote = $this->checkoutSession->getQuote();
        $this->assertEmpty($quote->getCustomerEmail(), 'Precondition failed: Customer data must not be set to quote');

        /** Execute SUT */
        $this->checkoutSession->loadCustomerQuote();
        $quote = $this->checkoutSession->getQuote();
        $this->_validateCustomerDataInQuote($quote);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Sales/_files/quote.php
     */
    public function testGetQuoteWithProductWithTierPrice()
    {
        $reservedOrderId = 'test01';
        $customerGroupId = 1;
        $tierPriceQty = 1;
        $tierPriceValue = 9;

        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get('simple');
        $tierPrice = $this->objectManager->create(ProductTierPriceInterface::class)
            ->setCustomerGroupId($customerGroupId)
            ->setQty($tierPriceQty)
            ->setValue($tierPriceValue);
        $product->setTierPrices([$tierPrice]);
        $productRepository->save($product);

        $quote = $this->getQuote($reservedOrderId);
        $this->checkoutSession->setQuoteId($quote->getId());

        $quote = $this->checkoutSession->getQuote();
        $item = $quote->getItems()[0];
        /** @var \Magento\Catalog\Model\Product $quoteProduct */
        $quoteProduct = $item->getProduct();
        $this->assertEquals(10, $quoteProduct->getTierPrice($tierPriceQty));

        $customer = $this->customerRepository->getById(1);
        $this->customerSession->setCustomerDataAsLoggedIn($customer);

        $quote = $this->checkoutSession->getQuote();
        $item = $quote->getItems()[0];
        /** @var \Magento\Catalog\Model\Product $quoteProduct */
        $quoteProduct = $item->getProduct();
        $this->assertEquals($tierPriceValue, $quoteProduct->getTierPrice(1));
    }

    /**
     * Returns quote by reserved order id.
     *
     * @param string $reservedOrderId
     * @return CartInterface
     */
    private function getQuote(string $reservedOrderId): CartInterface
    {
        $filterBuilder = $this->objectManager->create(FilterBuilder::class);
        $filter = $filterBuilder->setField('reserved_order_id')
            ->setConditionType('=')
            ->setValue($reservedOrderId)
            ->create();
        $searchCriteriaBuilder = $this->objectManager->create(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilters([$filter])
            ->create();
        $quoteRepository = $this->objectManager->get(CartRepositoryInterface::class);
        $searchResult = $quoteRepository->getList($searchCriteria);
        /** @var CartInterface[] $items */
        $items = $searchResult->getItems();

        return \array_values($items)[0];
    }

    /**
     * Ensure that quote has customer data specified in customer fixture.
     *
     * @param \Magento\Quote\Model\Quote $quote
     */
    protected function _validateCustomerDataInQuote($quote)
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
    }
}
