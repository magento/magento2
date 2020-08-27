<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Product filtering by condition "match" for "Name" attribute
 *
 * Preconditions:
 *   Fixture simple products created
 * Steps:
 *   Send request:
 *     query test {
 *       products(filter: {name: {match: "apple"}}, sort: {price: ASC}) {
 *         items {
 *           name
 *         }
 *         total_count
 *       }
 *     }
 *     Expected Response:
 * {
 *   "data": {
 *     "products": {
 *       "items": [
 *         {"name": "Apple"},
 *         {"name": "AppleOne"},
 *         {"name": "Apple One"},
 *         {"name": "Apple Apple"},
 *         {"name": "One Apple One"},
 *         {"name": "OneApple"},
 *         {"name": "One Apple"},
 *         {"name": "AApple"},
 *         {"name": "AApplee"},
 *         {"name": "Applee"}
 *       ],
 *       "total_count": 10
 *     }
 *   }
 *  }
 */
class ProductSearchNameFilterTest extends GraphQlAbstract
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * Verify that search returns correct values for given price filter
     *
     * @magentoApiDataFixture Magento/Catalog/_files/products_with_different_names.php
     * @param string $match
     * @param string $sort
     * @param array $items
     * @return void
     * @dataProvider productSearchNameDataProvider
     * @throws \Exception
     */
    public function testProductSearchNameFilter(string $match, string $sort, array $items): void
    {
        $this->markTestSkipped(
            "Product search shouldn't use the filter 'match'. " .
            "Filter 'match' or 'equal' should be introduced in MC-18450 instead"
        );
        $query = <<<QUERY
{
  products(filter: {name: {match: "{$match}"}}, sort: {{$sort}}) {
    items {
      name
    }
    total_count
  }
}
QUERY;
        $response = $this->graphQlQuery($query);

        // expected total count
        $totalCount = count($items);

        $assertionMap = [
            'products' => [
                'items' => $items,
                'total_count' => $totalCount,
            ],
        ];

        // check are there any items in the return data
        self::assertNotNull(
            $response['products']['items'],
            'product items must not be null'
        );

        // check for the total of items in return
        self::assertCount(
            $totalCount,
            $response['products']['items'],
            "there are should be $totalCount products in price with filter match: '$match' "
        );

        // check entire response
        $this->assertResponseFields($response, $assertionMap);
    }

    /**
     * Data provider for product search price filter
     *
     * @return array[][]
     */
    public function productSearchNameDataProvider(): array
    {
        return [
            [
                'match' => 'apple',
                'sort' => 'price: ASC',
                'items' => [
                    ['name' => 'Apple'],
                    ['name' => 'AppleOne'],
                    ['name' => 'Apple One'],
                    ['name' => 'Apple Apple'],
                    ['name' => 'One Apple One'],
                    ['name' => 'OneApple'],
                    ['name' => 'One Apple'],
                    ['name' => 'AApple'],
                    ['name' => 'AApplee'],
                    ['name' => 'Applee'],
                ],
            ],
            [
                'match' => 'apple on',
                'sort' => 'name: ASC',
                'items' => [
                    ['name' => 'Apple One'],
                    ['name' => 'One Apple One'],
                ],
            ],
            [
                'match' => 'Apple One',
                'sort' => 'name: DESC',
                'items' => [
                    ['name' => 'One Apple One'],
                    ['name' => 'Apple One'],
                ],
            ],
        ];
    }
}
