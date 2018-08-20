<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Model;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class SessionTest
 */
class SessionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->_checkoutSession = Bootstrap::getObjectManager()->create(\Magento\Checkout\Model\Session::class);
        parent::setUp();
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
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository */
        $customerRepository = $objectManager->create(\Magento\Customer\Api\CustomerRepositoryInterface::class);
        $customer = $customerRepository->getById(1);
        $this->_checkoutSession->setCustomerData($customer);

        /** Execute SUT */
        $quote = $this->_checkoutSession->getQuote();
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
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository */
        $customerRepository = $objectManager->create(\Magento\Customer\Api\CustomerRepositoryInterface::class);
        $customer = $customerRepository->getById(1);
        /** @var \Magento\Customer\Model\Session $customerSession */
        $customerSession = Bootstrap::getObjectManager()->get(\Magento\Customer\Model\Session::class);
        $customerSession->setCustomerDataObject($customer);

        /** Execute SUT */
        $quote = $this->_checkoutSession->getQuote();
        $this->_validateCustomerDataInQuote($quote);
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
        $quote = $this->_checkoutSession->getQuote();
        $this->assertEmpty($quote->getCustomerId(), 'Precondition failed: Customer data must not be set to quote');
        $this->assertEmpty($quote->getCustomerEmail(), 'Precondition failed: Customer data must not be set to quote');

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository */
        $customerRepository = $objectManager->create(\Magento\Customer\Api\CustomerRepositoryInterface::class);
        $customer = $customerRepository->getById(1);
        /** @var \Magento\Customer\Model\Session $customerSession */
        $customerSession = Bootstrap::getObjectManager()->get(\Magento\Customer\Model\Session::class);
        $customerSession->setCustomerDataObject($customer);

        /** Ensure that customer data is still unavailable before SUT invocation */
        $quote = $this->_checkoutSession->getQuote();
        $this->assertEmpty($quote->getCustomerEmail(), 'Precondition failed: Customer data must not be set to quote');

        /** Execute SUT */
        $this->_checkoutSession->loadCustomerQuote();
        $quote = $this->_checkoutSession->getQuote();
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

        $productRepository = Bootstrap::getObjectManager()->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $product = $productRepository->get('simple');
        $tierPrice = Bootstrap::getObjectManager()->create(\Magento\Catalog\Api\Data\ProductTierPriceInterface::class)
            ->setCustomerGroupId($customerGroupId)
            ->setQty($tierPriceQty)
            ->setValue($tierPriceValue);
        $product->setTierPrices([$tierPrice]);
        $productRepository->save($product);

        $quote = $this->getQuote($reservedOrderId);
        $this->_checkoutSession->setQuoteId($quote->getId());

        $quote = $this->_checkoutSession->getQuote();
        $item = $quote->getItems()[0];
        /** @var \Magento\Catalog\Model\Product $quoteProduct */
        $quoteProduct = $item->getProduct();
        $this->assertEquals(10, $quoteProduct->getTierPrice($tierPriceQty));

        $customerRepository = Bootstrap::getObjectManager()->get(\Magento\Customer\Api\CustomerRepositoryInterface::class);
        $customer = $customerRepository->getById(1);
        $customerSession = Bootstrap::getObjectManager()->get(\Magento\Customer\Model\Session::class);
        $customerSession->setCustomerDataAsLoggedIn($customer);

        $quote = $this->_checkoutSession->getQuote();
        $item = $quote->getItems()[0];
        /** @var \Magento\Catalog\Model\Product $quoteProduct */
        $quoteProduct = $item->getProduct();
        $this->assertEquals($tierPriceValue, $quoteProduct->getTierPrice(1));
    }

    /**
     * Returns quote by reserved order id.
     *
     * @param string $reservedOrderId
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    private function getQuote(string $reservedOrderId): \Magento\Quote\Api\Data\CartInterface
    {
        $filterBuilder = Bootstrap::getObjectManager()->create(\Magento\Framework\Api\FilterBuilder::class);
        $filter = $filterBuilder->setField('reserved_order_id')
            ->setConditionType('=')
            ->setValue($reservedOrderId)
            ->create();
        $searchCriteriaBuilder = Bootstrap::getObjectManager()->create(\Magento\Framework\Api\SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilters([$filter])
            ->create();
        $quoteRepository = Bootstrap::getObjectManager()->get(\Magento\Quote\Api\CartRepositoryInterface::class);
        $searchResult = $quoteRepository->getList($searchCriteria);
        /** @var \Magento\Quote\Api\Data\CartInterface[] $items */
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
