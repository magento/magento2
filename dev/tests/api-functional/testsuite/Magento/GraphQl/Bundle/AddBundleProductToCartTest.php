<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Bundle;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;

/**
 * Test adding bundled products to cart
 */
class AddBundleProductToCartTest extends GraphQlAbstract
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
    protected function setUp()
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

        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$this->quote->getId());

        $query = <<<QUERY
mutation {  
  addBundleProductsToCart(input:{
    cart_id:"{$maskedQuoteId}"
    cart_items:[
      {
        data:{
          sku:"{$sku}"
          quantity:1
        }
        bundle_options:[
          {
            id:{$optionId}
            quantity:1
            value:[
              "{$selectionId}"
            ]
          }
        ]
      }
    ]
  }) {
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

        $this->assertArrayHasKey('addBundleProductsToCart', $response);
        $this->assertArrayHasKey('cart', $response['addBundleProductsToCart']);
        $cart = $response['addBundleProductsToCart']['cart'];
        $bundleItem = current($cart['items']);
        $this->assertEquals($sku, $bundleItem['product']['sku']);
        $bundleItemOption = current($bundleItem['bundle_options']);
        $this->assertEquals($optionId, $bundleItemOption['id']);
        $this->assertEquals($option->getTitle(), $bundleItemOption['label']);
        $this->assertEquals($option->getType(), $bundleItemOption['type']);
        $value = current($bundleItemOption['values']);
        $this->assertEquals($selection->getSelectionId(), $value['id']);
        $this->assertEquals((float) $selection->getSelectionPriceValue(), $value['price']);
        $this->assertEquals(1, $value['quantity']);
    }

    /**
     * @magentoApiDataFixture Magento/Bundle/_files/product_1.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     * @expectedException \Exception
     * @expectedExceptionMessage Please select all required options
     */
    public function testAddBundleToCartWithoutOptions()
    {
        $this->quoteResource->load(
            $this->quote,
            'test_order_1',
            'reserved_order_id'
        );

        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$this->quote->getId());

        $query = <<<QUERY
mutation {  
  addBundleProductsToCart(input:{
    cart_id:"{$maskedQuoteId}"
    cart_items:[
      {
        data:{
          sku:"bundle-product"
          quantity:1
        }
        bundle_options:[
          {
            id:555
            quantity:1
            value:[
              "555"
            ]
          }
        ]
      }
    ]
  }) {
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

        $this->graphQlMutation($query);
    }
}
