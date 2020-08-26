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
 * Test is category fields
 *
 * Preconditions:
 *   Fixture with category in two stores created
 * Steps:
 *  Set Headers - Store = default
 *  Send Request:
 *  query{
 *      category(id: %categoryId%){
 *        id
 *        include_in_menu
 *        name
 *        image
 *        description
 *        display_mode
 *        available_sort_by
 *        default_sort_by
 *        url_key
 *        meta_title
 *        meta_keywords
 *        meta_description
 *      }
 *  }
 * Expected response:
 * {
 *    "category": {
 *      "id": 9,
 *      "include_in_menu": 0,
 *      "name": "Category_en",
 *      "image": NULL,
 *      "description": "<p>Category_en Description</p>",
 *      "display_mode": "PRODUCTS_AND_PAGE",
 *      "available_sort_by": [
 *        "name",
 *        "price"
 *      ],
 *      "default_sort_by": "price",
 *      "url_key": "category-en",
 *      "meta_title": "Category_en Meta Title",
 *      "meta_keywords": "Category_en Meta Keywords",
 *      "meta_description": "Category_en Meta Description"
 *  }
 * }
 */
class CategorySpecificFieldsTest extends GraphQlAbstract
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
     * @magentoApiDataFixture Magento/Catalog/_files/category_specific_fields.php
     * @param int $categoryId
     * @param array $categoryFields
     * @return void
     * @throws \Exception
     * @dataProvider categoryFieldsDataProvider
     */
    public function testSpecificCategoryFields(int $categoryId, array $categoryFields): void
    {
        $query = <<<QUERY
{
    category(id: {$categoryId}){
        id
        include_in_menu
        name
        description
        display_mode
        available_sort_by
        default_sort_by
        url_key
        meta_title
        meta_keywords
        meta_description
    }
}
QUERY;
        $response = $this->graphQlQuery($query);

        // check are there any items in the return data
        self::assertNotNull($response['category'], 'category must not be null');

        // check entire response
        $this->assertResponseFields($response['category'], $categoryFields);
    }

    /**
     * Data provider for enabled category
     *
     * @return array[][]
     */
    public function categoryFieldsDataProvider(): array
    {
        return [
            [
                'category_id' => 10,
                'category_fields' => [
                    'id' => 10,
                    'include_in_menu' => 0,
                    'name' => 'Category_en',
                    'description' => 'Category_en Description',
                    'display_mode' => 'PRODUCTS_AND_PAGE',
                    'available_sort_by' => [
                        'name',
                        'price',
                    ],
                    'default_sort_by' => 'price',
                    'url_key' => 'category-en',
                    'meta_title' => 'Category_en Meta Title',
                    'meta_keywords' => 'Category_en Meta Keywords',
                    'meta_description' => 'Category_en Meta Description',
                ],
            ],
        ];
    }
}
