<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Test\Fixture\Attribute as AttributeFixture;
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
 * Get add to cart through GraphQl query and variables
 */
class AddProductsToCartWithVariablesTest extends GraphQlAbstract
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
        DataFixture(AttributeFixture::class, ['is_visible_on_front' => true], as: 'attr'),
        DataFixture(ProductFixture::class, [
            'attribute_set_id' => 4,
            '$attr.attribute_code$' => 'default_value'
        ], as: 'product'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
    ]
    public function testAddProductsToEmptyCartWithVariables(): void
    {
        $attribute = $this->fixtures->get('attr');
        $product = $this->fixtures->get('product');

        $this->cleanCache();

        $cart = $this->fixtures->get('cart');
        $maskedQuoteId = $this->quoteIdToMaskedQuoteIdInterface->execute((int) $cart->getId());
        $query = $this->getAddToCartMutation($attribute->getAttributeCode());
        $variables = $this->getAddToCartVariables($maskedQuoteId, 1, $product->getSku());
        $response = $this->graphQlMutation($query, $variables);
        $result = $response['addProductsToCart'];

        self::assertEmpty($result['user_errors']);
        self::assertCount(1, $result['cart']['items']);

        $cartItem = $result['cart']['items'][0];
        self::assertEquals($product->getSku(), $cartItem['product']['sku']);
        self::assertEquals('default_value', $cartItem['product'][$attribute->getAttributeCode()]);
        self::assertEquals(1, $cartItem['quantity']);
        self::assertEquals($product->getFinalPrice(), $cartItem['prices']['price']['value']);
    }

    /**
     * Returns GraphQl mutation for adding item to cart
     *
     * @param string $customAttributeCode
     * @return string
     */
    private function getAddToCartMutation(string $customAttributeCode): string
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
          {$customAttributeCode}
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
