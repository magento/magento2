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
     * Test that X-Magento-Tags are correct
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/Cms/_files/pages.php
     */
    public function testCacheTagsHaveExpectedValue()
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

        print_r("Debug value PageCacheTest testCacheTagsHaveExpectedValue\n");
        $json_response = json_encode($response, JSON_PRETTY_PRINT);
        print_r($json_response);
        print_r("\n end \n");
        print_r("Debug value End of testCacheTagsHaveExpectedValue\n");

        $this->assertArrayHasKey('X-Magento-Tags', $response['headers']);
        $actualTags = explode(',', $response['headers']['X-Magento-Tags']);
        $expectedTags = ["cms_p", "cms_p_{$pageId}", "FPC"];
        $this->assertEquals($expectedTags, $actualTags);
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

        print_r("Debug value MISS PageCacheTest testCacheIsUsedOnSecondRequest\n");
        $json_response = json_encode($response, JSON_PRETTY_PRINT);
        print_r($json_response);
        print_r("\n end \n");
        print_r("Debug value End MISS of testCacheIsUsedOnSecondRequest\n");

        // Verify we obtain a cache MISS the first time we search the cache using this X-Magento-Cache-Id
        $this->assertCacheMissAndReturnResponse($query, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);

        // Verify we obtain a cache HIT the second time around for this X-Magento-Cache-Id
        $responseHit = $this->graphQlQueryWithResponseHeaders($query);

        print_r("Debug value HIT PageCacheTest testCacheIsUsedOnSecondRequest\n");
        $json_response = json_encode($responseHit, JSON_PRETTY_PRINT);
        print_r($json_response);
        print_r("\n end \n");
        print_r("Debug value End HIT of testCacheIsUsedOnSecondRequest\n");

        $this->assertArrayHasKey('X-Magento-Cache-Debug', $response['headers']);
        $this->assertEquals('HIT', $response['headers']['X-Magento-Cache-Debug']);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $response['headers']);
        $this->assertEquals($cacheId, $response['headers'][CacheIdCalculator::CACHE_ID_HEADER]);

        //cached data should be correct
        $this->assertNotEmpty($responseHit['body']);
        $this->assertArrayNotHasKey('errors', $responseHit['body']);
        $pageData = $responseHit['body']['cmsPage'];
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
        $page100Miss = $this->graphQlQueryWithResponseHeaders($page100Query);

        print_r("Debug value Page100 MISS PageCacheTest testCacheIsInvalidatedOnPageUpdate\n");
        $json_response = json_encode($page100Miss, JSON_PRETTY_PRINT);
        print_r($json_response);
        print_r("\n end \n");
        print_r("Debug value End of testCacheIsInvalidatedOnPageUpdate\n");

        $this->assertEquals('MISS', $page100Miss['headers']['X-Magento-Cache-Debug']);
        $pageBlankMiss = $this->graphQlQueryWithResponseHeaders($pageBlankQuery);

        print_r("Debug value PageBlank MISS PageCacheTest testCacheIsInvalidatedOnPageUpdate\n");
        $json_response = json_encode($pageBlankMiss, JSON_PRETTY_PRINT);
        print_r($json_response);
        print_r("\n end \n");
        print_r("Debug value End of testCacheIsInvalidatedOnPageUpdate\n");

        $this->assertEquals('MISS', $pageBlankMiss['headers']['X-Magento-Cache-Debug']);

        //cache-debug should be a HIT on second request
        $page100Hit = $this->graphQlQueryWithResponseHeaders($page100Query);

        print_r("Debug value Page100 HIT PageCacheTest testCacheIsInvalidatedOnPageUpdate\n");
        $json_response = json_encode($page100Hit, JSON_PRETTY_PRINT);
        print_r($json_response);
        print_r("\n end \n");
        print_r("Debug value End HIT of testCacheIsInvalidatedOnPageUpdate\n");

        $this->assertEquals('HIT', $page100Hit['headers']['X-Magento-Cache-Debug']);
        $pageBlankHit = $this->graphQlQueryWithResponseHeaders($pageBlankQuery);

        print_r("Debug value Page100 MISS PageCacheTest testCacheIsInvalidatedOnPageUpdate\n");
        $json_response = json_encode($pageBlankHit, JSON_PRETTY_PRINT);
        print_r($json_response);
        print_r("\n end \n");
        print_r("Debug value pageBlankHit End of testCacheIsInvalidatedOnPageUpdate\n");

        $this->assertEquals('HIT', $pageBlankHit['headers']['X-Magento-Cache-Debug']);

        $pageRepository = Bootstrap::getObjectManager()->get(PageRepository::class);
        $newPageContent = 'New page content for blank page.';
        $pageBlank->setContent($newPageContent);
        $pageRepository->save($pageBlank);

        //cache-debug should be a MISS after updating the page
        $pageBlankMiss = $this->graphQlQueryWithResponseHeaders($pageBlankQuery);

        print_r("Debug value Page0 MISS PageCacheTest testCacheIsInvalidatedOnPageUpdate\n");
        $json_response = json_encode($pageBlankMiss, JSON_PRETTY_PRINT);
        print_r($json_response);
        print_r("\n end \n");
        print_r("Debug value End of testCacheIsInvalidatedOnPageUpdate\n");

        $this->assertEquals('MISS', $pageBlankMiss['headers']['X-Magento-Cache-Debug']);
        $page100Hit = $this->graphQlQueryWithResponseHeaders($page100Query);

        print_r("Debug value Page100 HIT PageCacheTest testCacheIsInvalidatedOnPageUpdate\n");
        $json_response = json_encode($page100Hit, JSON_PRETTY_PRINT);
        print_r($json_response);
        print_r("\n end \n");
        print_r("Debug value End of testCacheIsInvalidatedOnPageUpdate\n");

        $this->assertEquals('HIT', $page100Hit['headers']['X-Magento-Cache-Debug']);
        //updated page data should be correct
        $this->assertNotEmpty($pageBlankMiss['body']);
        $pageData = $pageBlankMiss['body']['cmsPage'];

        print_r($pageData."\n");

        $this->assertArrayNotHasKey('errors', $pageBlankMiss['body']);
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
