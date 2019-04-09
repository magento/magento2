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
 * Test Apply Coupon to Cart functionality for guest
 */
class ApplyCouponToCartTest extends GraphQlAbstract
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
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/SalesRule/_files/coupon_code_with_wildcard.php
     */
    public function testApplyCouponToCart()
    {
        $couponCode = '2?ds5!2d';
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->getQuery($maskedQuoteId, $couponCode);
        $response = $this->graphQlQuery($query);

        self::assertArrayHasKey('applyCouponToCart', $response);
        self::assertEquals($couponCode, $response['applyCouponToCart']['cart']['applied_coupon']['code']);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/SalesRule/_files/coupon_code_with_wildcard.php
     * @expectedException \Exception
     * @expectedExceptionMessage A coupon is already applied to the cart. Please remove it to apply another
     */
    public function testApplyCouponTwice()
    {
        $couponCode = '2?ds5!2d';
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->getQuery($maskedQuoteId, $couponCode);
        $response = $this->graphQlQuery($query);

        self::assertArrayHasKey("applyCouponToCart", $response);
        self::assertEquals($couponCode, $response['applyCouponToCart']['cart']['applied_coupon']['code']);

        $this->graphQlQuery($query);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/SalesRule/_files/coupon_code_with_wildcard.php
     * @expectedException \Exception
     * @expectedExceptionMessage Cart does not contain products.
     */
    public function testApplyCouponToCartWithoutItems()
    {
        $couponCode = '2?ds5!2d';
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->getQuery($maskedQuoteId, $couponCode);

        $this->graphQlQuery($query);
    }

    /**
     * _security
     * @magentoApiDataFixture Magento/SalesRule/_files/coupon_code_with_wildcard.php
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @expectedException \Exception
     */
    public function testApplyCouponToCustomerCart()
    {
        $couponCode = '2?ds5!2d';
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->getQuery($maskedQuoteId, $couponCode);

        self::expectExceptionMessage('The current user cannot perform operations on cart "' . $maskedQuoteId . '"');
        $this->graphQlQuery($query);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @expectedException \Exception
     * @expectedExceptionMessage The coupon code isn't valid. Verify the code and try again.
     */
    public function testApplyNonExistentCouponToCart()
    {
        $couponCode = 'non_existent_coupon_code';
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->getQuery($maskedQuoteId, $couponCode);

        $this->graphQlQuery($query);
    }

    /**
     * @magentoApiDataFixture Magento/SalesRule/_files/coupon_code_with_wildcard.php
     * @expectedException \Exception
     */
    public function testApplyCouponToNonExistentCart()
    {
        $couponCode = '2?ds5!2d';
        $maskedQuoteId = 'non_existent_masked_id';
        $query = $this->getQuery($maskedQuoteId, $couponCode);

        self::expectExceptionMessage('Could not find a cart with ID "' . $maskedQuoteId . '"');
        $this->graphQlQuery($query);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/SalesRule/_files/coupon_code_with_wildcard.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/make_coupon_expired.php
     * @expectedException \Exception
     * @expectedExceptionMessage The coupon code isn't valid. Verify the code and try again.
     */
    public function testApplyExpiredCoupon()
    {
        $this->markTestIncomplete('https://github.com/magento/graphql-ce/issues/574');
        $couponCode = '2?ds5!2d';
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->getQuery($maskedQuoteId, $couponCode);

        $this->graphQlQuery($query);
    }

    /**
     * Products in cart don't fit to the coupon
     *
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/SalesRule/_files/coupon_code_with_wildcard.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/restrict_coupon_usage_for_simple_product.php
     * @expectedException \Exception
     * @expectedExceptionMessage The coupon code isn't valid. Verify the code and try again.
     */
    public function testApplyCouponWhichIsNotApplicable()
    {
        $couponCode = '2?ds5!2d';
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->getQuery($maskedQuoteId, $couponCode);

        $this->graphQlQuery($query);
    }

    /**
     * @param string $input
     * @param string $message
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @dataProvider dataProviderUpdateWithMissedRequiredParameters
     * @expectedException \Exception
     */
    public function testApplyCouponWithMissedRequiredParameters(string $input, string $message)
    {
        $query = <<<QUERY
mutation {
  applyCouponToCart(input: {{$input}}) {
    cart {
      applied_coupon {
        code
      }
    }
  }
}
QUERY;

        $this->expectExceptionMessage($message);
        $this->graphQlQuery($query);
    }

    /**
     * @return array
     */
    public function dataProviderUpdateWithMissedRequiredParameters(): array
    {
        return [
            'missed_cart_id' => [
                'coupon_code: "test"',
                'Required parameter "cart_id" is missing'
            ],
            'missed_coupon_code' => [
                'cart_id: "test"',
                'Required parameter "coupon_code" is missing'
            ],
        ];
    }

    /**
     * @param string $maskedQuoteId
     * @param string $couponCode
     * @return string
     */
    private function getQuery(string $maskedQuoteId, string $couponCode): string
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
}
