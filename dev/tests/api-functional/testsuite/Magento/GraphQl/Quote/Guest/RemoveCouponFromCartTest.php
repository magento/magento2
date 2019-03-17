<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Guest;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\SalesRule\Api\CouponRepositoryInterface;
use Magento\SalesRule\Model\Coupon;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for getting cart information
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
     * @var CouponRepositoryInterface
     */
    private $couponRepository;

    /**
     * @var Coupon
     */
    private $coupon;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->quoteResource = $objectManager->create(QuoteResource::class);
        $this->quote = $objectManager->create(Quote::class);
        $this->quoteIdToMaskedId = $objectManager->create(QuoteIdToMaskedQuoteIdInterface::class);
        $this->couponRepository = $objectManager->get(CouponRepositoryInterface::class);
        $this->coupon = $objectManager->create(Coupon::class);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @magentoApiDataFixture Magento/SalesRule/_files/coupon_code_with_wildcard.php
     */
    public function testRemoveCouponFromCart()
    {
        $couponCode = '2?ds5!2d';

        /* Apply coupon to the quote */
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
        $response = $this->graphQlQuery($query);

        self::assertArrayHasKey('removeCouponFromCart', $response);
        self::assertNull($response['removeCouponFromCart']['cart']['applied_coupon']['code']);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @magentoApiDataFixture Magento/SalesRule/_files/coupon_code_with_wildcard.php
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testRemoveCouponFromCustomerCart()
    {
        $this->quoteResource->load(
            $this->quote,
            'test_order_with_simple_product_without_address',
            'reserved_order_id'
        );
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$this->quote->getId());

        $this->quote->setCustomerId(1);
        $this->quoteResource->save($this->quote);
        $query = $this->prepareRemoveCouponRequestQuery($maskedQuoteId);

        self::expectExceptionMessage('The current user cannot perform operations on cart "' . $maskedQuoteId . '"');
        $this->graphQlQuery($query);
    }

    public function testRemoveCouponFromNonExistentCart()
    {
        $maskedQuoteId = '1234000000099912';

        /* Remove coupon from quote */
        $query = $this->prepareRemoveCouponRequestQuery($maskedQuoteId);

        self::expectExceptionMessage('Could not find a cart with ID "' . $maskedQuoteId. '"');
        $this->graphQlQuery($query);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     */
    public function testRemoveCouponFromEmptyCart()
    {
        $this->quoteResource->load(
            $this->quote,
            'test_order_1',
            'reserved_order_id'
        );
        $quoteId = (int)$this->quote->getId();
        $maskedQuoteId = $this->quoteIdToMaskedId->execute($quoteId);

        /* Remove coupon from the empty quote */
        $query = $this->prepareRemoveCouponRequestQuery($maskedQuoteId);

        $this->expectExceptionMessage("The \"$quoteId\" Cart doesn't contain products");
        $this->graphQlQuery($query);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @magentoApiDataFixture Magento/SalesRule/_files/coupon_code_with_wildcard.php
     */
    public function testRemoveCouponFromCartWithoutItems()
    {
        $couponCode = '2?ds5!2d';

        $this->quoteResource->load(
            $this->quote,
            'test_order_with_simple_product_without_address',
            'reserved_order_id'
        );

        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$this->quote->getId());

        /* Apply coupon to the guest quote */
        $query = $this->prepareAddCouponRequestQuery($maskedQuoteId, $couponCode);
        $this->graphQlQuery($query);

        /* Clear the quote */
        $this->quote->removeAllItems();
        $this->quoteResource->save($this->quote);

        /* Remove coupon from the guest quote */
        $query = $this->prepareRemoveCouponRequestQuery($maskedQuoteId);
        $response = $this->graphQlQuery($query);

        self::assertArrayHasKey('removeCouponFromCart', $response);
        self::assertNull($response['removeCouponFromCart']['cart']['applied_coupon']['code']);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @magentoApiDataFixture Magento/SalesRule/_files/coupon_code_with_wildcard.php
     */
    public function testRemoveNonExistentCouponFromCart()
    {
        $couponCode = '2?ds5!2d';

        $this->quoteResource->load(
            $this->quote,
            'test_order_with_simple_product_without_address',
            'reserved_order_id'
        );
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$this->quote->getId());

        /* Apply coupon to the guest quote */
        $query = $this->prepareAddCouponRequestQuery($maskedQuoteId, $couponCode);
        $this->graphQlQuery($query);

        /* Remove the coupon */
        $this->removeCoupon($couponCode);

        /* Remove the non-existent coupon from the quote */
        $query = $this->prepareRemoveCouponRequestQuery($maskedQuoteId);

        $response = $this->graphQlQuery($query);

        self::assertArrayHasKey('removeCouponFromCart', $response);
        self::assertNull($response['removeCouponFromCart']['cart']['applied_coupon']['code']);
    }

    /**
     * Remove the given coupon code from the database
     *
     * @param string $couponCode
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function removeCoupon(string $couponCode): void
    {
        $this->coupon->loadByCode($couponCode);
        $couponId = $this->coupon->getCouponId();

        if ($couponId) {
            $this->couponRepository->deleteById($couponId);
        }
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
