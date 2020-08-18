<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Controller\Catalog;

use Magento\GraphQlCache\Controller\AbstractGraphqlCacheTest;

/**
 * Tests cache debug headers and cache tag validation for a simple category query
 *
 * @magentoAppArea graphql
 * @magentoCache full_page enabled
 * @magentoDbIsolation disabled
 */
class CategoryCacheTest extends AbstractGraphqlCacheTest
{
    /**
     * Test cache tags and debug header for category and querying only for category
     *
     * @magentoDataFixture Magento/Catalog/_files/category_product.php
     */
    public function testRequestCacheTagsForCategory(): void
    {
        $categoryId ='333';
        $query
            = <<<QUERY
        {
            category(id: $categoryId) {
            id
            name
            url_key
            description
            product_count
           }
       }
QUERY;
        $response = $this->dispatchGraphQlGETRequest(['query' => $query]);
        $this->assertEquals('MISS', $response->getHeader('X-Magento-Cache-Debug')->getFieldValue());
        $actualCacheTags = explode(',', $response->getHeader('X-Magento-Tags')->getFieldValue());
        $expectedCacheTags = ['cat_c','cat_c_' . $categoryId, 'FPC'];
        $this->assertEquals($expectedCacheTags, $actualCacheTags);
    }
}
