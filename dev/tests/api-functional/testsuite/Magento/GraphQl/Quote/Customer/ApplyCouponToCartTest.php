<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Customer;

use Magento\Framework\Exception\AuthenticationException;
use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test Apply Coupon to Cart functionality for customer
 */
class ApplyCouponToCartTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var GetMaskedQuoteIdByReservedOrderId
     */
    private $getMaskedQuoteIdByReservedOrderId;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/discount_10percent_generalusers.php
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testApplyCouponToCart()
    {
        $couponCode = '2?ds5!2d';
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->getQuery($maskedQuoteId, $couponCode);
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());

        self::assertArrayHasKey('applyCouponToCart', $response);
        self::assertEquals($couponCode, $response['applyCouponToCart']['cart']['applied_coupon']['code']);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/discount_10percent_generalusers.php
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testApplyCouponTwice()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('A coupon is already applied to the cart. Please remove it to apply another');

        $couponCode = '2?ds5!2d';
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->getQuery($maskedQuoteId, $couponCode);
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());

        self::assertArrayHasKey("applyCouponToCart", $response);
        self::assertEquals($couponCode, $response['applyCouponToCart']['cart']['applied_coupon']['code']);

        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/discount_10percent_generalusers.php
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     */
    public function testApplyCouponToCartWithoutItems()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cart does not contain products.');

        $couponCode = '2?ds5!2d';
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->getQuery($maskedQuoteId, $couponCode);

        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * _security
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @magentoApiDataFixture Magento/SalesRule/_files/coupon_code_with_wildcard.php
     */
    public function testApplyCouponToGuestCart()
    {
        $this->expectException(\Exception::class);

        $couponCode = '2?ds5!2d';
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->getQuery($maskedQuoteId, $couponCode);

        self::expectExceptionMessage('The current user cannot perform operations on cart "' . $maskedQuoteId . '"');
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * _security
     * @magentoApiDataFixture Magento/Checkout/_files/discount_10percent_generalusers.php
     * @magentoApiDataFixture Magento/Customer/_files/two_customers.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testApplyCouponToAnotherCustomerCart()
    {
        $this->expectException(\Exception::class);

        $couponCode = '2?ds5!2d';
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->getQuery($maskedQuoteId, $couponCode);

        self::expectExceptionMessage('The current user cannot perform operations on cart "' . $maskedQuoteId . '"');
        $this->graphQlMutation($query, [], '', $this->getHeaderMap('customer_two@example.com'));
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     *
     */
    public function testApplyEmptyCouponCodeToCustomerCart()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Required parameter "coupon_code" is missing');

        $couponCode = '';
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->getQuery($maskedQuoteId, $couponCode);

        $this->graphQlMutation($query);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testApplyNonExistentCouponToCart()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The coupon code isn\'t valid. Verify the code and try again.');

        $couponCode = 'non_existent_coupon_code';
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->getQuery($maskedQuoteId, $couponCode);

        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/SalesRule/_files/coupon_code_with_wildcard.php
     */
    public function testApplyCouponToNonExistentCart()
    {
        $this->expectException(\Exception::class);

        $couponCode = '2?ds5!2d';
        $maskedQuoteId = 'non_existent_masked_id';
        $query = $this->getQuery($maskedQuoteId, $couponCode);

        self::expectExceptionMessage('Could not find a cart with ID "' . $maskedQuoteId . '"');
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * Products in cart don't fit to the coupon
     *
     * @magentoApiDataFixture Magento/Checkout/_files/discount_10percent_generalusers.php
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/restrict_coupon_usage_for_simple_product.php
     */
    public function testApplyCouponWhichIsNotApplicable()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The coupon code isn\'t valid. Verify the code and try again.');

        $couponCode = '2?ds5!2d';
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->getQuery($maskedQuoteId, $couponCode);

        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * Retrieve customer authorization headers
     *
     * @param string $username
     * @param string $password
     * @return array
     * @throws AuthenticationException
     */
    private function getHeaderMap(string $username = 'customer@example.com', string $password = 'password'): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($username, $password);
        $headerMap = ['Authorization' => 'Bearer ' . $customerToken];
        return $headerMap;
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
