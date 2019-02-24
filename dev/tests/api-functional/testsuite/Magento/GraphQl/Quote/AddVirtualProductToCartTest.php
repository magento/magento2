<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\Catalog\Api\ProductCustomOptionRepositoryInterface;

class AddVirtualProductToCartTest extends GraphQlAbstract
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
        $this->quote = $objectManager->create(Quote::class);
        $this->quoteIdToMaskedId = $objectManager->get(QuoteIdToMaskedQuoteIdInterface::class);
        $this->productCustomOptionsRepository = $objectManager->get(ProductCustomOptionRepositoryInterface::class);
    }

    /**
     * Test adding a virtual product to the shopping cart with all supported
     * customizable options assigned
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_virtual_with_options.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     */
    public function testAddVirtualProductWithOptions()
    {
        $sku = 'virtual';
        $qty = 1;

        $customOptionsValues = $this->getCustomOptionsValuesForQuery($sku);

        /* Generate customizable options fragment for GraphQl request */
        $queryCustomizableOptions = preg_replace('/"([^"]+)"\s*:\s*/', '$1:', json_encode($customOptionsValues));

        $this->quoteResource->load(
            $this->quote,
            'test_order_1',
            'reserved_order_id'
        );

        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$this->quote->getId());

        $query = <<<QUERY
mutation {  
  addVirtualProductsToCart(
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
        ... on VirtualCartItem {
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

        self::assertArrayHasKey('items', $response['addVirtualProductsToCart']['cart']);
        self::assertCount(1, $response['addVirtualProductsToCart']['cart']);

        $customizableOptionsOutput = $response['addVirtualProductsToCart']['cart']['items'][0]['customizable_options'];
        $assignedOptionsCount = count($customOptionsValues);
        for ($counter = 0; $counter < $assignedOptionsCount; $counter++) {
            self::assertEquals(
                $customOptionsValues[$counter]['value'],
                $customizableOptionsOutput[$counter]['values'][0]['value']
            );
        }
    }

    /**
     * Test adding a virtual product with empty values for required options
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_virtual_with_options.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     */
    public function testAddVirtualProductWithNoRequiredOptionsSet()
    {
        $sku = 'virtual';
        $qty = 1;

        $this->quoteResource->load(
            $this->quote,
            'test_order_1',
            'reserved_order_id'
        );

        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$this->quote->getId());

        $query = <<<QUERY
mutation {  
  addVirtualProductsToCart(
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
        ... on VirtualCartItem {
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
