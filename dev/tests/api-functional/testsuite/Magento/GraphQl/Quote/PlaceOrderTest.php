<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for placing an order
 */
class PlaceOrderTest extends GraphQlAbstract
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var QuoteResource
     */
    private $quoteResource;

    /**
     * @var Quote
     */
    private $quote;

    /**
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    private $quoteIdToMaskedId;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->quoteResource = $this->objectManager->create(QuoteResource::class);
        $this->quote = $this->objectManager->create(Quote::class);
        $this->quoteIdToMaskedId = $this->objectManager->create(QuoteIdToMaskedQuoteIdInterface::class);
        $this->customerTokenService = $this->objectManager->get(CustomerTokenServiceInterface::class);
        $this->quoteResource->load(
            $this->quote,
            'test_order_1',
            'reserved_order_id'
        );
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_check_payment.php
     */
    public function testPlaceOrder()
    {
        $reservedOrderId = 'test_order_1';

        $query = $this->preparePlaceOrderQuery();
        $response = $this->graphQlQuery($query, [], '', $this->getHeaderMap());

        self::assertArrayHasKey('order_id', $response['placeOrder']['order']);
        self::assertEquals($reservedOrderId, $response['placeOrder']['order']['order_id']);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_check_payment.php
     */
    public function testPlaceOrderOfAnotherCustomerCart()
    {
        $query = $this->preparePlaceOrderQuery();

        self::expectExceptionMessageRegExp('/The current user cannot perform operations on cart*/');
        $this->graphQlQuery($query);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_check_payment.php
     */
    public function testPlaceOrderWithOutOfStockProduct()
    {
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get('simple');
        $extensionAttributes = $product->getExtensionAttributes();
        $stockItem = $extensionAttributes->getStockItem();
        $stockItem->setIsInStock(false);
        $productRepository->save($product);

        $query = $this->preparePlaceOrderQuery();

        self::expectExceptionMessage('Unable to place order: Some of the products are out of stock');
        $this->graphQlQuery($query, [], '', $this->getHeaderMap());
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_check_payment.php
     */
    public function testPlaceOrderWithNoItemsInCart()
    {
        $quoteItems = $this->quote->getAllItems();

        /** @var CartItemInterface $quoteItem */
        foreach ($quoteItems as $quoteItem) {
            $this->quote->removeItem($quoteItem->getItemId());
        }
        $this->quoteResource->save($this->quote);

        $query = $this->preparePlaceOrderQuery();

        self::expectExceptionMessage(
            'Unable to place order: A server error stopped your order from being placed. ' .
            'Please try to place your order again'
        );
        $this->graphQlQuery($query, [], '', $this->getHeaderMap());
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_check_payment.php
     */
    public function testPlaceOrderWithNoShippingMethod()
    {
        $this->quote->getShippingAddress()->setShippingMethod('');
        $this->quoteResource->save($this->quote);

        $query = $this->preparePlaceOrderQuery();

        self::expectExceptionMessage(
            'Unable to place order: The shipping method is missing. Select the shipping method and try again'
        );
        $this->graphQlQuery($query, [], '', $this->getHeaderMap());
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_check_payment.php
     */
    public function testPlaceOrderWithNoShippingAddress()
    {
        $this->quote->removeAddress($this->quote->getShippingAddress()->getId());
        $this->quoteResource->save($this->quote);

        $query = $this->preparePlaceOrderQuery();

        self::expectExceptionMessage(
            'Unable to place order: Some addresses can\'t be used due to the configurations for specific countries'
        );
        $this->graphQlQuery($query, [], '', $this->getHeaderMap());
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_check_payment.php
     */
    public function testPlaceOrderWithNoPaymentMethod()
    {
        $this->quote->getPayment()->setMethod('');
        $this->quoteResource->save($this->quote);

        $query = $this->preparePlaceOrderQuery();

        self::expectExceptionMessage('Unable to place order: Enter a valid payment method and try again');
        $this->graphQlQuery($query, [], '', $this->getHeaderMap());
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_check_payment.php
     */
    public function testPlaceOrderWithNoBillingAddress()
    {
        $this->quote->removeAddress($this->quote->getBillingAddress()->getId());
        $this->quoteResource->save($this->quote);

        $query = $this->preparePlaceOrderQuery();

        self::expectExceptionMessageRegExp(
            '/Unable to place order: Please check the billing address information*/'
        );
        $this->graphQlQuery($query, [], '', $this->getHeaderMap());
    }

    /**
     * Prepares GraphQl query for placing an order
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function preparePlaceOrderQuery(): string
    {
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$this->quote->getId());

        return <<<QUERY
mutation {
  placeOrder(input: {cart_id: "$maskedQuoteId"}) {
    order {
      order_id
    }
  }
}
QUERY;
    }

    /**
     * @param string $username
     * @param string $password
     * @return array
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    private function getHeaderMap(string $username = 'customer@example.com', string $password = 'password'): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($username, $password);
        $headerMap = ['Authorization' => 'Bearer ' . $customerToken];
        return $headerMap;
    }
}
