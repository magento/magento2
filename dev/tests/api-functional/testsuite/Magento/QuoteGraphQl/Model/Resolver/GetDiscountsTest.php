<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\Quote\Test\Fixture\QuoteIdMask;
use Magento\SalesRule\Test\Fixture\Rule as SalesRuleFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class GetDiscountsTest extends GraphQlAbstract
{
    /** @var CustomerTokenServiceInterface */
    private CustomerTokenServiceInterface $customerTokenService;

    /** @inheritdoc */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
        parent::setUp();
    }

    #[
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(Customer::class, ['email' => 'customer@example.com'], as: 'customer'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], as: 'quoteIdMask'),
        DataFixture(SalesRuleFixture::class, ['discount_amount' => 10, 'simple_action' => 'by_percent'], as: 'rule')
    ]
    /**
     * Test retrieval of discount values for a cart
     * @throws LocalizedException
     */
    public function testGetDiscounts()
    {
        $maskedQuoteId = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();
        $query = $this->getCartDiscountsQuery($maskedQuoteId);
        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getHeaderMap()
        );
        $cartData = $response['cart'];
        $discounts = $cartData['prices']['discounts'] ?? [];
        $this->assertNotEmpty($discounts);
        $this->assertCount(1, $discounts);
        $discount = $discounts[0];
        $this->assertArrayHasKey('label', $discount);
        $this->assertArrayHasKey('amount', $discount);
        $this->assertArrayHasKey('value', $discount['amount']);
        $this->assertArrayHasKey('currency', $discount['amount']);
        $this->assertEquals('Discount', $discount['label']);
        $this->assertGreaterThan(0, $discount['amount']['value']);
        $this->assertEquals('USD', $discount['amount']['currency']);
    }

    #[
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(Customer::class, ['email' => 'customer@example.com'], as: 'customer'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], as: 'quoteIdMask')
    ]
    /**
     * Test when no discounts are applied
     */
    public function testGetDiscountsNoDiscounts()
    {
        $maskedQuoteId = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();
        $query = $this->getCartDiscountsQuery($maskedQuoteId);
        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getHeaderMap()
        );
        $cartData = $response['cart'];
        $discounts = $cartData['prices']['discounts'] ?? [];
        $this->assertEmpty($discounts);
    }

    /**
     * Get cart discounts query
     *
     * @param string $maskedQuoteId
     * @return string
     */
    private function getCartDiscountsQuery(string $maskedQuoteId): string
    {
        return <<<QUERY
        query {
            cart(cart_id: "{$maskedQuoteId}") {
                prices {
                    discounts {
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

    /**
     * Get bearer authorization header
     *
     * @param string $username
     * @param string $password
     * @return array
     * @throws AuthenticationException
     */
    private function getHeaderMap(string $username = 'customer@example.com', string $password = 'password'): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($username, $password);
        return ['Authorization' => 'Bearer ' . $customerToken];
    }
}
