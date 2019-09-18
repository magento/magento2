<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Magento\Catalog\Api\ProductCustomOptionRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException as NoSuchEntityException;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Edit cart customizable options test
 */
class EditQuoteItemWithCustomOptionsTest extends GraphQlAbstract
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var GetMaskedQuoteIdByReservedOrderId
     */
    private $getMaskedQuoteIdByReservedOrderId;

    /**
     * @var ProductCustomOptionRepositoryInterface
     */
    private $productCustomOptionsRepository;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var QuoteResource
     */
    private $quoteResource;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
        $this->productCustomOptionsRepository = $objectManager->get(ProductCustomOptionRepositoryInterface::class);
        $this->quoteFactory = $objectManager->get(QuoteFactory::class);
        $this->quoteResource = $objectManager->get(QuoteResource::class);
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/set_custom_options_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product_with_options.php
     */
    public function testChangeQuoteItemCustomOptions()
    {
        $sku = 'simple_product';
        $quoteItemId = $this->getQuoteItemIdBySku($sku);
        $customOptionsValues = $this->getCustomOptionsValuesForQuery($sku);
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $customizableOptionsQuery = preg_replace('/"([^"]+)"\s*:\s*/', '$1:', json_encode($customOptionsValues));

        $query = $this->getQuery($maskedQuoteId, $quoteItemId, $customizableOptionsQuery);
        $response = $this->graphQlMutation($query);
        $itemOptionsResponse = $response['updateCartItems']['cart']['items'][0]['customizable_options'];
        self::assertCount(2, $itemOptionsResponse);
        self::assertEquals('test', $itemOptionsResponse[0]['values'][0]['value']);
        self::assertEquals('test', $itemOptionsResponse[1]['values'][0]['value']);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/set_custom_options_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product_with_options.php
     */
    public function testOptionsSetPersistsOnQtyChange()
    {
        $sku = 'simple_product';
        $newQuantity = 2;
        $quoteItemId = $this->getQuoteItemIdBySku($sku);
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = <<<QUERY
mutation {
  updateCartItems(input: {
    cart_id:"$maskedQuoteId"
    cart_items: [
      {
        cart_item_id: $quoteItemId
        quantity: $newQuantity
      }
    ]
  }) {
    cart {
      items {
        quantity
        ... on SimpleCartItem {
          customizable_options {
            label
            values {
              value
            }
          }
        }
      }
    }
  }
}
QUERY;
        $response = $this->graphQlMutation($query);
        $cartItemResponse = $response['updateCartItems']['cart']['items'][0];

        self::assertEquals($newQuantity, $cartItemResponse['quantity']);
        self::assertCount(2, $cartItemResponse['customizable_options']);
        self::assertEquals('initial value', $cartItemResponse['customizable_options'][0]['values'][0]['value']);
        self::assertEquals('initial value', $cartItemResponse['customizable_options'][1]['values'][0]['value']);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/set_custom_options_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product_with_options.php
     */
    public function testOptionsSetChangedOnChangeOneOption()
    {
        $sku = 'simple_product';
        $quoteItemId = $this->getQuoteItemIdBySku($sku);

        /* Get only the first option */
        $customOptionsValues = array_slice($this->getCustomOptionsValuesForQuery($sku), 0, 1);

        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $customizableOptionsQuery = preg_replace('/"([^"]+)"\s*:\s*/', '$1:', json_encode($customOptionsValues));
        $query = $this->getQuery($maskedQuoteId, $quoteItemId, $customizableOptionsQuery);

        $response = $this->graphQlMutation($query);
        $itemOptionsResponse = $response['updateCartItems']['cart']['items'][0]['customizable_options'];
        self::assertCount(1, $itemOptionsResponse);
        self::assertEquals('test', $itemOptionsResponse[0]['values'][0]['value']);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/set_custom_options_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product_with_options.php
     */
    public function testOptionSetPersistsOnExtraOptionWithIncorrectId()
    {
        $sku = 'simple_product';
        $quoteItemId = $this->getQuoteItemIdBySku($sku);
        $customOptionsValues = $this->getCustomOptionsValuesForQuery($sku);

        /* Add nonexistent option to the query */
        $customOptionsValues[] = ['id' => -10, 'value_string' => 'value'];

        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $customizableOptionsQuery = preg_replace('/"([^"]+)"\s*:\s*/', '$1:', json_encode($customOptionsValues));
        $query = $this->getQuery($maskedQuoteId, $quoteItemId, $customizableOptionsQuery);

        $response = $this->graphQlMutation($query);
        $itemOptionsResponse = $response['updateCartItems']['cart']['items'][0]['customizable_options'];
        self::assertCount(2, $itemOptionsResponse);
    }

    /**
     * Returns GraphQl query for updating items in shopping cart
     *
     * @param string $maskedQuoteId
     * @param int $quoteItemId
     * @param $customizableOptionsQuery
     * @return string
     */
    private function getQuery(string $maskedQuoteId, int $quoteItemId, $customizableOptionsQuery): string
    {
        return <<<QUERY
mutation {
  updateCartItems(input: {
    cart_id:"$maskedQuoteId"
    cart_items: [
      {
        cart_item_id: $quoteItemId
        quantity: 1
        customizable_options: $customizableOptionsQuery
      }
    ]
  }) {
    cart {
      items {
        quantity
        product {
          name
        }
        ... on SimpleCartItem {
          customizable_options {
            label
            values {
              label
              value
            }
          }
        }
      }
    }
  }
}
QUERY;
    }

    /**
     * Returns quote item id by product's SKU
     *
     * @param string $sku
     * @return int
     * @throws NoSuchEntityException
     */
    private function getQuoteItemIdBySku(string $sku): int
    {
        $quote = $this->quoteFactory->create();
        $product = $this->productRepository->get($sku);
        $this->quoteResource->load($quote, 'test_quote', 'reserved_order_id');
        /** @var Item $quoteItem */
        $quoteItem = $quote->getItemByProduct($product);

        return (int)$quoteItem->getId();
    }

    /**
     * Generate an array with test values for customizable options
     * based on the option type
     *
     * @param string $sku
     * @return array
     */
    private function getCustomOptionsValuesForQuery(string $sku): array
    {
        $customOptions = $this->productCustomOptionsRepository->getList($sku);
        $customOptionsValues = [];

        foreach ($customOptions as $customOption) {
            $optionType = $customOption->getType();
            if ($optionType == 'field' || $optionType == 'area') {
                $customOptionsValues[] = [
                    'id' => (int) $customOption->getOptionId(),
                    'value_string' => 'test'
                ];
            } elseif ($optionType == 'drop_down') {
                $optionSelectValues = $customOption->getValues();
                $customOptionsValues[] = [
                    'id' => (int) $customOption->getOptionId(),
                    'value_string' => reset($optionSelectValues)->getOptionTypeId()
                ];
            }
        }

        return $customOptionsValues;
    }
}
