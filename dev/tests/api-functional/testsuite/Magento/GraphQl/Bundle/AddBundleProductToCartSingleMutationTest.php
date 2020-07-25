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
    userInputErrors {
        message
    }
  }
}
QUERY;

        $response = $this->graphQlMutation($query);

        self::assertEquals(
            "Please select all required options.",
            $response['addProductsToCart']['userInputErrors'][0]['message']
        );
    }
}
