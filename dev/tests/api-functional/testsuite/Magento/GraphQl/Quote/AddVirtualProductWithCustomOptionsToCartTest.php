<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Catalog\Api\ProductCustomOptionRepositoryInterface;

/**
 * Add virtual product with custom options to cart testcases
 */
class AddVirtualProductWithCustomOptionsToCartTest extends GraphQlAbstract
{
    /**
     * @var GetMaskedQuoteIdByReservedOrderId
     */
    private $getMaskedQuoteIdByReservedOrderId;

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
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
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
        $quantity = 1;
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1');

        $customOptionsValues = $this->getCustomOptionsValuesForQuery($sku);
        /* Generate customizable options fragment for GraphQl request */
        $queryCustomizableOptionValues = preg_replace('/"([^"]+)"\s*:\s*/', '$1:', json_encode($customOptionsValues));

        $customizableOptions = "customizable_options: {$queryCustomizableOptionValues}";
        $query = $this->getQuery($maskedQuoteId, $sku, $quantity, $customizableOptions);

        $response = $this->graphQlMutation($query);

        self::assertArrayHasKey('items', $response['addVirtualProductsToCart']['cart']);
        self::assertCount(1, $response['addVirtualProductsToCart']['cart']);

        $customizableOptionsOutput = $response['addVirtualProductsToCart']['cart']['items'][0]['customizable_options'];
        $assignedOptionsCount = count($customOptionsValues);
        for ($counter = 0; $counter < $assignedOptionsCount; $counter++) {
            $expectedValues = $this->buildExpectedValuesArray($customOptionsValues[$counter]['value_string']);
            self::assertEquals(
                $expectedValues,
                $customizableOptionsOutput[$counter]['values']
            );
        }
    }

    /**
     * Test adding a virtual product with empty values for required options
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_virtual_with_options.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     */
    public function testAddSimpleProductWithMissedRequiredOptionsSet()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1');
        $sku = 'virtual';
        $quantity = 1;
        $customizableOptions = '';

        $query = $this->getQuery($maskedQuoteId, $sku, $quantity, $customizableOptions);

        self::expectExceptionMessage(
            'The product\'s required option(s) weren\'t entered. Make sure the options are entered and try again.'
        );
        $this->graphQlMutation($query);
    }

    /**
     * @param string $maskedQuoteId
     * @param string $sku
     * @param float $quantity
     * @param string $customizableOptions
     * @return string
     */
    private function getQuery(string $maskedQuoteId, string $sku, float $quantity, string $customizableOptions): string
    {
        return <<<QUERY
mutation {  
  addVirtualProductsToCart(
    input: {
      cart_id: "{$maskedQuoteId}", 
      cart_items: [
        {
          data: {
            quantity: $quantity
            sku: "$sku"
          }
          {$customizableOptions}
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
                    'id' => (int)$customOption->getOptionId(),
                    'value_string' => 'test'
                ];
            } elseif ($optionType == 'drop_down') {
                $optionSelectValues = $customOption->getValues();
                $customOptionsValues[] = [
                    'id' => (int)$customOption->getOptionId(),
                    'value_string' => reset($optionSelectValues)->getOptionTypeId()
                ];
            } elseif ($optionType == 'multiple') {
                $customOptionsValues[] = [
                    'id' => (int)$customOption->getOptionId(),
                    'value_string' => '[' . implode(',', array_keys($customOption->getValues())) . ']'
                ];
            }
        }

        return $customOptionsValues;
    }

    /**
     * Build the part of expected response.
     *
     * @param string $assignedValue
     * @return array
     */
    private function buildExpectedValuesArray(string $assignedValue) : array
    {
        $assignedOptionsArray = explode(',', trim($assignedValue, '[]'));
        $expectedArray = [];
        foreach ($assignedOptionsArray as $assignedOption) {
            $expectedArray[] = ['value' => $assignedOption];
        }
        return $expectedArray;
    }
}
