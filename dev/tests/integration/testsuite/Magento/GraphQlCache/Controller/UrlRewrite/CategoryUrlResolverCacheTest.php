<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Controller\UrlRewrite;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\GraphQl\Controller\GraphQl;
use Magento\GraphQlCache\Controller\AbstractGraphqlCacheTest;
use Magento\UrlRewrite\Model\UrlFinderInterface;

/**
 * Test caching works for categoryUrlResolver
 *
 * @magentoAppArea graphql
 * @magentoCache full_page enabled
 * @magentoDbIsolation disabled
 */
class CategoryUrlResolverCacheTest extends AbstractGraphqlCacheTest
{
    /**
     * @var GraphQl
     */
    private $graphqlController;
    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->graphqlController = $this->objectManager->get(GraphQl::class);
    }

    /**
     * Tests that X-Magento-tags and cache debug headers are correct for category urlResolver
     *
     * @magentoDataFixture Magento/CatalogUrlRewrite/_files/product_with_category.php
     */
    public function testCategoryUrlResolverRequestHasCorrectTags()
    {
        $categoryUrlKey = 'cat-1.html';
        $productSku = 'p002';
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        /** @var Product $product */
        $product = $productRepository->get($productSku, false, null, true);
        $storeId = $product->getStoreId();

        /** @var  UrlFinderInterface $urlFinder */
        $urlFinder = $this->objectManager->get(UrlFinderInterface::class);
        $actualUrls = $urlFinder->findOneByData(
            [
                'request_path' => $categoryUrlKey,
                'store_id' => $storeId
            ]
        );
        $categoryId = $actualUrls->getEntityId();
        $query
            = <<<QUERY
{
  urlResolver(url:"{$categoryUrlKey}")
  {
   id
   relative_url,
   canonical_url
   type
  }
}
QUERY;
        $request = $this->prepareRequest($query);
        /** @var \Magento\Framework\App\Response\Http $response */
        $response = $this->graphqlController->dispatch($request);
        $this->assertEquals('MISS', $response->getHeader('X-Magento-Cache-Debug')->getFieldValue());
        $expectedCacheTags = ['cat_c','cat_c_' . $categoryId, 'FPC'];
        $rawActualCacheTags = $response->getHeader('X-Magento-Tags')->getFieldValue();
        $actualCacheTags = explode(',', $rawActualCacheTags);
        $this->assertEquals($expectedCacheTags, $actualCacheTags);
    }
}
