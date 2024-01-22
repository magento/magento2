<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Controller\Catalog;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\GraphQlCache\Controller\AbstractGraphqlCacheTest;

/**
 * Tests cache debug headers and cache tag validation for a category with product query
 *
 * @magentoAppArea graphql
 * @magentoCache full_page enabled
 * @magentoDbIsolation disabled
 */
class CategoriesWithProductsCacheTest extends AbstractGraphqlCacheTest
{
    /**
     * Test cache tags and debug header for category with products querying for products and category
     *
     * @magentoDataFixture Magento/Catalog/_files/category_product.php
     */
    public function testRequestCacheTagsForCategoryWithProducts(): void
    {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        /** @var ProductInterface $product */
        $product = $productRepository->get('simple333');
        $categoryId = 333;
        $query =
<<<QUERY
query GetCategoryWithProducts(\$id: Int!, \$pageSize: Int!, \$currentPage: Int!) {
        category(id: \$id) {
            id
            description
            name
            product_count
            products(
                      pageSize: \$pageSize,
                      currentPage: \$currentPage) {
                items {
                    id
                    name
                    attribute_set_id
                    url_key
                    sku
                    type_id
                    updated_at
                    url_key
                    url_path
                }
                total_count
            }
        }
    }
QUERY;
        $variables = [
            'id' => $categoryId,
            'pageSize'=> 10,
            'currentPage' => 1
        ];
        $queryParams = [
            'query' => $query,
            'variables' => json_encode($variables),
            'operationName' => 'GetCategoryWithProducts'
        ];

        $response = $this->dispatchGraphQlGETRequest($queryParams);
        $this->assertEquals('MISS', $response->getHeader('X-Magento-Cache-Debug')->getFieldValue());
        $expectedCacheTags = ['cat_c', 'cat_c_' . $categoryId, 'cat_p', 'cat_p_' . $product->getId(), 'FPC'];
        $actualCacheTags = explode(',', $response->getHeader('X-Magento-Tags')->getFieldValue());
        $this->assertEquals($expectedCacheTags, $actualCacheTags);
    }
}
