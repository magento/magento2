<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Customer;

use Magento\Framework\Exception\AuthenticationException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Check removing of the coupon from customer quotes
 */
class RemoveCouponFromCartTest extends GraphQlAbstract
{
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

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->quoteResource = $objectManager->create(QuoteResource::class);
        $this->quote = $objectManager->create(Quote::class);
        $this->quoteIdToMaskedId = $objectManager->create(QuoteIdToMaskedQuoteIdInterface::class);
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @magentoApiDataFixture Magento/SalesRule/_files/coupon_code_with_wildcard.php
     */
    public function testRemoveCouponFromCart()
    {
        $couponCode = '2?ds5!2d';

        /* Apply coupon to the customer quote */
        $this->quoteResource->load(
            $this->quote,
            'test_order_with_simple_product_without_address',
            'reserved_order_id'
        );
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$this->quote->getId());

        $this->quote->setCustomerId(1);
        $this->quoteResource->save($this->quote);

        $queryHeaders = $this->prepareAuthorizationHeaders('customer@example.com', 'password');
        $query = $this->prepareAddCouponRequestQuery($maskedQuoteId, $couponCode);
        $this->graphQlQuery($query, [], '', $queryHeaders);

        /* Remove coupon from the quote */
        $query = $this->prepareRemoveCouponRequestQuery($maskedQuoteId);
        $response = $this->graphQlQuery($query, [], '', $queryHeaders);

        self::assertArrayHasKey('removeCouponFromCart', $response);
        self::assertNull($response['removeCouponFromCart']['cart']['applied_coupon']['code']);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/two_customers.php
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @magentoApiDataFixture Magento/SalesRule/_files/coupon_code_with_wildcard.php
     */
    public function testRemoveCouponFromAonotherCustomerCart()
    {
        $couponCode = '2?ds5!2d';

        /* Apply coupon to the first customer quote */
        $this->quoteResource->load(
            $this->quote,
            'test_order_with_simple_product_without_address',
            'reserved_order_id'
        );
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$this->quote->getId());

        $this->quote->setCustomerId(1);
        $this->quoteResource->save($this->quote);

        $query = $this->prepareAddCouponRequestQuery($maskedQuoteId, $couponCode);
        $queryHeaders = $this->prepareAuthorizationHeaders('customer@example.com', 'password');
        $this->graphQlQuery($query, [], '', $queryHeaders);

        /* Remove coupon from the quote from the second customer */
        $query = $this->prepareRemoveCouponRequestQuery($maskedQuoteId);
        $queryHeaders = $this->prepareAuthorizationHeaders('customer_two@example.com', 'password');

        $this->expectExceptionMessage("The current user cannot perform operations on cart \"$maskedQuoteId\"");
        $this->graphQlQuery($query, [], '', $queryHeaders);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @magentoApiDataFixture Magento/SalesRule/_files/coupon_code_with_wildcard.php
     */
    public function testRemoveCouponFromGuestCart()
    {
        $couponCode = '2?ds5!2d';

        /* Apply coupon to the guest quote */
        $this->quoteResource->load(
            $this->quote,
            'test_order_with_simple_product_without_address',
            'reserved_order_id'
        );
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$this->quote->getId());

        $query = $this->prepareAddCouponRequestQuery($maskedQuoteId, $couponCode);
        $this->graphQlQuery($query);

        /* Remove coupon from quote */
        $query = $this->prepareRemoveCouponRequestQuery($maskedQuoteId);
        $queryHeaders = $this->prepareAuthorizationHeaders('customer@example.com', 'password');

        $this->expectExceptionMessage("The current user cannot perform operations on cart \"$maskedQuoteId\"");
        $this->graphQlQuery($query, [], '', $queryHeaders);
    }

    /**
     * Retrieve customer authorization headers
     *
     * @param string $email
     * @param string $password
     * @return array
     * @throws AuthenticationException
     */
    private function prepareAuthorizationHeaders(string $email, string $password): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($email, $password);

        return ['Authorization' => 'Bearer ' . $customerToken];
    }

    /**
     * Retrieve add coupon GraphQL query
     *
     * @param string $maskedQuoteId
     * @param string $couponCode
     * @return string
     */
    private function prepareAddCouponRequestQuery(string $maskedQuoteId, string $couponCode): string
    {
        return <<<QUERY
mutation {
  applyCouponToCart(input: {cart_id: "$maskedQuoteId", coupon_code: "$couponCode"}) {
    cart {
      applied_coupon {
        code
      }
    }
  }
}
QUERY;
    }

    /**
     * Retrieve remove coupon GraphQL query
     *
     * @param string $maskedQuoteId
     * @return string
     */
    private function prepareRemoveCouponRequestQuery(string $maskedQuoteId): string
    {
        return <<<QUERY
mutation {
  removeCouponFromCart(input: {cart_id: "$maskedQuoteId"}) {
    cart {
      applied_coupon {
        code
      }
    }
  }
}

QUERY;
    }
}
