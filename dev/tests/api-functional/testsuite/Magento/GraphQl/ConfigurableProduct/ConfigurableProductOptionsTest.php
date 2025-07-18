<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\ConfigurableProduct;

use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test products query for configurable product options
 */
class ConfigurableProductOptionsTest extends GraphQlAbstract
{
    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/configurable_products_with_two_attributes.php
     * @dataProvider expectedResultDataProvider
     * @param $expectedOptions
     * @throws \Exception
     */
    public function testQueryConfigurableProductLinks($expectedOptions)
    {
        $configurableProduct = 'configurable';
        $query = $this->getQuery($configurableProduct);

        $response = $this->graphQlQuery($query);

        $this->assertArrayHasKey('products', $response);
        $this->assertArrayHasKey('items', $response['products']);
        $this->assertConfigurableProductOptions($response['products']['items'], $expectedOptions);
    }

    /**
     * @param $actualResponse
     * @param $expectedOptions
     */
    private function assertConfigurableProductOptions($actualResponse, $expectedOptions)
    {
        $this->assertCount(2, $actualResponse);

        foreach ($actualResponse as $responseProduct) {
            $this->assertNotEmpty(
                $responseProduct['configurable_options'],
                "Precondition failed: 'configurable_options' must not be empty"
            );
            $expectedProductOptions = $expectedOptions[$responseProduct['sku']];
            foreach ($expectedProductOptions['configurable_options'] as $optionIndex => $expectedProductOption) {
                $responseProductOption = $responseProduct['configurable_options'][$optionIndex];
                $this->assertEquals(
                    $expectedProductOption['use_default'],
                    $responseProductOption['use_default']
                );
                $this->assertEquals(
                    $expectedProductOption['label'],
                    $responseProductOption['label']
                );
                $this->assertEquals(
                    $expectedProductOption['position'],
                    $responseProductOption['position']
                );
                $this->assertEquals(
                    $expectedProductOption['attribute_code'],
                    $responseProductOption['attribute_code']
                );
                $optionValuesCount = 2;
                self::assertCount(
                    $optionValuesCount,
                    $responseProductOption['values'],
                    'Product option values count in response is different with real option values'
                );
                foreach ($expectedProductOption['values'] as $key => $value) {
                    $responseProductOptionValue = $responseProductOption['values'][$key];
                    $this->assertEquals(
                        $value['label'],
                        $responseProductOptionValue['label']
                    );
                    $this->assertEquals(
                        $value['store_label'],
                        $responseProductOptionValue['store_label']
                    );
                    $this->assertEquals(
                        $value['default_label'],
                        $responseProductOptionValue['default_label']
                    );
                    $this->assertEquals(
                        $value['use_default_value'],
                        $responseProductOptionValue['use_default_value']
                    );
                }
            }
        }
    }

    /**
     * @param string $configurableProduct
     * @return string
     */
    private function getQuery($configurableProduct)
    {
        return <<<QUERY
{
  products(filter: {name: {match: "$configurableProduct"}}) {
    items {
      id
      sku
      ... on ConfigurableProduct {
        configurable_options {
          id
          attribute_id
          label
          position
          use_default
          attribute_code
          values {
            value_index
            label
            store_label
            default_label
            use_default_value
          }
          product_id
        }
      }
    }
  }
}
QUERY;
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function expectedResultDataProvider()
    {
        return [
            [
                [
                    'configurable_12345' =>
                        [
                            'sku' => 'configurable_12345',
                            'configurable_options' =>
                                [
                                    [
                                        'label' => 'Test Configurable First',
                                        'position' => 0,
                                        'use_default' => false,
                                        'attribute_code' => 'test_configurable_first',
                                        'values' =>
                                            [
                                                [
                                                    'label' => 'First Option 3',
                                                    'store_label' => 'First Option 3',
                                                    'default_label' => 'First Option 3',
                                                    'use_default_value' => true,
                                                ],
                                                [
                                                    'label' => 'First Option 4',
                                                    'store_label' => 'First Option 4',
                                                    'default_label' => 'First Option 4',
                                                    'use_default_value' => true,
                                                ],
                                            ],
                                    ],
                                    [
                                        'label' => 'Test Configurable Second',
                                        'position' => 1,
                                        'use_default' => false,
                                        'attribute_code' => 'test_configurable_second',
                                        'values' =>
                                            [
                                                [
                                                    'label' => 'Second Option 3',
                                                    'store_label' => 'Second Option 3',
                                                    'default_label' => 'Second Option 3',
                                                    'use_default_value' => true,
                                                ],
                                                [
                                                    'label' => 'Second Option 4',
                                                    'store_label' => 'Second Option 4',
                                                    'default_label' => 'Second Option 4',
                                                    'use_default_value' => true,
                                                ],
                                            ],
                                    ],
                                ],
                        ],
                    'configurable' =>
                        [
                            'sku' => 'configurable',
                            'configurable_options' =>
                                [
                                    [
                                        'label' => 'Test Configurable First',
                                        'position' => 0,
                                        'use_default' => false,
                                        'attribute_code' => 'test_configurable_first',
                                        'values' =>
                                            [
                                                [
                                                    'label' => 'First Option 1',
                                                    'store_label' => 'First Option 1',
                                                    'default_label' => 'First Option 1',
                                                    'use_default_value' => true,
                                                ],
                                                [
                                                    'label' => 'First Option 2',
                                                    'store_label' => 'First Option 2',
                                                    'default_label' => 'First Option 2',
                                                    'use_default_value' => true,
                                                ],
                                            ],
                                    ],
                                    [
                                        'label' => 'Test Configurable Second',
                                        'position' => 1,
                                        'use_default' => false,
                                        'attribute_code' => 'test_configurable_second',
                                        'values' =>
                                            [
                                                [
                                                    'label' => 'Second Option 1',
                                                    'store_label' => 'Second Option 1',
                                                    'default_label' => 'Second Option 1',
                                                    'use_default_value' => true,
                                                ],
                                                [
                                                    'label' => 'Second Option 2',
                                                    'store_label' => 'Second Option 2',
                                                    'default_label' => 'Second Option 2',
                                                    'use_default_value' => true,
                                                ],
                                            ],
                                    ],
                                ],
                        ]
                ]
            ]
        ];
    }
}
