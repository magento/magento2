<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Magento\Catalog\Api\ProductCustomOptionRepositoryInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class AddSimpleProductToCartTest extends GraphQlAbstract
{
    /**
     * @var QuoteResource
     */
    private $quoteResource;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    private $quoteIdToMaskedId;

    /**
     * @var ProductCustomOptionRepositoryInterface
     */
    private $productCustomOptionsRepository;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->quoteResource = $objectManager->get(QuoteResource::class);
        $this->quoteFactory = $objectManager->get(QuoteFactory::class);
        $this->quoteIdToMaskedId = $objectManager->get(QuoteIdToMaskedQuoteIdInterface::class);
        $this->productCustomOptionsRepository = $objectManager->get(ProductCustomOptionRepositoryInterface::class);
    }

    /**
     * Test adding a simple product to the shopping cart with all supported
     * customizable options assigned
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_with_options.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     */
    public function testAddSimpleProductWithOptions()
    {
        $sku = 'simple';
        $qty = 1;

        $customOptionsValues = $this->getCustomOptionsValuesForQuery($sku);

        /* Generate customizable options fragment for GraphQl request */
        $queryCustomizableOptions = preg_replace('/"([^"]+)"\s*:\s*/', '$1:', json_encode($customOptionsValues));

        $maskedQuoteId = $this->getMaskedQuoteId();

        $query = <<<QUERY
mutation {  
  addSimpleProductsToCart(
    input: {
      cart_id: "{$maskedQuoteId}", 
      cartItems: [
        {
          data: {
            qty: $qty
            sku: "$sku"
          },
          customizable_options: $queryCustomizableOptions  
        }
      ]
    }
  ) {
    cart {
      items {
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

        $response = $this->graphQlQuery($query);

        self::assertArrayHasKey('items', $response['addSimpleProductsToCart']['cart']);
        self::assertCount(1, $response['addSimpleProductsToCart']['cart']);

        $customizableOptionsOutput = $response['addSimpleProductsToCart']['cart']['items'][0]['customizable_options'];
        $assignedOptionsCount = count($customOptionsValues);
        for ($counter = 0; $counter < $assignedOptionsCount; $counter++) {
            self::assertEquals(
                $customOptionsValues[$counter]['value'],
                $customizableOptionsOutput[$counter]['values'][0]['value']
            );
        }
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     */
    public function testAddSimpleProductToCart()
    {
        $sku = 'simple';
        $qty = 2;
        $maskedQuoteId = $this->getMaskedQuoteId();

        $query = $this->getAddSimpleProductQuery($maskedQuoteId, $sku, $qty);
        $response = $this->graphQlQuery($query);
        self::assertArrayHasKey('cart', $response['addSimpleProductsToCart']);

        self::assertEquals($qty, $response['addSimpleProductsToCart']['cart']['items'][0]['qty']);
        self::assertEquals($sku, $response['addSimpleProductsToCart']['cart']['items'][0]['product']['sku']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     * @expectedException \Exception
     * @expectedExceptionMessage Please enter a number greater than 0 in this field.
     */
    public function testAddSimpleProductToCartWithNegativeQty()
    {
        $sku = 'simple';
        $qty = -2;
        $maskedQuoteId = $this->getMaskedQuoteId();

        $query = $this->getAddSimpleProductQuery($maskedQuoteId, $sku, $qty);
        $this->graphQlQuery($query);
    }

    /**
     * @return string
     */
    private function getMaskedQuoteId() : string
    {
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, 'test_order_1', 'reserved_order_id');

        return $this->quoteIdToMaskedId->execute((int)$quote->getId());
    }

    /**
     * @param string $maskedQuoteId
     * @param string $sku
     * @param int $qty
     * @return string
     */
    public function getAddSimpleProductQuery(string $maskedQuoteId, string $sku, int $qty): string
    {
        return <<<QUERY
mutation {  
  addSimpleProductsToCart(
    input: {
      cart_id: "{$maskedQuoteId}"
      cartItems: [
        {
          data: {
            qty: $qty
            sku: "$sku"
          }
        }
      ]
    }
  ) {
    cart {
      items {
        qty
        product {
          sku
        }
      }
    }
  }
}
QUERY;
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products.php
     * @expectedException \Exception
     * @expectedExceptionMessage Could not find a cart with ID "wrong_cart_hash"
     */
    public function testAddProductWithWrongCartHash()
    {
        $sku = 'simple';
        $qty = 1;

        $maskedQuoteId = 'wrong_cart_hash';

        $query = <<<QUERY
mutation {  
  addSimpleProductsToCart(
    input: {
      cart_id: "{$maskedQuoteId}"
      cartItems: [
        {
          data: {
            qty: $qty
            sku: "$sku"
          }
        }
      ]
    }
  ) {
    cart {
      items {
        qty
      }
    }
  }
}
QUERY;

        $this->graphQlQuery($query);
    }

    /**
     * Test adding a simple product with empty values for required options
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_with_options.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     */
    public function testAddSimpleProductWithNoRequiredOptionsSet()
    {
        $sku = 'simple';
        $qty = 1;

        $maskedQuoteId = $this->getMaskedQuoteId();

        $query = <<<QUERY
mutation {  
  addSimpleProductsToCart(
    input: {
      cart_id: "{$maskedQuoteId}", 
      cartItems: [
        {
          data: {
            qty: $qty
            sku: "$sku"
          }
        }
      ]
    }
  ) {
    cart {
      items {
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

        self::expectExceptionMessage(
            'The product\'s required option(s) weren\'t entered. Make sure the options are entered and try again.'
        );

        $this->graphQlQuery($query);
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
                    'value' => 'test'
                ];
            } elseif ($optionType == 'drop_down') {
                $optionSelectValues = $customOption->getValues();
                $customOptionsValues[] = [
                    'id' => (int) $customOption->getOptionId(),
                    'value' => reset($optionSelectValues)->getOptionTypeId()
                ];
            }
        }

        return $customOptionsValues;
    }
}
