<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Bundle;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\DataObject;
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
                uid
                quantity
                product {
                sku
            }
            ... on BundleCartItem {
                bundle_options {
                    id
                    uid
                    label
                    type
                    values {
                        id
                        uid
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

    public static function dataProviderTestUpdateBundleItemQuantity(): array
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
            uid
            quantity
            product {
                sku
            }
            ... on BundleCartItem {
                bundle_options {
                    id
                    uid
                    label
                    type
                    values {
                        id
                        uid
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
     * @dataProvider bundleItemOptionsDataProvider
     * @return void
     */
    public function testAddBundleItemWithCustomOptionQuantity(
        string $optionQty0,
        string $optionQty1,
        string $expectedOptionQty0,
        string $expectedOptionQty1
    ): void {
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
            $this->getMutationsQuery($maskedQuoteId, $uId0, $uId1, $sku, $optionQty0, $optionQty1)
        );
        $bundleOptions = $response['addProductsToCart']['cart']['items'][0]['bundle_options'];
        $this->assertEquals($expectedOptionQty0, $bundleOptions[0]['values'][0]['quantity']);
        $this->assertEquals($expectedOptionQty1, $bundleOptions[1]['values'][0]['quantity']);
    }

    /**
     * @magentoApiDataFixture Magento/SalesRule/_files/coupon_code_with_wildcard.php
     * @magentoApiDataFixture Magento/Bundle/_files/bundle_product_with_dynamic_price.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     * @return void
     */
    public function testAddBundleProductToCartWithDiscount(): void
    {
        $this->quoteResource->load(
            $this->quote,
            'test_order_1',
            'reserved_order_id'
        );
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$this->quote->getId());
        $response = $this->graphQlQuery($this->getProductQuery('bundle_product_with_dynamic_price'));
        $bundleItem = $response['products']['items'][0];
        $sku = $bundleItem['sku'];
        $bundleOptions = $bundleItem['items'];

        $uId0 = $bundleOptions[0]['options'][0]['uid'];
        $uId1 = $bundleOptions[1]['options'][0]['uid'];
        $response = $this->graphQlMutation(
            $this->getMutationsQuery($maskedQuoteId, $uId0, $uId1, $sku, '1', '1')
        );
        $responseDataObject = new DataObject($response);
        $cartItems = $responseDataObject->getData('addProductsToCart/cart/items');
        self::assertIsArray($cartItems);
        self::assertCount(1, $cartItems);
        self::assertEquals($sku, $cartItems[0]['product']['sku']);

        $couponCode = '2?ds5!2d';
        $query = $this->getCouponMutationsQuery($maskedQuoteId, $couponCode);
        $response = $this->graphQlMutation($query);
        $responseDataObject = new DataObject($response);
        $appliedCouponCode = $responseDataObject->getData('applyCouponToCart/cart/applied_coupon/code');
        self::assertEquals($couponCode, $appliedCouponCode);

        $query = $this->getCartQueryWithDiscounts($maskedQuoteId);
        $response = $this->graphQlMutation($query);
        $responseDataObject = new DataObject($response);
        $discounts = $responseDataObject->getData('cart/prices/discounts');
        self::assertIsArray($discounts);
        self::assertCount(1, $discounts);
        self::assertEquals(5, $discounts[0]['amount']['value']);
        $cartItems = $responseDataObject->getData('cart/items');
        self::assertIsArray($cartItems);
        self::assertCount(1, $cartItems);
        self::assertEquals($sku, $cartItems[0]['product']['sku']);
        self::assertIsArray($cartItems[0]['prices']['discounts']);
        self::assertEquals(5, $cartItems[0]['prices']['discounts'][0]['amount']['value']);
    }

    /**
     * Data provider for testAddBundleItemWithCustomOptionQuantity
     *
     * @return array
     */
    public static function bundleItemOptionsDataProvider(): array
    {
        return [
            [
                'optionQty0' => '10',
                'optionQty1' => '1',
                'expectedOptionQty0' => '10',
                'expectedOptionQty1' => '1',
            ],
            [
                'optionQty0' => '5',
                'optionQty1' => '5',
                'expectedOptionQty0' => '5',
                'expectedOptionQty1' => '1',
            ],
        ];
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
                uid
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
                  uid
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

    /**
     * @param string $maskedQuoteId
     * @param string $optionUid0
     * @param string $optionUid1
     * @param string $sku
     * @param string $optionQty0
     * @param string $optionQty1
     * @return string
     */
    private function getMutationsQuery(
        string $maskedQuoteId,
        string $optionUid0,
        string $optionUid1,
        string $sku,
        string $optionQty0,
        string $optionQty1
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
                        "{$optionUid0}", "{$optionUid1}"
                    ],
                    entered_options: [{
                        uid: "{$optionUid0}"
                        value: "{$optionQty0}"
                     },
                     {
                        uid: "{$optionUid1}"
                        value: "{$optionQty1}"
                     }]
                }
            ]
       ) {
    cart {
        items {
            id
            uid
            quantity
            product {
                sku
            }
            ... on BundleCartItem {
                bundle_options {
                    id
                    uid
                    label
                    type
                    values {
                        id
                        uid
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

    /**
     * @param string $maskedQuoteId
     * @param string $couponCode
     * @return string
     */
    private function getCouponMutationsQuery(string $maskedQuoteId, string $couponCode): string
    {
        return <<<QUERY
mutation {
  applyCouponToCart(input: {cart_id: "$maskedQuoteId", coupon_code: "$couponCode"}) {
    cart {
    id
      applied_coupon {
        code
      }
    }
  }
}
QUERY;
    }

    /**
     * @param string $maskedQuoteId
     * @return string
     */
    private function getCartQueryWithDiscounts(string $maskedQuoteId): string
    {
        return <<<QUERY
{
  cart(cart_id: "$maskedQuoteId") {
    email
    items {
      uid
      prices {
        discounts {
          amount {
            value
          }
        }
      }
      product {
        sku
      }
    }
    applied_coupons {
      code
    }
    prices {
      discounts {
        amount {
          value
        }
        label
      }
      grand_total {
        value
      }
    }
  }
}
QUERY;
    }
}
