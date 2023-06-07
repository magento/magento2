<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Get customizable options of simple product via the corresponding GraphQl query and add the product
 * with customizable options to the shopping cart
 */
class AddSimpleProductToCartEndToEndTest extends GraphQlAbstract
{
    /**
     * @var GetCustomOptionsWithUIDForQueryBySku
     */
    private $getCustomOptionsWithIDV2ForQueryBySku;

    /**
     * @var GetMaskedQuoteIdByReservedOrderId
     */
    private $getMaskedQuoteIdByReservedOrderId;

    /**
     * @var GetCartItemOptionsFromUID
     */
    private $getCartItemOptionsFromUID;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
        $this->getCartItemOptionsFromUID = $objectManager->get(GetCartItemOptionsFromUID::class);
        $this->getCustomOptionsWithIDV2ForQueryBySku = $objectManager->get(
            GetCustomOptionsWithUIDForQueryBySku::class
        );
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
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1');
        $qty = 1;

        $productOptionsData = $this->getProductOptionsViaQuery($sku);

        $itemOptionsQuery = preg_replace(
            '/"([^"]+)"\s*:\s*/',
            '$1:',
            json_encode($productOptionsData['received_options'])
        );

        $query = $this->getAddToCartMutation($maskedQuoteId, $qty, $sku, trim($itemOptionsQuery, '{}'));
        $response = $this->graphQlMutation($query);

        self::assertArrayHasKey('customizable_options', $response['addProductsToCart']['cart']['items'][0]);

        foreach ($response['addProductsToCart']['cart']['items'][0]['customizable_options'] as $option) {
            self::assertEquals($productOptionsData['expected_options'][$option['id']], $option['values'][0]['value']);
        }
    }

    /**
     * Get product data with customizable options using GraphQl query
     *
     * @param string $sku
     * @return array
     * @throws \Exception
     */
    private function getProductOptionsViaQuery(string $sku): array
    {
        $query = $this->getProductQuery($sku);
        $response = $this->graphQlQuery($query);
        self::assertArrayHasKey('options', $response['products']['items'][0]);

        $expectedItemOptions = [];
        $receivedItemOptions = [
            'entered_options' => [],
            'selected_options' => []
        ];

        foreach ($response['products']['items'][0]['options'] as $option) {
            if (isset($option['entered_option'])) {
                /* The date normalization is required since the attribute might value is formatted by the system */
                if ($option['title'] === 'date option') {
                    $value = '2012-12-12 00:00:00';
                    $expectedItemOptions[$option['option_id']] = date('M d, Y', strtotime($value));
                } else {
                    $value = 'test';
                    $expectedItemOptions[$option['option_id']] = $value;
                }
                $value = $option['title'] === 'date option' ? '2012-12-12 00:00:00' : 'test';

                $receivedItemOptions['entered_options'][] = [
                    'uid' => $option['entered_option']['uid'],
                    'value' => $value
                ];

            } elseif (isset($option['selected_option'])) {
                $receivedItemOptions['selected_options'][] = reset($option['selected_option'])['uid'];
                $expectedItemOptions[$option['option_id']] = reset($option['selected_option'])['option_type_id'];
            }
        }

        return [
            'expected_options' => $expectedItemOptions,
            'received_options' => $receivedItemOptions
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
query {
  products(search: "$sku") {
    items {
      sku

      ... on CustomizableProductInterface {
        options {
          option_id
          title

          ... on CustomizableRadioOption {
            option_id
            selected_option: value {
              option_type_id
              uid
            }
          }

          ... on CustomizableDropDownOption {
            option_id
            selected_option: value {
              option_type_id
              uid
            }
          }

          ... on CustomizableMultipleOption {
            option_id
            selected_option: value {
              option_type_id
              uid
            }
          }

          ... on CustomizableCheckboxOption {
            option_id
            selected_option: value {
              option_type_id
              uid
            }
          }

          ... on CustomizableAreaOption {
            option_id
            entered_option: value {
              uid
            }
          }

          ... on CustomizableFieldOption {
            option_id
            entered_option: value {
              uid
            }
          }

          ... on CustomizableFileOption {
            option_id
            entered_option: value {
              uid
            }
          }

          ... on CustomizableDateOption {
            option_id
            entered_option: value {
              uid
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
     * Returns GraphQl mutation for adding item to cart
     *
     * @param string $maskedQuoteId
     * @param int $qty
     * @param string $sku
     * @param string $customizableOptions
     * @return string
     */
    private function getAddToCartMutation(
        string $maskedQuoteId,
        int $qty,
        string $sku,
        string $customizableOptions
    ): string {
        return <<<MUTATION
mutation {
    addProductsToCart(
        cartId: "{$maskedQuoteId}",
        cartItems: [
            {
                sku: "{$sku}"
                quantity: {$qty}
                {$customizableOptions}
            }
        ]
    ) {
        cart {
            items {
                quantity
                ... on SimpleCartItem {
                    customizable_options {
                        label
                        id
                          values {
                            value
                        }
                    }
                }
            }
        },
        user_errors {
            message
        }
    }
}
MUTATION;
    }
}
