<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\PageCache\Cms;

use Magento\Cms\Model\GetPageByIdentifier;
use Magento\Cms\Model\PageRepository;
use Magento\GraphQl\PageCache\GraphQLPageCacheAbstract;
use Magento\GraphQlCache\Model\CacheId\CacheIdCalculator;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test the caching works properly for CMS Pages
 */
class PageCacheTest extends GraphQLPageCacheAbstract
{
    /**
     * @var GetPageByIdentifier
     */
    private $pageByIdentifier;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->pageByIdentifier = Bootstrap::getObjectManager()->get(GetPageByIdentifier::class);
    }

    /**
     * Test the second request for the same page will return a cached result
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/Cms/_files/pages.php
     */
    public function testCacheIsUsedOnSecondRequest()
    {
        $pageIdentifier = 'page100';
        $page = $this->pageByIdentifier->execute($pageIdentifier, 0);
        $pageId = (int) $page->getId();

        $query = $this->getPageQuery($pageId);

        // Obtain the X-Magento-Cache-Id from the response which will be used as the cache key
        $response = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $response['headers']);
        $cacheId = $response['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS the first time we search the cache using this X-Magento-Cache-Id
        $this->assertCacheMissAndReturnResponse($query, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);
        // Verify we obtain a cache HIT the second time we search the cache using this X-Magento-Cache-Id
        $this->assertCacheHitAndReturnResponse($query, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);

        //cached data should be correct
        $this->assertNotEmpty($response['body']);
        $this->assertArrayNotHasKey('errors', $response['body']);
        $pageData = $response['body']['cmsPage'];
        $this->assertEquals('Cms Page 100', $pageData['title']);
    }

    /**
     * Test that cache is invalidated when page is updated
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/Cms/_files/pages.php
     */
    public function testCacheIsInvalidatedOnPageUpdate()
    {
        $page100Identifier = 'page100';
        $page100 = $this->pageByIdentifier->execute($page100Identifier, 0);
        $page100Id = (int) $page100->getId();
        $pageBlankIdentifier = 'page_design_blank';
        $pageBlank = $this->pageByIdentifier->execute($pageBlankIdentifier, 0);
        $pageBlankId = (int) $pageBlank->getId();

        $page100Query = $this->getPageQuery($page100Id);
        $pageBlankQuery = $this->getPageQuery($pageBlankId);

        //cache-debug should be a MISS on first request
        $page100 = $this->graphQlQueryWithResponseHeaders($page100Query);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $page100['headers']);
        $cacheId = $page100['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS the first time we search the cache using this X-Magento-Cache-Id
        $this->assertCacheMissAndReturnResponse($page100Query, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);
        //cache-debug should be a HIT on second request
        $this->assertCacheHitAndReturnResponse($page100Query, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);

        $pageBlankCache = $this->graphQlQueryWithResponseHeaders($pageBlankQuery);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $pageBlankCache['headers']);
        $cacheId = $pageBlankCache['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS the first time we search the cache using this X-Magento-Cache-Id
        $this->assertCacheMissAndReturnResponse($pageBlankQuery, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);
        //cache-debug should be a HIT on second request
        $this->assertCacheHitAndReturnResponse($pageBlankQuery, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);

        $pageRepository = Bootstrap::getObjectManager()->get(PageRepository::class);
        $newPageContent = 'New page content for blank page.';
        $pageBlank->setContent($newPageContent);
        $pageRepository->save($pageBlank);

        //cache-debug should be a MISS after updating the page
        $pageBlank = $this->graphQlQueryWithResponseHeaders($pageBlankQuery);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $pageBlank['headers']);
        $cacheId = $pageBlank['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS the first time we search the cache using this X-Magento-Cache-Id
        $this->assertCacheMissAndReturnResponse($pageBlankQuery, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);

        $page100 = $this->graphQlQueryWithResponseHeaders($page100Query);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $page100['headers']);
        $cacheId = $page100['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertCacheHitAndReturnResponse($page100Query, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);

        //updated page data should be correct
        $this->assertNotEmpty($pageBlankCache['body']);
        $pageData = $pageBlankCache['body']['cmsPage'];
        $this->assertArrayNotHasKey('errors', $pageBlankCache['body']);
        $this->assertEquals('Cms Page Design Blank', $pageData['title']);
        $this->assertEquals($newPageContent, $pageData['content']);
    }

    /**
     * Get page query
     *
     * @param int $pageId
     * @return string
     */
    private function getPageQuery(int $pageId): string
    {
        $query = <<<QUERY
{
    cmsPage(id: $pageId) {
        title
   	    url_key
        content
    }
}
QUERY;
        return $query;
    }
}
