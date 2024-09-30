<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog\Options;

use Exception;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\CompareArraysRecursively;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for customizable product options.
 */
class CustomizableOptionsTest extends GraphQlAbstract
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
     * @magentoApiDataFixture Magento/Catalog/_files/product_with_options.php
     *
     * @param array $optionDataProvider
     *
     * @dataProvider getProductCustomizableOptionsProvider
     * @throws Exception
     */
    public function testQueryCustomizableOptions(array $optionDataProvider): void
    {
        $productSku = 'simple';
        $query = $this->getQuery($productSku);
        $response = $this->graphQlQuery($query);
        $responseProduct = reset($response['products']['items']);
        self::assertNotEmpty($responseProduct['options']);

        foreach ($optionDataProvider as $key => $data) {
            $this->compareArraysRecursively->execute($data, $responseProduct[$key]);
        }
    }

    /**
     * Get query.
     *
     * @param string $sku
     *
     * @return string
     */
    private function getQuery(string $sku): string
    {
        return <<<QUERY
query {
  products(filter: { sku: { eq: "$sku" } }) {
    items {
      ... on CustomizableProductInterface {
        options {
          option_id
          title
          ... on CustomizableDateOption {
               value {
                  type
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
     * Get product customizable options provider.
     *
     * @return array
     */
    public function getProductCustomizableOptionsProvider(): array
    {
        return [
            'products' => [
                'items' => [
                    'options' => [
                        [
                            'title' => 'test_option_code_1'
                        ],
                        [
                            'title' => 'area option'
                        ],
                        [
                            'title' => 'file option'
                        ],
                        [
                            'title' => 'radio option'
                        ],
                        [
                            'title' => 'multiple option'
                        ],
                        [
                            'title' => 'date option',
                            'values' => [
                                'type' => 'DATE'
                            ]
                        ],
                        [
                            'title' => 'date_time option',
                            'values' => [
                                'type' => 'DATE_TIME'
                            ]
                        ],
                        [
                            'title' => 'time option',
                            'values' => [
                                'type' => 'TIME'
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
