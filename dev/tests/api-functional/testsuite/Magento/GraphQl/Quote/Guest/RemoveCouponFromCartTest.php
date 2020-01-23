<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Guest;

use Exception;
use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Check removing of the coupon from guest cart
 */
class RemoveCouponFromCartTest extends GraphQlAbstract
{
    /**
     * @var GetMaskedQuoteIdByReservedOrderId
     */
    private $getMaskedQuoteIdByReservedOrderId;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/SalesRule/_files/coupon_code_with_wildcard.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/apply_coupon.php
     */
    public function testRemoveCouponFromCart()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlMutation($query);

        self::assertArrayHasKey('removeCouponFromCart', $response);
        self::assertNull($response['removeCouponFromCart']['cart']['applied_coupon']['code']);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Required parameter "cart_id" is missing
     */
    public function testRemoveCouponFromCartIfCartIdIsEmpty()
    {
        $maskedQuoteId = '';
        $query = $this->getQuery($maskedQuoteId);

        $this->graphQlMutation($query);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Could not find a cart with ID "non_existent_masked_id"
     */
    public function testRemoveCouponFromNonExistentCart()
    {
        $maskedQuoteId = 'non_existent_masked_id';
        $query = $this->getQuery($maskedQuoteId);

        $this->graphQlMutation($query);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @expectedException Exception
     * @expectedExceptionMessage Cart does not contain products
     */
    public function testRemoveCouponFromEmptyCart()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->getQuery($maskedQuoteId);

        $this->graphQlMutation($query);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testRemoveCouponFromCartIfCouponWasNotSet()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlMutation($query);

        self::assertArrayHasKey('removeCouponFromCart', $response);
        self::assertNull($response['removeCouponFromCart']['cart']['applied_coupon']['code']);
    }

    /**
     * _security
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/Checkout/_files/discount_10percent_generalusers.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/apply_coupon.php
     */
    public function testRemoveCouponFromCustomerCart()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->getQuery($maskedQuoteId);

        self::expectExceptionMessage('The current user cannot perform operations on cart "' . $maskedQuoteId . '"');
        $this->graphQlMutation($query);
    }

    /**
     * @param string $maskedQuoteId
     * @return string
     */
    private function getQuery(string $maskedQuoteId): string
    {
        return <<<QUERY
mutation {
  removeCouponFromCart(input: {cart_id: "{$maskedQuoteId}"}) {
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
