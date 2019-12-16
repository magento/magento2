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
use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\GetPageByIdentifierInterface;

/**
 * Test caching works for categoryUrlResolver
 *
 * @magentoAppArea graphql
 * @magentoCache full_page enabled
 * @magentoDbIsolation disabled
 */
class AllEntitiesUrlResolverCacheTest extends AbstractGraphqlCacheTest
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
     * @magentoDataFixture Magento/Cms/_files/pages.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testAllEntitiesUrlResolverRequestHasCorrectTags()
    {
        $categoryUrlKey = 'cat-1.html';
        $productUrlKey = 'p002.html';
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
        $categoryQuery = $this->getQuery($categoryUrlKey);

        $productQuery = $this->getQuery($productUrlKey);

        /** @var GetPageByIdentifierInterface $page */
        $page = $this->objectManager->get(GetPageByIdentifierInterface::class);
        /** @var PageInterface $cmsPage */
        $cmsPage = $page->execute('page100', 0);
        $cmsPageId = $cmsPage->getId();
        $requestPath = $cmsPage->getIdentifier();
        $pageQuery = $this->getQuery($requestPath);

        // query category for MISS
        $request = $this->prepareRequest($categoryQuery);
        $response = $this->graphqlController->dispatch($request);
        $this->assertEquals('MISS', $response->getHeader('X-Magento-Cache-Debug')->getFieldValue());
        $expectedCacheTags = ['cat_c','cat_c_' . $categoryId, 'FPC'];
        $rawActualCacheTags = $response->getHeader('X-Magento-Tags')->getFieldValue();
        $actualCacheTags = explode(',', $rawActualCacheTags);
        $this->assertEquals($expectedCacheTags, $actualCacheTags);

        // query product for MISS
        $request = $this->prepareRequest($productQuery);
        $response = $this->graphqlController->dispatch($request);
        $this->assertEquals('MISS', $response->getHeader('X-Magento-Cache-Debug')->getFieldValue());
        $expectedCacheTags = ['cat_p', 'cat_p_' . $product->getId(), 'FPC'];
        $rawActualCacheTags = $response->getHeader('X-Magento-Tags')->getFieldValue();
        $actualCacheTags = explode(',', $rawActualCacheTags);
        $this->assertEquals($expectedCacheTags, $actualCacheTags);

        // query page for MISS
        $request = $this->prepareRequest($pageQuery);
        $response = $this->graphqlController->dispatch($request);
        $this->assertEquals('MISS', $response->getHeader('X-Magento-Cache-Debug')->getFieldValue());
        $expectedCacheTags = ['cms_p','cms_p_' . $cmsPageId,'FPC'];
        $rawActualCacheTags = $response->getHeader('X-Magento-Tags')->getFieldValue();
        $actualCacheTags = explode(',', $rawActualCacheTags);
        $this->assertEquals($expectedCacheTags, $actualCacheTags);

        // query category for HIT
        $request = $this->prepareRequest($categoryQuery);
        $response = $this->graphqlController->dispatch($request);
        $this->assertEquals('HIT', $response->getHeader('X-Magento-Cache-Debug')->getFieldValue());
        $expectedCacheTags = ['cat_c','cat_c_' . $categoryId, 'FPC'];
        $rawActualCacheTags = $response->getHeader('X-Magento-Tags')->getFieldValue();
        $actualCacheTags = explode(',', $rawActualCacheTags);
        $this->assertEquals($expectedCacheTags, $actualCacheTags);

        // query product for HIT
        $request = $this->prepareRequest($productQuery);
        $response = $this->graphqlController->dispatch($request);
        $this->assertEquals('HIT', $response->getHeader('X-Magento-Cache-Debug')->getFieldValue());
        $expectedCacheTags = ['cat_p', 'cat_p_' . $product->getId(), 'FPC'];
        $rawActualCacheTags = $response->getHeader('X-Magento-Tags')->getFieldValue();
        $actualCacheTags = explode(',', $rawActualCacheTags);
        $this->assertEquals($expectedCacheTags, $actualCacheTags);

        // query product for HIT
        $request = $this->prepareRequest($pageQuery);
        $response = $this->graphqlController->dispatch($request);
        $this->assertEquals('HIT', $response->getHeader('X-Magento-Cache-Debug')->getFieldValue());
        $expectedCacheTags = ['cms_p','cms_p_' . $cmsPageId,'FPC'];
        $rawActualCacheTags = $response->getHeader('X-Magento-Tags')->getFieldValue();
        $actualCacheTags = explode(',', $rawActualCacheTags);
        $this->assertEquals($expectedCacheTags, $actualCacheTags);

        $product->setUrlKey('something-else-that-invalidates-the-cache');
        $productRepository->save($product);
        $productQuery = $this->getQuery('something-else-that-invalidates-the-cache.html');

        // query category for MISS
        $request = $this->prepareRequest($categoryQuery);
        $response = $this->graphqlController->dispatch($request);
        $this->assertEquals('MISS', $response->getHeader('X-Magento-Cache-Debug')->getFieldValue());
        $expectedCacheTags = ['cat_c','cat_c_' . $categoryId, 'FPC'];
        $rawActualCacheTags = $response->getHeader('X-Magento-Tags')->getFieldValue();
        $actualCacheTags = explode(',', $rawActualCacheTags);
        $this->assertEquals($expectedCacheTags, $actualCacheTags);

        // query product for HIT
        $request = $this->prepareRequest($productQuery);
        $response = $this->graphqlController->dispatch($request);
        $this->assertEquals('MISS', $response->getHeader('X-Magento-Cache-Debug')->getFieldValue());
        $expectedCacheTags = ['cat_p', 'cat_p_' . $product->getId(), 'FPC'];
        $rawActualCacheTags = $response->getHeader('X-Magento-Tags')->getFieldValue();
        $actualCacheTags = explode(',', $rawActualCacheTags);
        $this->assertEquals($expectedCacheTags, $actualCacheTags);
    }

    /**
     * Get urlResolver query
     *
     * @param string $id
     * @return string
     */
    private function getQuery(string $requestPath) : string
    {
        $resolverQuery = <<<QUERY
{
  urlResolver(url:"{$requestPath}")
  {
   id
   relative_url
   canonical_url
   type
  }
}
QUERY;
        return $resolverQuery;
    }
}
