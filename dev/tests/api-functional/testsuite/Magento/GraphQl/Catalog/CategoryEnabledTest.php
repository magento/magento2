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
 * Test is categories enabled for specific storeView
 *
 * Preconditions:
 *   Fixture with enabled and disabled categories in two stores created
 * Steps:
 *  Set Headers - Store = ukrainian
 *  Send Request:
 *   query{
 *    category(id: %categoryId%){
 *     id
 *     name
 *    }
 *   }
 *  Expected response:
 *   {
 *    "category": {
 *     "id": %categoryId%,
 *     "name": "Category_UA"
 *    }
 *   }
 *
 * @magentoApiDataFixture Magento/Catalog/_files/category_enabled_for_store.php
 */
class CategoryEnabledTest extends GraphQlAbstract
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
     * Verify that category enabled for specific store view
     *
     * @param string $query
     * @param string $storeCode
     * @param array $category
     * @return void
     * @throws \Exception
     * @dataProvider categoryEnabledDataProvider
     */
    public function testCategoryEnabledForSpecificStoreView(string $query, string $storeCode, array $category): void
    {
        $response = $this->graphQlQuery($query, [], '', ['store' => $storeCode]);

        // check are there any items in the return data
        self::assertNotNull($response['category'], 'category must not be null');

        // check entire response
        $this->assertResponseFields($response, $category);
    }

    /**
     * Verify that category disabled for specific store view
     *
     * @param string $query
     * @param string $storeCode
     * @param array $category
     * @return void
     * @throws \Exception
     * @dataProvider categoryDisabledDataProvider
     */
    public function testCategoryDisabledForSpecificStoreView(string $query, string $storeCode, array $category): void
    {
        $this->markTestSkipped(
            'GraphQL response currently return Exception instead of data structure - MC-20132'
        );
        $response = $this->graphQlQuery($query, [], '', ['store' => $storeCode]);

        // check are there any items in the return data
        self::assertNotNull($response['category'], 'category must not be null');

        // check entire response
        $this->assertResponseFields($response, $category);
    }

    /**
     * Data provider for enabled category
     *
     * @return array
     */
    public function categoryEnabledDataProvider(): array
    {
        return [
            [
                'query' => $this->getQuery(44),
                'store' => 'default',
                'data' => [
                    'category' => [
                        'id' => 44,
                        'name' => 'Category_UA',
                    ],
                ]
            ],
        ];
    }

    /**
     * Data provider for disabled category
     *
     * @return array[][]
     */
    public function categoryDisabledDataProvider(): array
    {
        return [
            [
                'query' => $this->getQuery(33),
                'store' => 'english',
                'data' => [
                    'category' => null,
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
    }
}
QUERY;
    }
}
