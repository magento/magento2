<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Controller\UrlRewrite;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Response\HttpInterface as HttpResponse;
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
    private function assertCacheMISSWithTagsForCategory(string $categoryId, HttpResponse $response): void
    {
        $this->assertCacheMISS($response);
        $this->assertCacheTags($categoryId, 'cat_c', $response);
    }

    private function assertCacheMISSWithTagsForProduct(string $productId, HttpResponse $response): void
    {
        $this->assertCacheMISS($response);
        $this->assertCacheTags($productId, 'cat_p', $response);
    }

    private function assertCacheMISSWithTagsForCmsPage(string $pageId, HttpResponse $response): void
    {
        $this->assertCacheMISS($response);
        $this->assertCacheTags($pageId, 'cms_p', $response);
    }

    private function assertCacheHITWithTagsForCategory(string $categoryId, HttpResponse $response): void
    {
        $this->assertCacheHIT($response);
        $this->assertCacheTags($categoryId, 'cat_c', $response);
    }

    private function assertCacheHITWithTagsForProduct(string $productId, HttpResponse $response): void
    {
        $this->assertCacheHIT($response);
        $this->assertCacheTags($productId, 'cat_p', $response);
    }

    private function assertCacheHITWithTagsForCmsPage(string $pageId, HttpResponse $response): void
    {
        $this->assertCacheHIT($response);
        $this->assertCacheTags($pageId, 'cms_p', $response);
    }

    private function assertCacheMISS(HttpResponse $response): void
    {
        $this->assertEquals('MISS', $response->getHeader('X-Magento-Cache-Debug')->getFieldValue());
    }

    private function assertCacheHIT(HttpResponse $response): void
    {
        $this->assertEquals('HIT', $response->getHeader('X-Magento-Cache-Debug')->getFieldValue());
    }

    private function assertCacheTags(string $entityId, string $entityCacheTag, HttpResponse $response)
    {
        $expectedCacheTags  = [$entityCacheTag, $entityCacheTag . '_' . $entityId, 'FPC'];
        $rawActualCacheTags = $response->getHeader('X-Magento-Tags')->getFieldValue();
        $actualCacheTags    = explode(',', $rawActualCacheTags);
        $this->assertEquals($expectedCacheTags, $actualCacheTags);
    }

    private function buildQuery(string $requestPath): string
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

    /**
     * Tests that X-Magento-Tags and cache debug headers are correct for category urlResolver
     *
     * @magentoDataFixture Magento/CatalogUrlRewrite/_files/product_with_category.php
     * @magentoDataFixture Magento/Cms/_files/pages.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testAllEntitiesUrlResolverRequestHasCorrectTags(): void
    {
        $categoryUrlKey = 'cat-1.html';
        $productUrlKey  = 'p002.html';
        $productSku     = 'p002';
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        /** @var Product $product */
        $product = $productRepository->get($productSku, false, null, true);
        $storeId = (string) $product->getStoreId();

        /** @var  UrlFinderInterface $urlFinder */
        $urlFinder     = $this->objectManager->get(UrlFinderInterface::class);
        $actualUrls    = $urlFinder->findOneByData(
            [
                'request_path' => $categoryUrlKey,
                'store_id' => $storeId
            ]
        );
        $categoryId    = (string) $actualUrls->getEntityId();
        $categoryQuery = $this->buildQuery($categoryUrlKey);

        $productQuery = $this->buildQuery($productUrlKey);

        /** @var GetPageByIdentifierInterface $page */
        $page = $this->objectManager->get(GetPageByIdentifierInterface::class);
        /** @var PageInterface $cmsPage */
        $cmsPage     = $page->execute('page100', 0);
        $cmsPageId   = (string) $cmsPage->getId();
        $requestPath = $cmsPage->getIdentifier();
        $pageQuery   = $this->buildQuery($requestPath);

        // query category for MISS
        $response = $this->dispatchGraphQlGETRequest(['query' => $categoryQuery]);
        $this->assertCacheMISSWithTagsForCategory($categoryId, $response);

        // query product for MISS
        $response = $this->dispatchGraphQlGETRequest(['query' => $productQuery]);
        $this->assertCacheMISSWithTagsForProduct((string) $product->getId(), $response);

        // query page for MISS
        $response = $this->dispatchGraphQlGETRequest(['query' => $pageQuery]);
        $this->assertCacheMISSWithTagsForCmsPage($cmsPageId, $response);

        // query category for HIT
        $response = $this->dispatchGraphQlGETRequest(['query' => $categoryQuery]);
        $this->assertCacheHITWithTagsForCategory($categoryId, $response);

        // query product for HIT
        $response = $this->dispatchGraphQlGETRequest(['query' => $productQuery]);
        $this->assertCacheHITWithTagsForProduct((string) $product->getId(), $response);

        // query page for HIT
        $response = $this->dispatchGraphQlGETRequest(['query' => $pageQuery]);
        $this->assertCacheHITWithTagsForCmsPage($cmsPageId, $response);

        $product->setUrlKey('something-else-that-invalidates-the-cache');
        $productRepository->save($product);
        $productQuery = $this->buildQuery('something-else-that-invalidates-the-cache.html');

        // query category for MISS
        $response = $this->dispatchGraphQlGETRequest(['query' => $categoryQuery]);
        $this->assertCacheMISSWithTagsForCategory($categoryId, $response);

        // query product for MISS
        $response = $this->dispatchGraphQlGETRequest(['query' => $productQuery]);
        $this->assertCacheMISSWithTagsForProduct((string) $product->getId(), $response);

        // query page for HIT
        $response = $this->dispatchGraphQlGETRequest(['query' => $pageQuery]);
        $this->assertCacheHITWithTagsForCmsPage($cmsPageId, $response);
    }
}
