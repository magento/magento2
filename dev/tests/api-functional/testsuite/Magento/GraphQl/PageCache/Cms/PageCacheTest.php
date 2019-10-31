<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\PageCache\Cms;

use Magento\Cms\Model\GetPageByIdentifier;
use Magento\Cms\Model\PageRepository;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test the caching works properly for CMS Pages
 */
class PageCacheTest extends GraphQlAbstract
{
    /**
     * @var GetPageByIdentifier
     */
    private $pageByIdentifier;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->markTestSkipped(
            'This test will stay skipped until DEVOPS-4924 is resolved'
        );
        $this->pageByIdentifier = Bootstrap::getObjectManager()->get(GetPageByIdentifier::class);
    }

    /**
     * Test that X-Magento-Tags are correct
     *
     * @magentoApiDataFixture Magento/Cms/_files/pages.php
     */
    public function testCacheTagsHaveExpectedValue()
    {
        $pageIdentifier = 'page100';
        $page = $this->pageByIdentifier->execute($pageIdentifier, 0);
        $pageId = (int) $page->getId();

        $query = $this->getPageQuery($pageId);

        //cache-debug should be a MISS on first request
        $response = $this->graphQlQueryWithResponseHeaders($query);

        $this->assertArrayHasKey('X-Magento-Tags', $response['headers']);
        $actualTags = explode(',', $response['headers']['X-Magento-Tags']);
        $expectedTags = ["cms_p", "cms_p_{$pageId}", "FPC"];
        $this->assertEquals($expectedTags, $actualTags);
    }

    /**
     * Test the second request for the same page will return a cached result
     *
     * @magentoApiDataFixture Magento/Cms/_files/pages.php
     */
    public function testCacheIsUsedOnSecondRequest()
    {
        $pageIdentifier = 'page100';
        $page = $this->pageByIdentifier->execute($pageIdentifier, 0);
        $pageId = (int) $page->getId();

        $query = $this->getPageQuery($pageId);

        //cache-debug should be a MISS on first request
        $responseMiss = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey('X-Magento-Cache-Debug', $responseMiss['headers']);
        $this->assertEquals('MISS', $responseMiss['headers']['X-Magento-Cache-Debug']);

        //cache-debug should be a HIT on second request
        $responseHit = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey('X-Magento-Cache-Debug', $responseHit['headers']);
        $this->assertEquals('HIT', $responseHit['headers']['X-Magento-Cache-Debug']);
        //cached data should be correct
        $this->assertNotEmpty($responseHit['body']);
        $this->assertArrayNotHasKey('errors', $responseHit['body']);
        $pageData = $responseHit['body']['cmsPage'];
        $this->assertEquals('Cms Page 100', $pageData['title']);
    }

    /**
     * Test that cache is invalidated when page is updated
     *
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
        $this->assertEquals('MISS', $page100Miss['headers']['X-Magento-Cache-Debug']);
        $pageBlankMiss = $this->graphQlQueryWithResponseHeaders($pageBlankQuery);
        $this->assertEquals('MISS', $pageBlankMiss['headers']['X-Magento-Cache-Debug']);

        //cache-debug should be a HIT on second request
        $page100Hit = $this->graphQlQueryWithResponseHeaders($page100Query);
        $this->assertEquals('HIT', $page100Hit['headers']['X-Magento-Cache-Debug']);
        $pageBlankHit = $this->graphQlQueryWithResponseHeaders($pageBlankQuery);
        $this->assertEquals('HIT', $pageBlankHit['headers']['X-Magento-Cache-Debug']);

        $pageRepository = Bootstrap::getObjectManager()->get(PageRepository::class);
        $newPageContent = 'New page content for blank page.';
        $pageBlank->setContent($newPageContent);
        $pageRepository->save($pageBlank);

        //cache-debug should be a MISS after updating the page
        $pageBlankMiss = $this->graphQlQueryWithResponseHeaders($pageBlankQuery);
        $this->assertEquals('MISS', $pageBlankMiss['headers']['X-Magento-Cache-Debug']);
        $page100Hit = $this->graphQlQueryWithResponseHeaders($page100Query);
        $this->assertEquals('HIT', $page100Hit['headers']['X-Magento-Cache-Debug']);
        //updated page data should be correct
        $this->assertNotEmpty($pageBlankMiss['body']);
        $pageData = $pageBlankMiss['body']['cmsPage'];
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
