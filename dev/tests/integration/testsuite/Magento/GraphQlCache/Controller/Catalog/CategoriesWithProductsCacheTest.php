<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Controller\Catalog;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Request\Http;
use Magento\GraphQl\Controller\GraphQl;
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
     * @var GraphQl
     */
    private $graphqlController;

    /**
     * @var Http
     */
    private $request;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->graphqlController = $this->objectManager->get(\Magento\GraphQl\Controller\GraphQl::class);
        $this->request = $this->objectManager->create(Http::class);
    }
    /**
     * Test cache tags and debug header for category with products querying for products and category
     *
     * @magentoDataFixture Magento/Catalog/_files/category_product.php
     */
    public function testToCheckRequestCacheTagsForCategoryWithProducts(): void
    {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        /** @var ProductInterface $product */
        $product = $productRepository->get('simple333');
        $categoryId ='333';
        $query
            = <<<QUERY
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
        $variables =[
            'id' => $categoryId,
            'pageSize'=> 10,
            'currentPage' => 1
        ];
        $queryParams = [
            'query' => $query,
            'variables' => json_encode($variables),
            'operationName' => 'GetCategoryWithProducts'
        ];

        /** @var \Magento\Framework\UrlInterface $urlInterface */
        $urlInterface = $this->objectManager->create(\Magento\Framework\UrlInterface::class);
        //set unique URL
        $urlInterface->setQueryParam('query', $queryParams['query']);
        $urlInterface->setQueryParam('variables', $queryParams['variables']);
        $urlInterface->setQueryParam('operationName', $queryParams['operationName']);
        $this->request->setUri($urlInterface->getUrl('graphql'));
        $this->request->setPathInfo('/graphql');
        $this->request->setMethod('GET');
        $this->request->setParams($queryParams);
        $response = $this->graphqlController->dispatch($this->request);
        $this->assertEquals('MISS', $response->getHeader('X-Magento-Cache-Debug')->getFieldValue());
        $expectedCacheTags = ['cat_c','cat_c_' . $categoryId,'cat_p','cat_p_' . $product->getId(),'FPC'];
        $actualCacheTags = explode(',', $response->getHeader('X-Magento-Tags')->getFieldValue());
        $this->assertEquals($expectedCacheTags, $actualCacheTags);
    }
}
