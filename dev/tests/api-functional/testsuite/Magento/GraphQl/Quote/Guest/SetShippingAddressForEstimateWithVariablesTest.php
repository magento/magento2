<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Guest;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Get estimate for cart with GraphQl query and variables
 */
class SetShippingAddressForEstimateWithVariablesTest extends GraphQlAbstract
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    private $quoteIdToMaskedQuoteIdInterface;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);
        $this->quoteIdToMaskedQuoteIdInterface = $objectManager->get(QuoteIdToMaskedQuoteIdInterface::class);
        $this->fixtures = $objectManager->get(DataFixtureStorageManager::class)->getStorage();
    }

    /**
     * @throws NoSuchEntityException
     * @throws \Exception
     */
    #[
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
    ]
    public function testAddProductsToEmptyCartWithVariables(): void
    {
        $product = $this->fixtures->get('product');
        $cart = $this->fixtures->get('cart');

        $maskedQuoteId = $this->quoteIdToMaskedQuoteIdInterface->execute((int) $cart->getId());
        $query = $this->getAddToCartMutation();
        $variables = $this->getAddToCartVariables($maskedQuoteId, 1, $product->getSku());
        $response = $this->graphQlMutation($query, $variables);
        $result = $response['addProductsToCart'];

        self::assertEmpty($result['user_errors']);
        self::assertCount(1, $result['cart']['items']);

        $query = $this->getSetShippingAddressForEstimateMutation();
        $variables = $this->getSetShippingAddressForEstimateVariables($maskedQuoteId);
        $response = $this->graphQlMutation($query, $variables);
        $result = $response['setShippingAddressesOnCart'];

        $cartItem = $result['cart']['items'][0];
        self::assertEquals($product->getSku(), $cartItem['product']['sku']);
        self::assertEquals(1, $cartItem['quantity']);
        self::assertEquals("SetShippingAddressesOnCartOutput", $result['__typename']);
    }

    /**
     * Returns GraphQl mutation for adding item to cart
     *
     * @return string
     */
    private function getSetShippingAddressForEstimateMutation(): string
    {
        return <<<MUTATION
mutation SetShippingAddressForEstimate(\$cartId: String!, \$address: CartAddressInput!) {
  setShippingAddressesOnCart(
    input: {cart_id: \$cartId, shipping_addresses: [{address: \$address}]}
  ) {
    cart {
      id
      __typename
      items {
        id
        product {
          name
          sku
        }
        quantity
      }
    }
    __typename
  }
}
MUTATION;
    }

    private function getSetShippingAddressForEstimateVariables(
        string $maskedQuoteId
    ): array {
        return
            [
                'cartId' => $maskedQuoteId,
                'address' =>
                [
                    'city' => 'New York',
                    'firstname' => 'Test',
                    'lastname' => 'Test',
                    'street' => ['line 1', 'line 2'],
                    'telephone' => '1234567890',
                    'postcode' => '11371',
                    'region' => 'NY',
                    'country_code' => 'US'
                ]
            ];
    }

    /**
     * Returns GraphQl mutation for adding item to cart
     *
     * @return string
     */
    private function getAddToCartMutation(): string
    {
        return <<<MUTATION
mutation (\$cartId: String!, \$products: [CartItemInput!]!) {
  addProductsToCart(cartId: \$cartId, cartItems: \$products) {
    cart {
      id
      items {
        uid
        quantity
        product {
          sku
          name
          thumbnail {
            url
            __typename
          }
          __typename
        }
        prices {
          price {
            value
            currency
          }
        }
      }
    }
    user_errors {
      code
      message
    }
  }
}
MUTATION;
    }

    private function getAddToCartVariables(
        string $maskedQuoteId,
        int $qty,
        string $sku
    ): array {
        return
            [
                'cartId' => $maskedQuoteId,
                'products' => [
                    [
                        'sku' => $sku,
                        'parent_sku' => $sku,
                        'quantity' => $qty
                    ]
                ]
            ];
    }
}
