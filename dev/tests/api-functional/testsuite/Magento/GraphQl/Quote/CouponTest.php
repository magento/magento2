<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for adding/removing shopping cart coupon codes
 */
class CouponTest extends GraphQlAbstract
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

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->quoteResource = $objectManager->create(QuoteResource::class);
        $this->quote = $objectManager->create(Quote::class);
        $this->quoteIdToMaskedId = $objectManager->create(QuoteIdToMaskedQuoteIdInterface::class);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @magentoApiDataFixture Magento/SalesRule/_files/coupon_code_with_wildcard.php
     */
    public function testApplyCouponToGuestCartWithItems()
    {
        $couponCode = '2?ds5!2d';

        $this->quoteResource->load(
            $this->quote,
            'test_order_with_simple_product_without_address',
            'reserved_order_id'
        );
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$this->quote->getId());
        $query = $this->prepareAddCouponRequestQuery($maskedQuoteId, $couponCode);
        $response = $this->graphQlQuery($query);

        self::assertArrayHasKey("applyCouponToCart", $response);
        self::assertEquals($couponCode, $response['applyCouponToCart']['cart']['applied_coupon']['code']);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @magentoApiDataFixture Magento/SalesRule/_files/coupon_code_with_wildcard.php
     */
    public function testApplyCouponTwice()
    {
        $couponCode = '2?ds5!2d';

        $this->quoteResource->load(
            $this->quote,
            'test_order_with_simple_product_without_address',
            'reserved_order_id'
        );
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$this->quote->getId());
        $query = $this->prepareAddCouponRequestQuery($maskedQuoteId, $couponCode);
        $response = $this->graphQlQuery($query);

        self::assertArrayHasKey("applyCouponToCart", $response);
        self::assertEquals($couponCode, $response['applyCouponToCart']['cart']['applied_coupon']['code']);

        self::expectExceptionMessage('A coupon is already applied to the cart. Please remove it to apply another');
        $this->graphQlQuery($query);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     * @magentoApiDataFixture Magento/SalesRule/_files/coupon_code_with_wildcard.php
     */
    public function testApplyCouponToCartWithNoItems()
    {
        $this->markTestIncomplete('https://github.com/magento/graphql-ce/issues/191');
        $couponCode = '2?ds5!2d';

        $this->quoteResource->load($this->quote, 'test_order_1', 'reserved_order_id');
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$this->quote->getId());
        $query = $this->prepareAddCouponRequestQuery($maskedQuoteId, $couponCode);

        self::expectExceptionMessageRegExp('/Cart doesn\'t contain products/');
        $this->graphQlQuery($query);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @magentoApiDataFixture Magento/SalesRule/_files/coupon_code_with_wildcard.php
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testGuestCustomerAttemptToChangeCustomerCart()
    {
        $couponCode = '2?ds5!2d';

        $this->quoteResource->load(
            $this->quote,
            'test_order_with_simple_product_without_address',
            'reserved_order_id'
        );
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$this->quote->getId());
        $this->quote->setCustomerId(1);
        $this->quoteResource->save($this->quote);
        $query = $this->prepareAddCouponRequestQuery($maskedQuoteId, $couponCode);

        self::expectExceptionMessage('The current user cannot perform operations on cart "' . $maskedQuoteId . '"');
        $this->graphQlQuery($query);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @magentoApiDataFixture Magento/SalesRule/_files/coupon_code_with_wildcard.php
     */
    public function testRemoveCoupon()
    {
        $couponCode = '2?ds5!2d';

        /* Apply coupon to the quote */
        $this->quoteResource->load(
            $this->quote,
            'test_order_with_simple_product_without_address',
            'reserved_order_id'
        );
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$this->quote->getId());
        $this->quoteResource->load(
            $this->quote,
            'test_order_with_simple_product_without_address',
            'reserved_order_id'
        );
        $query = $this->prepareAddCouponRequestQuery($maskedQuoteId, $couponCode);
        $this->graphQlQuery($query);

        /* Remove coupon from quote */
        $query = $this->prepareRemoveCouponRequestQuery($maskedQuoteId);
        $response = $this->graphQlQuery($query);

        self::assertArrayHasKey('removeCouponFromCart', $response);
        self::assertSame('', $response['removeCouponFromCart']['cart']['applied_coupon']['code']);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @magentoApiDataFixture Magento/SalesRule/_files/coupon_code_with_wildcard.php
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testRemoveCouponFromCustomerCartByGuest()
    {
        $this->quoteResource->load(
            $this->quote,
            'test_order_with_simple_product_without_address',
            'reserved_order_id'
        );
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$this->quote->getId());
        $this->quoteResource->load(
            $this->quote,
            'test_order_with_simple_product_without_address',
            'reserved_order_id'
        );
        $this->quote->setCustomerId(1);
        $this->quoteResource->save($this->quote);
        $query = $this->prepareRemoveCouponRequestQuery($maskedQuoteId);

        self::expectExceptionMessage('The current user cannot perform operations on cart "' . $maskedQuoteId . '"');
        $this->graphQlQuery($query);
    }

    /**
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
