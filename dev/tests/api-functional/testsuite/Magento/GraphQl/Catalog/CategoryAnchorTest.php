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
 * Test is categories anchor or not
 *
 * Preconditions:
 *   Fixture with anchor and not-anchored categories created
 * Steps:
 *  Send Request:
 *   query{
 *    category(id: %categoryId%){
 *     id
 *     name
 *     is_anchor
 *     product_count
 *     products(pageSize: 10, currentPage: 1){
 *     items{
 *     name
 *     }
 *    }
 *   }
 *  Expected response:
 * {
 *    "category": {
 *       "id": %category1Id%,
 *       "name": Category_Anchor,
 *       "is_anchor": 1,
 *       "product_count": 2,
 *       "products": {
 *         "items": [
 *           {
 *             "name": "Product1",
 *             "name": "Product2"
 *           }
 *         ]
 *      }
 *   }
 * }
 */
class CategoryAnchorTest extends GraphQlAbstract
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
     * Verify that request returns correct values for given category
     *
     * @magentoApiDataFixture Magento/Catalog/_files/category_anchor.php
     * @param string $query
     * @param string $storeCode
     * @param array $category
     * @return void
     * @throws \Exception
     * @dataProvider categoryAnchorDataProvider
     */
    public function testCategoryAnchor(string $query, string $storeCode, array $category): void
    {
        $response = $this->graphQlQuery($query, [], '', ['store' => $storeCode]);

        // check are there any items in the return data
        self::assertNotNull($response['category'], 'category must not be null');

        // check entire response
        $this->assertResponseFields($response, $category);
    }

    /**
     * Data provider for anchored category and product inside
     *
     * @return array[][]
     */
    public function categoryAnchorDataProvider(): array
    {
        return [
            [
                'query' => $this->getQuery(22),
                'store' => 'default',
                'data' => [
                    'category' => [
                        'id' => 22,
                        'name' => 'Category_Anchor',
                        'is_anchor' => 1,
                        'product_count' => 2,
                        'products' => [
                            'items' => [
                                ['name' => 'Product1'],
                                ['name' => 'Product2'],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'query' => $this->getQuery(11),
                'store' => 'default',
                'data' => [
                    'category' => [
                        'id' => 11,
                        'name' => 'Category_Default',
                        'is_anchor' => 0,
                        'product_count' => 1,
                        'products' => [
                            'items' => [
                                ['name' => 'Product1'],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Return GraphQL query string by categoryId
     *
     * @param int $categoryId
     * @return string
     */
    private function getQuery(int $categoryId): string
    {
        return <<<QUERY
{
    category(id: {$categoryId}){
        id
        name
        is_anchor
        product_count
        products(pageSize: 10, currentPage: 1, sort: {name: ASC}){
            items{
                name
            }
        }
    }
}
QUERY;
    }
}
