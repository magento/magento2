<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\PageCache\UrlRewrite;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\GraphQl\PageCache\GraphQLPageCacheAbstract;
use Magento\GraphQlCache\Model\CacheId\CacheIdCalculator;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewrite as UrlRewriteResourceModel;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Model\UrlRewrite as UrlRewriteModel;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite as UrlRewriteService;

/**
 * Test caching works for url route.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RouteCacheTest extends GraphQLPageCacheAbstract
{
    /** @var ObjectManager */
    private $objectManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * Tests if target_path(relative_url) is resolved for Product entity
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/CatalogUrlRewrite/_files/product_with_category.php
     */
    public function testProductUrlResolver()
    {
        $productSku = 'p002';
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $productRepository->get($productSku, false, null, true);

        $routeQuery = $this->getRouteQuery($this->getProductUrlKey($productSku));
        $response = $this->graphQlQueryWithResponseHeaders($routeQuery);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $response['headers']);
        $cacheId = $response['headers'][CacheIdCalculator::CACHE_ID_HEADER];

        // Verify we obtain a cache MISS the first time we search the cache using this X-Magento-Cache-Id
        $this->assertCacheMissAndReturnResponse($routeQuery, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);

        // Verify we obtain a cache HIT the second time around for this X-Magento-Cache-Id
        $this->assertCacheHitAndReturnResponse($routeQuery, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);
    }

    /**
     * Test the use case where non seo friendly is provided as resolver input in the Query
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/CatalogUrlRewrite/_files/product_with_category.php
     */
    public function testProductUrlWithNonSeoFriendlyUrlInput()
    {
        $productSku = 'p002';
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $productRepository->get($productSku, false, null, true);

        $actualUrls = $this->getProductUrlRewriteData($productSku);
        $nonSeoFriendlyPath = $actualUrls->getTargetPath();

        $routeQuery = $this->getRouteQuery($nonSeoFriendlyPath);
        $response = $this->graphQlQueryWithResponseHeaders($routeQuery);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $response['headers']);
        $cacheId = $response['headers'][CacheIdCalculator::CACHE_ID_HEADER];

        // Verify we obtain a cache MISS the first time we search the cache using this X-Magento-Cache-Id
        $this->assertCacheMissAndReturnResponse($routeQuery, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);

        // Verify we obtain a cache HIT the second time around for this X-Magento-Cache-Id
        $this->assertCacheHitAndReturnResponse($routeQuery, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);
    }

    /**
     * Test the use case where url_key of the existing product is changed and verify final url is redirected correctly
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/Catalog/_files/product_with_category.php
     */
    public function testProductUrlRewriteResolver()
    {
        $productSku = 'in-stock-product';
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get($productSku, false, null, true);
        $this->getProductUrlKey($productSku);
        $renamedKey = 'simple-product-in-stock-new';
        $suffix = '.html';
        $product->setUrlKey($renamedKey)->setData('save_rewrites_history', true)->save();
        $newUrlPath = $renamedKey . $suffix;

        $routeQuery = $this->getRouteQuery($newUrlPath);
        $response = $this->graphQlQueryWithResponseHeaders($routeQuery);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $response['headers']);
        $cacheId = $response['headers'][CacheIdCalculator::CACHE_ID_HEADER];

        // Verify we obtain a cache MISS the first time we search the cache using this X-Magento-Cache-Id
        $this->assertCacheMissAndReturnResponse($routeQuery, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);

        // Verify we obtain a cache HIT the second time around for this X-Magento-Cache-Id
        $this->assertCacheHitAndReturnResponse($routeQuery, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);
    }

    /**
     * Test for custom type which point to the valid product/category/cms page.
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/CatalogUrlRewrite/_files/product_with_category.php
     */
    public function testGetNonExistentUrlRewrite()
    {
        $productSku = 'p002';
        $urlPath = 'non-exist-product.html';

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $productRepository->get($productSku, false, null, true);

        /** @var UrlRewriteModel $urlRewriteModel */
        $urlRewriteModel = $this->objectManager->create(UrlRewriteModel::class);
        $urlRewriteModel->load($urlPath, 'request_path');

        $routeQuery = $this->getRouteQuery($urlPath);
        $response = $this->graphQlQueryWithResponseHeaders($routeQuery);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $response['headers']);
        $cacheId = $response['headers'][CacheIdCalculator::CACHE_ID_HEADER];

        // Verify we obtain a cache MISS the first time we search the cache using this X-Magento-Cache-Id
        $this->assertCacheMissAndReturnResponse($routeQuery, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);

        // Verify we obtain a cache HIT the second time around for this X-Magento-Cache-Id
        $this->assertCacheHitAndReturnResponse($routeQuery, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);
    }

    /**
     * Test for category entity
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/CatalogUrlRewrite/_files/product_with_category.php
     */
    public function testCategoryUrlResolver()
    {
        $productSku = 'p002';
        $categoryUrlPath = 'cat-1.html';
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get($productSku, false, null, true);
        $storeId = $product->getStoreId();

        /** @var  UrlFinderInterface $urlFinder */
        $urlFinder = $this->objectManager->get(UrlFinderInterface::class);
        $actualUrls = $urlFinder->findOneByData(
            [
                'request_path' => $categoryUrlPath,
                'store_id' => $storeId
            ]
        );
        $categoryId = $actualUrls->getEntityId();
        $categoryRepository = $this->objectManager->get(CategoryRepositoryInterface::class);
        $categoryRepository->get($categoryId);

        $query
            = <<<QUERY
{
    category(id:{$categoryId}) {
        url_key
        url_suffix
    }
}
QUERY;
        $response = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $response['headers']);
        $cacheId = $response['headers'][CacheIdCalculator::CACHE_ID_HEADER];

        // Verify we obtain a cache MISS the first time we search the cache using this X-Magento-Cache-Id
        $this->assertCacheMissAndReturnResponse($query, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);

        // Verify we obtain a cache HIT the second time around for this X-Magento-Cache-Id
        $this->assertCacheHitAndReturnResponse($query, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);
    }

    /**
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/Cms/_files/pages.php
     */
    public function testCMSPageUrlResolver()
    {
        /** @var \Magento\Cms\Model\Page $page */
        $page = $this->objectManager->get(\Magento\Cms\Model\Page::class);
        $page->load('page100');
        $page->getData();

        /** @var \Magento\CmsUrlRewrite\Model\CmsPageUrlPathGenerator $urlPathGenerator */
        $urlPathGenerator = $this->objectManager->get(\Magento\CmsUrlRewrite\Model\CmsPageUrlPathGenerator::class);

        /** @param \Magento\Cms\Api\Data\PageInterface $page */
        $targetPath = $urlPathGenerator->getCanonicalUrlPath($page);

        $routeQuery = $this->getRouteQuery($targetPath);
        $response = $this->graphQlQueryWithResponseHeaders($routeQuery);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $response['headers']);
        $cacheId = $response['headers'][CacheIdCalculator::CACHE_ID_HEADER];

        // Verify we obtain a cache MISS the first time we search the cache using this X-Magento-Cache-Id
        $this->assertCacheMissAndReturnResponse($routeQuery, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);

        // Verify we obtain a cache HIT the second time around for this X-Magento-Cache-Id
        $this->assertCacheHitAndReturnResponse($routeQuery, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);
    }

    /**
     * @param string $urlKey
     * @return string
     */
    public function getRouteQuery(string $urlKey): string
    {
        $routeQuery
            = <<<QUERY
{
  route(url:"{$urlKey}")
  {
    __typename
    ...on SimpleProduct {
      name
      sku
      relative_url
      redirect_code
      type
    }
    ...on CategoryTree {
        name
        uid
        relative_url
        redirect_code
        type
    }
    ...on CmsPage {
    	title
        url_key
        page_layout
        content
        content_heading
        relative_url
        redirect_code
        type
    }
  }
}
QUERY;
        return $routeQuery;
    }

    /**
     * @param string $productSku
     * @return string
     * @throws \Exception
     */
    public function getProductUrlKey(string $productSku): string
    {
        $query
            = <<<QUERY
{
    products(filter: {sku: {eq: "{$productSku}"}})
    {
        items {
               url_key
               url_suffix
            }
    }
}
QUERY;
        $response = $this->graphQlQuery($query);
        return $response['products']['items'][0]['url_key'] . $response['products']['items'][0]['url_suffix'];
    }

    /**
     * @param $productSku
     * @return UrlRewriteService
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getProductUrlRewriteData($productSku): UrlRewriteService
    {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get($productSku, false, null, true);
        $storeId = $product->getStoreId();

        $urlPath = $this->getProductUrlKey($productSku);

        /** @var  UrlFinderInterface $urlFinder */
        $urlFinder = $this->objectManager->get(UrlFinderInterface::class);
        /** @var UrlRewriteService $actualUrls */
        $actualUrls = $urlFinder->findOneByData(
            [
                'request_path' => $urlPath,
                'store_id' => $storeId
            ]
        );
        return $actualUrls;
    }

    /**
     * Test for url rewrite to clean cache on rewrites update
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/Catalog/_files/product_with_category.php
     * @magentoApiDataFixture Magento/Cms/_files/pages.php
     *
     * @dataProvider urlRewriteEntitiesDataProvider
     * @param string $requestPath
     * @throws AlreadyExistsException
     */
    public function testUrlRewriteCleansCacheOnChange(string $requestPath)
    {

        /** @var UrlRewriteResourceModel $urlRewriteResourceModel */
        $urlRewriteResourceModel = $this->objectManager->create(UrlRewriteResourceModel::class);
        $storeId = 1;
        $query = function ($requestUrl) {
            return <<<QUERY
{
  route(url:"{$requestUrl}")
  {
   relative_url
   type
   redirect_code
  }
}
QUERY;
        };

        // warming up route API response cache for entity and validate proper response
        $apiResponse = $this->graphQlQueryWithResponseHeaders($query($requestPath));
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $apiResponse['headers']);
        $cacheId = $apiResponse['headers'][CacheIdCalculator::CACHE_ID_HEADER];

        // Verify we obtain a cache MISS the first time we search the cache using this X-Magento-Cache-Id
        $this->assertCacheMissAndReturnResponse($query($requestPath), [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);

        // Verify we obtain a cache HIT the second time around for this X-Magento-Cache-Id
        $this->assertCacheHitAndReturnResponse($query($requestPath), [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);

        $this->assertEquals($requestPath, $apiResponse['body']['route']['relative_url']);

        $urlRewrite = $this->getUrlRewriteModelByRequestPath($requestPath, $storeId);

        // renaming entity request path and validating that API will not return cached response
        $urlRewrite->setRequestPath('test' . $requestPath);
        $urlRewriteResourceModel->save($urlRewrite);
        $apiResponse = $this->assertCacheMissAndReturnResponse(
            $query($requestPath),
            [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]
        );
        $this->assertNull($apiResponse['body']['route']);

        // rolling back changes
        $urlRewrite->setRequestPath($requestPath);
        $urlRewriteResourceModel->save($urlRewrite);
    }

    public static function urlRewriteEntitiesDataProvider(): array
    {
        return [
            [
                'simple-product-in-stock.html'
            ],
            [
                'category-1.html'
            ],
            [
                'page100'
            ]
        ];
    }

    /**
     * Test for custom url rewrite to clean cache on update combinations
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/Catalog/_files/product_with_category.php
     * @magentoApiDataFixture Magento/Cms/_files/pages.php
     *
     * @throws AlreadyExistsException
     */
    public function testUrlRewriteCleansCacheForCustomRewrites()
    {
        /** @var UrlRewriteResourceModel $urlRewriteResourceModel */
        $urlRewriteResourceModel = $this->objectManager->create(UrlRewriteResourceModel::class);
        $storeId = 1;
        $query = function ($requestUrl) {
            return <<<QUERY
{
  route(url:"{$requestUrl}")
  {
   relative_url
   type
   redirect_code
  }
}
QUERY;
        };

        $customRequestPath = 'test.html';
        $customSecondRequestPath = 'test2.html';
        $entitiesRequestPaths = [
            'simple-product-in-stock.html',
            'category-1.html',
            'page100'
        ];

        // create custom url rewrite
        $urlRewriteModel = $this->objectManager->create(UrlRewriteModel::class);
        $urlRewriteModel->setEntityType('custom')
            ->setRedirectType(302)
            ->setStoreId($storeId)
            ->setDescription(null)
            ->setIsAutogenerated(0);

        // create second custom url rewrite and target it to previous one to check
        // if proper final target url will be resolved
        $secondUrlRewriteModel = $this->objectManager->create(UrlRewriteModel::class);
        $secondUrlRewriteModel->setEntityType('custom')
            ->setRedirectType(302)
            ->setStoreId($storeId)
            ->setRequestPath($customSecondRequestPath)
            ->setTargetPath($customRequestPath)
            ->setDescription(null)
            ->setIsAutogenerated(0);
        $urlRewriteResourceModel->save($secondUrlRewriteModel);

        foreach ($entitiesRequestPaths as $entityRequestPath) {
            // updating custom rewrite for each entity
            $urlRewriteModel->setRequestPath($customRequestPath)
                ->setTargetPath($entityRequestPath);
            $urlRewriteResourceModel->save($urlRewriteModel);

            // confirm that API returns non-cached response for the first custom rewrite
            $apiResponse = $this->graphQlQueryWithResponseHeaders($query($customRequestPath));
            $this->assertEquals($entityRequestPath, $apiResponse['body']['route']['relative_url']);
            $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $apiResponse['headers']);
            $cacheId = $apiResponse['headers'][CacheIdCalculator::CACHE_ID_HEADER];

            // Verify we obtain a cache MISS the first time we search the cache using this X-Magento-Cache-Id
            $this->assertCacheMissAndReturnResponse(
                $query($customRequestPath),
                [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]
            );

            // Verify we obtain a cache HIT the second time around for this X-Magento-Cache-Id
            $this->assertCacheHitAndReturnResponse(
                $query($customRequestPath),
                [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]
            );

            // confirm that API returns non-cached response for the second custom rewrite
            $apiResponse = $this->graphQlQueryWithResponseHeaders($query($customSecondRequestPath));
            $this->assertEquals($entityRequestPath, $apiResponse['body']['route']['relative_url']);
            $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $apiResponse['headers']);
            $cacheId = $apiResponse['headers'][CacheIdCalculator::CACHE_ID_HEADER];

            // Verify we obtain a cache MISS the first time we search the cache using this X-Magento-Cache-Id
            $this->assertCacheMissAndReturnResponse(
                $query($customSecondRequestPath),
                [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]
            );

            // Verify we obtain a cache HIT the second time around for this X-Magento-Cache-Id
            $this->assertCacheHitAndReturnResponse(
                $query($customSecondRequestPath),
                [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]
            );
        }

        $urlRewriteResourceModel->delete($secondUrlRewriteModel);

        // delete custom rewrite and validate that API will not return cached response
        $urlRewriteResourceModel->delete($urlRewriteModel);
        $apiResponse = $this->assertCacheMissAndReturnResponse(
            $query($customRequestPath),
            [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]
        );
        $this->assertNull($apiResponse['body']['route']);
    }

    /**
     * Return UrlRewrite model instance by request_path
     *
     * @param string $requestPath
     * @param int $storeId
     * @return UrlRewriteModel
     */
    private function getUrlRewriteModelByRequestPath(string $requestPath, int $storeId): UrlRewriteModel
    {
        /** @var  UrlFinderInterface $urlFinder */
        $urlFinder = $this->objectManager->get(UrlFinderInterface::class);

        /** @var UrlRewriteService $urlRewriteService */
        $urlRewriteService = $urlFinder->findOneByData(
            [
                'request_path' => $requestPath,
                'store_id' => $storeId
            ]
        );

        /** @var UrlRewriteModel $urlRewrite */
        $urlRewrite = $this->objectManager->create(UrlRewriteModel::class);
        $urlRewrite->load($urlRewriteService->getUrlRewriteId());

        return $urlRewrite;
    }
}
