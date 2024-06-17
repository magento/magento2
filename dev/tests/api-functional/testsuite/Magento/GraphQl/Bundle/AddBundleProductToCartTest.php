<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Bundle;

use Magento\Bundle\Test\Fixture\Link as BundleSelectionFixture;
use Magento\Bundle\Test\Fixture\Option as BundleOptionFixture;
use Magento\Bundle\Test\Fixture\Product as BundleProductFixture;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;

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
     * @var DataFixtureStorage
     */
    private $fixtures;

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
        $this->fixtures = $objectManager->get(DataFixtureStorageManager::class)->getStorage();
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
     * @magentoApiDataFixture Magento/Bundle/_files/quote_with_bundle_and_options.php
     * @dataProvider dataProviderTestUpdateBundleItemQuantity
     */
    public function testUpdateBundleItemQuantity(int $quantity)
    {
        $this->quoteResource->load(
            $this->quote,
            'test_cart_with_bundle_and_options',
            'reserved_order_id'
        );

        $item = current($this->quote->getAllVisibleItems());

        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$this->quote->getId());
        $mutation = <<<QUERY
mutation {
  updateCartItems(
    input: {
      cart_id: "{$maskedQuoteId}"
      cart_items: {
        cart_item_id: {$item->getId()}
        quantity: {$quantity}
      }
    }
  ) {
    cart {
      items {
        id
        quantity
        product {
          sku
        }
      }
    }
  }
}
QUERY;

        $response = $this->graphQlMutation($mutation);

        $this->assertArrayHasKey('updateCartItems', $response);
        $this->assertArrayHasKey('cart', $response['updateCartItems']);
        $cart = $response['updateCartItems']['cart'];
        if ($quantity === 0) {
            $this->assertCount(0, $cart['items']);
            return;
        }

        $bundleItem = current($cart['items']);
        $this->assertEquals($quantity, $bundleItem['quantity']);
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
     */
    public function testAddBundleToCartWithoutOptions()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Please select all required options');

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

    /**
     * @magentoApiDataFixture Magento/Bundle/_files/product_with_multiple_options_radio_select.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     */
    public function testAddBundleToCartWithRadioAndSelectErr()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Option type (select, radio) should have only one element.');

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
        $options = $typeInstance->getOptionsCollection($product);

        $selectionIds = [];
        $optionIds = [];
        foreach ($options as $option) {
            $type = $option->getType();

            /** @var \Magento\Catalog\Model\Product $selection */
            $selections = $typeInstance->getSelectionsCollection([$option->getId()], $product);
            $optionIds[$type] = $option->getId();

            foreach ($selections->getItems() as $selection) {
                $selectionIds[$type][] = $selection->getSelectionId();
            }
        }

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
            id:{$optionIds['select']}
            quantity:1
            value:[
              "{$selectionIds['select'][0]}"
              "{$selectionIds['select'][1]}"
            ]
          },
           {
            id:{$optionIds['radio']}
            quantity:1
            value:[
              "{$selectionIds['radio'][0]}"
              "{$selectionIds['radio'][1]}"
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

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     */
    #[
        DataFixture(ProductFixture::class, ['sku' => 'simple-1', 'price' => 10], 'p1'),
        DataFixture(ProductFixture::class, ['sku' => 'simple2', 'price' => 20], 'p2'),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$p1.sku$', 'price' => 10, 'price_type' => 0], 'link1'),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$p2.sku$', 'price' => 25, 'price_type' => 1], 'link2'),
        DataFixture(BundleOptionFixture::class, ['title' => 'Checkbox Options', 'type' => 'checkbox',
            'required' => 1,'product_links' => ['$link1$', '$link2$']], 'opt1'),
        DataFixture(BundleOptionFixture::class, ['title' => 'Multiselect Options', 'type' => 'multi',
            'required' => 1,'product_links' => ['$link1$', '$link2$']], 'opt2'),
        DataFixture(
            BundleProductFixture::class,
            ['sku' => 'bundle-product-multiselect-checkbox-options','price' => 50,'price_type' => 1,
                '_options' => ['$opt1$', '$opt2$']],
            'bundle-product-multiselect-checkbox-options'
        ),
    ]
    public function testAddBundleToCartWithEmptyMultiselectOptionValue()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Please select all required options.');

        $this->quoteResource->load(
            $this->quote,
            'test_order_1',
            'reserved_order_id'
        );
        $sku = 'bundle-product-multiselect-checkbox-options';

        $product = $this->fixtures->get($sku);

        /** @var $typeInstance \Magento\Bundle\Model\Product\Type */
        $typeInstance = $product->getTypeInstance();
        $typeInstance->setStoreFilter($product->getStoreId(), $product);
        /** @var $option \Magento\Bundle\Model\Option */
        $options = $typeInstance->getOptionsCollection($product);

        $selectionIds = [];
        $optionIds = [];
        foreach ($options as $option) {
            $type = $option->getType();

            /** @var \Magento\Catalog\Model\Product $selection */
            $selections = $typeInstance->getSelectionsCollection([$option->getId()], $product);
            $optionIds[$type] = $option->getId();

            foreach ($selections->getItems() as $selection) {
                $selectionIds[$type][] = $selection->getSelectionId();
            }
        }

        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$this->quote->getId());

        $query = <<<QUERY
mutation {
  addBundleProductsToCart(input:{
    cart_id: "{$maskedQuoteId}"
    cart_items: [
      {
        data: {
          sku: "{$sku}"
          quantity: 1
        }
        bundle_options: [
          {
            id: {$optionIds['multi']}
            quantity: 1
            value: [
              ""
            ]
          },
          {
            id: {$optionIds['checkbox']}
            quantity: 1
            value: [
               "{$selectionIds['checkbox'][0]}"
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
