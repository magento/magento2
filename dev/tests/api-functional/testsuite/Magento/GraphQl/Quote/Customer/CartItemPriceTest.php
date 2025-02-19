<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Customer;

use Exception;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\CustomerCart as CustomerCartFixture;
use Magento\Quote\Test\Fixture\QuoteIdMask;
use Magento\SalesRule\Model\Rule as SalesRule;
use Magento\SalesRule\Test\Fixture\AddressCondition as AddressConditionFixture;
use Magento\SalesRule\Test\Fixture\Rule as SalesRuleFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test getting CartItemPrices schema (original_item_price & original_row_total) fields
 */
class CartItemPriceTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    protected function setUp(): void
    {
        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
    }

    /**
     * Test original_item_price & original_row_total fields
     *
     * @return void
     * @throws AuthenticationException
     * @throws LocalizedException
     * @throws Exception
     */
    #[
        DataFixture(
            AddressConditionFixture::class,
            ['attribute' => 'base_subtotal', 'operator' => '>=', 'value' => 0],
            'condition'
        ),
        DataFixture(
            SalesRuleFixture::class,
            [
                'store_labels' => [1 => 'Coupon1'],
                'simple_action' => SalesRule::BY_PERCENT_ACTION,
                'uses_per_customer' => 1,
                'discount_amount' => 10,
                'stop_rules_processing' => false,
                'conditions' => ['$condition$']
            ]
        ),
        DataFixture(
            SalesRuleFixture::class,
            [
                'store_labels' => [1 => 'Coupon2'],
                'simple_action' => SalesRule::BY_FIXED_ACTION,
                'uses_per_customer' => 1,
                'discount_amount' => 0.5,
                'stop_rules_processing' => false,
                'conditions' => ['$condition$']
            ]
        ),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(CustomerFixture::class, ['email' => 'customer@example.com'], as: 'customer'),
        DataFixture(CustomerCartFixture::class, ['customer_id' => '$customer.id$'], as: 'cart'),
        DataFixture(
            AddProductToCartFixture::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 2]
        ),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
    ]
    public function testGetCartItemPricesWithDiscount()
    {
        $maskedQuoteId = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();
        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlQuery($query, [], '', $this->getHeaderMap());

        self::assertEquals(
            [
                'cart' => [
                    'items' => [
                        0 => [
                            'prices' => [
                                'original_item_price' => [
                                    'value' => 10,
                                    'currency' => 'USD'
                                ],
                                'original_row_total' => [
                                    'value' => 20,
                                    'currency' => 'USD'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            $response
        );
    }

    /**
     * Generates GraphQl query for retrieving cart items prices [original_item_price & original_row_total]
     *
     * @param string $maskedQuoteId
     * @return string
     */
    private function getQuery(string $maskedQuoteId): string
    {
        return <<<QUERY
{
  cart(cart_id: "$maskedQuoteId") {
    items {
      prices {
        original_item_price {
            value
            currency
        }
        original_row_total {
          value
          currency
        }
      }
    }
  }
}
QUERY;
    }

    /**
     * Get Authentication header
     *
     * @return array
     * @throws AuthenticationException
     */
    private function getHeaderMap(): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken('customer@example.com', 'password');
        return ['Authorization' => 'Bearer ' . $customerToken];
    }
}
