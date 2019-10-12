<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Guest;

use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test is getting cart discount for guest customer.
 */
class CartDiscountTest extends GraphQlAbstract
{
    /**
     * @var GetMaskedQuoteIdByReservedOrderId
     */
    private $getMaskedQuoteIdByReservedOrderId;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/cart_rule_discount_no_coupon.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testGetDiscountInformation()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlQuery($query);
        $discountResponse = $response['cart']['prices']['discount'];
        self::assertEquals(-10, $discountResponse['amount']['value']);
        self::assertEquals(['50% Off for all orders'], $discountResponse['label']);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/cart_rule_discount_no_coupon.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/SalesRule/_files/coupon_code_with_wildcard.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/apply_coupon.php
     */
    public function testGetDiscountInformationWithTwoRulesApplied()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlQuery($query);
        $discountResponse = $response['cart']['prices']['discount'];
        self::assertEquals(-15, $discountResponse['amount']['value']);
        self::assertEquals(['50% Off for all orders', '5$ fixed discount on whole cart'], $discountResponse['label']);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testGetDiscountInformationWithNoRulesApplied()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlQuery($query);
        self::assertEquals(null, $response['cart']['prices']['discount']);
    }

    /**
     * Generates GraphQl query for retrieving cart discount.
     *
     * @param string $maskedQuoteId
     * @return string
     */
    private function getQuery(string $maskedQuoteId): string
    {
        return <<<QUERY
{
  cart(cart_id: "$maskedQuoteId") {
    prices {
      discount {
        label
        amount {
          value
          currency
        }
      }
    }
  }
}
QUERY;
    }
}
