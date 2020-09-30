<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Bundle;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test adding bundled products to cart using the unified mutation mutation
 */
class AddBundleProductToCartSingleMutationTest extends GraphQlAbstract
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
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->quoteResource = $objectManager->get(QuoteResource::class);
        $this->quote = $objectManager->create(Quote::class);
        $this->quoteIdToMaskedId = $objectManager->get(QuoteIdToMaskedQuoteIdInterface::class);
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);
    }

    /**
     * @magentoApiDataFixture Magento/Bundle/_files/product_1.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     */
    public function testAddBundleProductToCart()
    {
        $sku = 'bundle-product';

        $this->quoteResource->load(
            $this->quote,
            'test_order_1',
            'reserved_order_id'
        );

        $product = $this->productRepository->get($sku);

        /** @var $typeInstance \Magento\Bundle\Model\Product\Type */
        $typeInstance = $product->getTypeInstance();
        $typeInstance->setStoreFilter($product->getStoreId(), $product);
        /** @var $option \Magento\Bundle\Model\Option */
        $option = $typeInstance->getOptionsCollection($product)->getFirstItem();
        /** @var \Magento\Catalog\Model\Product $selection */
        $selection = $typeInstance->getSelectionsCollection([$option->getId()], $product)->getFirstItem();
        $optionId = $option->getId();
        $selectionId = $selection->getSelectionId();

        $bundleOptionIdV2 = $this->generateBundleOptionIdV2((int) $optionId, (int) $selectionId, 1);
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$this->quote->getId());

        $query = <<<QUERY
mutation {
    addProductsToCart(
        cartId: "{$maskedQuoteId}",
        cartItems: [
            {
                sku: "{$sku}"
                quantity: 1
                selected_options: [
                    "{$bundleOptionIdV2}"
                ]
            }
        ]
    ) {
        cart {
            items {
                id
                quantity
                product {
                sku
            }
            ... on BundleCartItem {
                bundle_options {
                    id
                    label
                    type
                    values {
                        id
                        label
                        price
                        quantity
                    }
                }
            }
          }
        }
    }
}
QUERY;

        $response = $this->graphQlMutation($query);

        self::assertArrayHasKey('addProductsToCart', $response);
        self::assertArrayHasKey('cart', $response['addProductsToCart']);
        $cart = $response['addProductsToCart']['cart'];
        $bundleItem = current($cart['items']);
        self::assertEquals($sku, $bundleItem['product']['sku']);
        $bundleItemOption = current($bundleItem['bundle_options']);
        self::assertEquals($optionId, $bundleItemOption['id']);
        self::assertEquals($option->getTitle(), $bundleItemOption['label']);
        self::assertEquals($option->getType(), $bundleItemOption['type']);
        $value = current($bundleItemOption['values']);
        self::assertEquals($selection->getSelectionId(), $value['id']);
        self::assertEquals((float) $selection->getSelectionPriceValue(), $value['price']);
        self::assertEquals(1, $value['quantity']);
    }

    /**
     * @param int $optionId
     * @param int $selectionId
     * @param int $quantity
     * @return string
     */
    private function generateBundleOptionIdV2(int $optionId, int $selectionId, int $quantity): string
    {
        return base64_encode("bundle/$optionId/$selectionId/$quantity");
    }

    public function dataProviderTestUpdateBundleItemQuantity(): array
    {
        return [
            [2],
            [0],
        ];
    }

    /**
     * @magentoApiDataFixture Magento/Bundle/_files/product_1.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     * @expectedExceptionMessage Please select all required options
     */
    public function testAddBundleToCartWithWrongBundleOptions()
    {
        $this->quoteResource->load(
            $this->quote,
            'test_order_1',
            'reserved_order_id'
        );

        $bundleOptionIdV2 = $this->generateBundleOptionIdV2((int) 1, (int) 1, 1);
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$this->quote->getId());

        $query = <<<QUERY
mutation {
      addProductsToCart(
            cartId: "{$maskedQuoteId}",
            cartItems: [
                {
                    sku: "bundle-product"
                    quantity: 1
                    selected_options: [
                        "{$bundleOptionIdV2}"
                    ]
                }
            ]
       ) {
    cart {
        items {
            id
            quantity
            product {
                sku
            }
            ... on BundleCartItem {
                bundle_options {
                    id
                    label
                    type
                    values {
                        id
                        label
                        price
                        quantity
                    }
                }
            }
        }
    }
    user_errors {
        message
    }
  }
}
QUERY;

        $response = $this->graphQlMutation($query);

        self::assertEquals(
            "Please select all required options.",
            $response['addProductsToCart']['user_errors'][0]['message']
        );
    }

    /**
     * @magentoApiDataFixture Magento/Bundle/_files/product_with_multiple_options_and_custom_quantity.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     */
    public function testAddBundleItemWithCustomOptionQuantity()
    {

        $this->quoteResource->load(
            $this->quote,
            'test_order_1',
            'reserved_order_id'
        );
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$this->quote->getId());
        $response = $this->graphQlQuery($this->getProductQuery("bundle-product"));
        $bundleItem = $response['products']['items'][0];
        $sku = $bundleItem['sku'];
        $bundleOptions = $bundleItem['items'];

        $uId0 = $bundleOptions[0]['options'][0]['uid'];
        $uId1 = $bundleOptions[1]['options'][0]['uid'];
        $response = $this->graphQlMutation(
            $this->getMutationsQuery($maskedQuoteId, $uId0, $uId1, $sku)
        );
        $bundleOptions = $response['addProductsToCart']['cart']['items'][0]['bundle_options'];
        $this->assertEquals(5, $bundleOptions[0]['values'][0]['quantity']);
        $this->assertEquals(1, $bundleOptions[1]['values'][0]['quantity']);
    }

    /**
     * Returns GraphQL query for retrieving a product with customizable options
     *
     * @param string $sku
     * @return string
     */
    private function getProductQuery(string $sku): string
    {
        return <<<QUERY
{
  products(search: "{$sku}") {
    items {
      sku
       ... on BundleProduct {
              items {
                sku
                option_id
                required
                type
                title
                options {
                  uid
                  label
                  product {
                    sku
                  }
                  can_change_quantity
                  id
                  price

                  quantity
                }
              }
       }
    }
  }
}
QUERY;
    }

    private function getMutationsQuery(
        string $maskedQuoteId,
        string $optionUid0,
        string $optionUid1,
        string $sku
    ): string {
        return <<<QUERY
mutation {
      addProductsToCart(
            cartId: "{$maskedQuoteId}",
            cartItems: [
                {
                    sku: "{$sku}"
                    quantity: 2
                    selected_options: [
                        "{$optionUid1}", "{$optionUid0}"
                    ],
                    entered_options: [{
                        uid: "{$optionUid0}"
                        value: "5"
                     },
                     {
                        uid: "{$optionUid1}"
                        value: "5"
                     }]
                }
            ]
       ) {
    cart {
        items {
            id
            quantity
            product {
                sku
            }
            ... on BundleCartItem {
                bundle_options {
                    id
                    label
                    type
                    values {
                        id
                        label
                        price
                        quantity
                    }
                }
            }
        }
    }
    user_errors {
        message
    }
  }
}
QUERY;
    }
}
