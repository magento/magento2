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
use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Spi\CouponResourceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test Apply Coupon to Cart functionality for customer
 */
class ApplyCouponToCartTest extends GraphQlAbstract
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
     * @var CouponResourceInterface
     */
    protected $couponResource;

    /**
     * @var Coupon
     */
    private $coupon;

    /**
     * @var Rule
     */
    private $salesRule;

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
        $this->couponResource = $objectManager->get(CouponResourceInterface::class);
        $this->coupon = $objectManager->create(Coupon::class);
        $this->salesRule = $objectManager->create(Rule::class);
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @magentoApiDataFixture Magento/SalesRule/_files/coupon_code_with_wildcard.php
     */
    public function testApplyCouponToCart()
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
        $queryHeaders = $this->prepareAuthorizationHeaders('customer@example.com', 'password');
        $query = $this->prepareAddCouponRequestQuery($maskedQuoteId, $couponCode);
        $response = $this->graphQlQuery($query, [], '', $queryHeaders);

        self::assertArrayHasKey('applyCouponToCart', $response);
        self::assertEquals($couponCode, $response['applyCouponToCart']['cart']['applied_coupon']['code']);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @magentoApiDataFixture Magento/SalesRule/_files/coupon_code_with_wildcard.php
     * @expectedException \Exception
     * @expectedExceptionMessage A coupon is already applied to the cart. Please remove it to apply another
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
        $this->quote->setCustomerId(1);
        $this->quoteResource->save($this->quote);
        $queryHeaders = $this->prepareAuthorizationHeaders('customer@example.com', 'password');
        $query = $this->prepareAddCouponRequestQuery($maskedQuoteId, $couponCode);
        $response = $this->graphQlQuery($query, [], '', $queryHeaders);

        self::assertArrayHasKey("applyCouponToCart", $response);
        self::assertEquals($couponCode, $response['applyCouponToCart']['cart']['applied_coupon']['code']);

        $this->graphQlQuery($query, [], '', $queryHeaders);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     * @magentoApiDataFixture Magento/SalesRule/_files/coupon_code_with_wildcard.php
     * @expectedException \Exception
     * @expectedExceptionMessage Cart does not contain products.
     */
    public function testApplyCouponToCartWithoutItems()
    {
        $couponCode = '2?ds5!2d';

        $this->quoteResource->load($this->quote, 'test_order_1', 'reserved_order_id');
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$this->quote->getId());
        $this->quote->setCustomerId(1);
        $this->quoteResource->save($this->quote);
        $queryHeaders = $this->prepareAuthorizationHeaders('customer@example.com', 'password');
        $query = $this->prepareAddCouponRequestQuery($maskedQuoteId, $couponCode);

        $this->graphQlQuery($query, [], '', $queryHeaders);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @magentoApiDataFixture Magento/SalesRule/_files/coupon_code_with_wildcard.php
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @expectedException \Exception
     */
    public function testApplyCouponToGuestCart()
    {
        $couponCode = '2?ds5!2d';

        $this->quoteResource->load(
            $this->quote,
            'test_order_with_simple_product_without_address',
            'reserved_order_id'
        );
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$this->quote->getId());
        $queryHeaders = $this->prepareAuthorizationHeaders('customer@example.com', 'password');
        $query = $this->prepareAddCouponRequestQuery($maskedQuoteId, $couponCode);

        self::expectExceptionMessage('The current user cannot perform operations on cart "' . $maskedQuoteId . '"');
        $this->graphQlQuery($query, [], '', $queryHeaders);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/two_customers.php
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @magentoApiDataFixture Magento/SalesRule/_files/coupon_code_with_wildcard.php
     * @expectedException \Exception
     */
    public function testApplyCouponToAnotherCustomerCart()
    {
        $couponCode = '2?ds5!2d';

        $this->quoteResource->load(
            $this->quote,
            'test_order_with_simple_product_without_address',
            'reserved_order_id'
        );
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$this->quote->getId());
        $this->quote->setCustomerId(2);
        $this->quoteResource->save($this->quote);
        $queryHeaders = $this->prepareAuthorizationHeaders('customer@example.com', 'password');
        $query = $this->prepareAddCouponRequestQuery($maskedQuoteId, $couponCode);

        self::expectExceptionMessage('The current user cannot perform operations on cart "' . $maskedQuoteId . '"');
        $this->graphQlQuery($query, [], '', $queryHeaders);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @expectedException \Exception
     * @expectedExceptionMessage The coupon code isn't valid. Verify the code and try again.
     */
    public function testApplyNonExistentCouponToCart()
    {
        $couponCode = '1%q#f5';
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
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/SalesRule/_files/coupon_code_with_wildcard.php
     * @expectedException \Exception
     */
    public function testApplyCouponToNonExistentCart()
    {
        $couponCode = '2?ds5!2d';
        $maskedQuoteId = '1hk3y1842h1n';
        $this->quote->setCustomerId(1);
        $this->quoteResource->save($this->quote);
        $queryHeaders = $this->prepareAuthorizationHeaders('customer@example.com', 'password');
        $query = $this->prepareAddCouponRequestQuery($maskedQuoteId, $couponCode);

        self::expectExceptionMessage('Could not find a cart with ID "' . $maskedQuoteId . '"');
        $this->graphQlQuery($query, [], '', $queryHeaders);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @magentoApiDataFixture Magento/SalesRule/_files/coupon_code_with_wildcard.php
     * @expectedException \Exception
     * @expectedExceptionMessage The coupon code isn't valid. Verify the code and try again.
     */
    public function testApplyExpiredCoupon()
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
        $queryHeaders = $this->prepareAuthorizationHeaders('customer@example.com', 'password');
        $query = $this->prepareAddCouponRequestQuery($maskedQuoteId, $couponCode);

        $this->coupon->loadByCode($couponCode);
        $yesterday = new \DateTime();
        $yesterday->add(\DateInterval::createFromDateString('-1 day'));
        $this->coupon->setExpirationDate($yesterday->format('Y-m-d'));
        $this->couponResource->save($this->coupon);

        $this->graphQlQuery($query, [], '', $queryHeaders);
    }

    /**
     * Products in cart don't fit to the coupon
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @magentoApiDataFixture Magento/SalesRule/_files/coupon_code_with_wildcard.php
     * @expectedException \Exception
     * @expectedExceptionMessage The coupon code isn't valid. Verify the code and try again.
     */
    public function testApplyCouponWhichIsNotApplicable()
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
        $queryHeaders = $this->prepareAuthorizationHeaders('customer@example.com', 'password');
        $query = $this->prepareAddCouponRequestQuery($maskedQuoteId, $couponCode);
        $this->excludeProductPerCoupon($couponCode, 'simple');

        $this->graphQlQuery($query, [], '', $queryHeaders);
    }

    /**
     * @param string $input
     * @param string $message
     * @dataProvider dataProviderUpdateWithMissedRequiredParameters
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
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

        $this->quote->setCustomerId(1);
        $this->quoteResource->save($this->quote);
        $queryHeaders = $this->prepareAuthorizationHeaders('customer@example.com', 'password');

        $this->expectExceptionMessage($message);
        $this->graphQlQuery($query, [], '', $queryHeaders);
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
     * @param string $couponCode
     * @param string $sku
     * @throws \Exception
     */
    private function excludeProductPerCoupon(string $couponCode, string $sku)
    {
        $this->coupon->loadByCode($couponCode);
        $ruleId = $this->coupon->getRuleId();
        $salesRule = $this->salesRule->load($ruleId);
        $salesRule->getConditions()->loadArray([
            'type' => \Magento\SalesRule\Model\Rule\Condition\Combine::class,
            'attribute' => null,
            'operator' => null,
            'value' => '1',
            'is_value_processed' => null,
            'aggregator' => 'all',
            'conditions' =>
                [
                    [
                        'type' => \Magento\SalesRule\Model\Rule\Condition\Product\Found::class,
                        'attribute' => null,
                        'operator' => null,
                        'value' => '1',
                        'is_value_processed' => null,
                        'aggregator' => 'all',
                        'conditions' =>
                            [
                                [
                                    'type' => \Magento\SalesRule\Model\Rule\Condition\Product::class,
                                    'attribute' => 'sku',
                                    'operator' => '!=',
                                    'value' => $sku,
                                    'is_value_processed' => false,
                                ],
                            ],
                    ],
                ],
        ]);
        $this->salesRule->save();
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
}
