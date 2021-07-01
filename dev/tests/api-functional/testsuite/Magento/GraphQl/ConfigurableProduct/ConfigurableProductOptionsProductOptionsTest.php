<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\ConfigurableProduct;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\CompareArraysRecursively;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test configurable options products with custom options.
 */
class ConfigurableProductOptionsProductOptionsTest extends GraphQlAbstract
{
    /**
     * @var CompareArraysRecursively
     */
    private $compareArraysRecursively;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->compareArraysRecursively = $objectManager->create(CompareArraysRecursively::class);
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/configurable_product_options_products_with_custom_options.php
     * @dataProvider expectedResultDataProvider
     * @param $expectedOptions
     * @throws \Exception
     */
    public function testQueryConfigurableProductLinks($expectedOptions): void
    {
        $configurableSku = 'configurable-product';
        $query = $this->getQuery($configurableSku);
        $response = $this->graphQlQuery($query);

        $this->assertArrayHasKey('products', $response);
        $this->assertArrayHasKey('items', $response['products']);
        $this->assertConfigurableProductOptions($response['products']['items'], $expectedOptions);
    }

    /**
     * @param $actualResponse
     * @param $expectedOptions
     */
    private function assertConfigurableProductOptions(array $actualResponse, array $expectedOptions): void
    {

        foreach ($expectedOptions as $key => $data) {
            $this->assertNotEmpty(
                $data['variants'],
                "Precondition failed: 'variants' must not be empty"
            );
            $diff = $this->compareArraysRecursively->execute($data, $actualResponse[$key]);
            self::assertEquals([], $diff, "Actual response doesn't equal to expected data");
        }
    }

    /**
     * @param string $configurableSku
     *
     * @return string
     */
    private function getQuery(string $configurableSku): string
    {
        return <<<QUERY
{
    products(filter: {sku: {eq: "{$configurableSku}"}}) {
    items {
      id
      sku
      ... on ConfigurableProduct {
        variants {
            product {
                id
                name
                sku
                type_id
                ... on CustomizableProductInterface {
                    options {
                        title
                        required
                    }
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
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function expectedResultDataProvider(): array
    {
        return [
            'products' => [
                'items' => [
                    [
                        'sku' => 'configurable-product',
                        'variants' => [
                            [
                                'product' => [
                                    'id' => 20,
                                    'name' => 'Configurable Option First Option 2',
                                    'sku' => 'simple_20',
                                    'type_id' => 'simple',
                                    'options' => [
                                        [
                                            'title' => 'field option 20',
                                            'required' => false
                                        ]
                                    ],
                                ]
                            ],
                            [
                                'product' => [
                                    'id' => 30,
                                    'name' => 'Configurable Option First Option 3',
                                    'sku' => 'simple_30',
                                    'type_id' => 'simple',
                                    'options' => [
                                        [
                                            'title' => 'area option 30',
                                            'required' => false
                                        ]
                                    ],
                                ]
                            ],
                            [
                                'product' => [
                                    'id' => 40,
                                    'name' => 'Configurable Option First Option 4',
                                    'sku' => 'simple_40',
                                    'type_id' => 'simple',
                                    'options' => [
                                        [
                                            'title' => 'drop_down option 40',
                                            'required' => false
                                        ]
                                    ],
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
