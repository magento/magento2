<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\ConfigurableProduct\Test\Fixture\Attribute as AttributeFixture;
use Magento\ConfigurableProduct\Test\Fixture\Product as ConfigurableProductFixture;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\Quote\Test\Fixture\QuoteIdMask;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class AddConfigurableProductToCartTest extends GraphQlAbstract
{
    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    protected function setUp(): void
    {
        $this->fixtures = DataFixtureStorageManager::getStorage();
        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
    }

    #[
        DataFixture(AttributeFixture::class, [
            'frontend_input' => 'select',
            'options' => ['40', '42'],
            'is_configurable' => true,
            'is_global' => true
        ], as: 'attribute'),
        DataFixture(
            ProductFixture::class,
            [
                'price' => 100,
                'custom_attributes' => [
                    ['attribute_code' => '$attribute.attribute_code$', 'value' => '40']
                ]
            ],
            as: 'product1'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'price' => 100,
                'custom_attributes' => [
                    ['attribute_code' => '$attribute.attribute_code$', 'value' => '42']
                ]
            ],
            as: 'product2'
        ),
        DataFixture(
            ConfigurableProductFixture::class,
            [
                '_options' => ['$attribute$'],
                '_links' => ['$product1$', '$product2$'],
                'custom_attributes' => [
                    ['attribute_code' => '$attribute.attribute_code$', 'value' => '40']
                ]
            ],
            'configurable_product'
        ),
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'customerCart'),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$customerCart.id$'], 'quoteIdMask'),
    ]
    public function testAddToCartForConfigurableProductWithoutOptions(): void
    {
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();

        $this->assertEquals(
            [
                'addProductsToCart' => [
                    'cart' => [
                        'id' => $maskedQuoteId,
                        'itemsV2' => [
                            'items' => []
                        ]
                    ],
                    'user_errors' => [
                        [
                            'code' => 'REQUIRED_PARAMETER_MISSING',
                            'message' => 'You need to choose options for your item.'
                        ]
                    ]
                ]
            ],
            $this->graphQlMutation(
                $this->getAddToCartMutation(
                    $maskedQuoteId,
                    $this->fixtures->get('configurable_product')->getSku(),
                    2
                ),
                [],
                "",
                $this->getCustomerAuthHeaders($this->fixtures->get('customer')->getEmail())
            )
        );
    }

    /**
     * Get addToCart mutation for a configurable product without specifying options
     *
     * @param string $cartId
     * @param string $sku
     * @param int $quantity
     * @return string
     */
    private function getAddToCartMutation(string $cartId, string $sku, int $quantity): string
    {
        return <<<MUTATION
            mutation{
               addProductsToCart(cartId: "{$cartId}",
                  cartItems:[
                    {
                      sku:"{$sku}"
                      quantity:{$quantity}
                    }
                  ]
            )
              {
                cart {
                  id
                  itemsV2 {
                    items {
                      quantity
                      product {
                        sku
                      }
                    }
                  }
                }
                user_errors{
                    code
                    message
                }
              }
            }
        MUTATION;
    }

    /**
     * Returns the header with customer token for GQL Mutation
     *
     * @param string $email
     * @return array
     * @throws AuthenticationException
     */
    private function getCustomerAuthHeaders(string $email): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($email, 'password');
        return ['Authorization' => 'Bearer ' . $customerToken];
    }
}
