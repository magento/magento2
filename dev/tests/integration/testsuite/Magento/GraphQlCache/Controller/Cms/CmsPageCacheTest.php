<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Controller\Cms;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Model\GetPageByIdentifier;
use Magento\Cms\Model\PageRepository;
use Magento\Framework\App\Response\HttpInterface as HttpResponse;
use Magento\GraphQlCache\Controller\AbstractGraphqlCacheTest;

/**
 * Test caching works for CMS pages
 *
 * @magentoAppArea graphql
 * @magentoCache full_page enabled
 * @magentoDbIsolation disabled
 *
 */
class CmsPageCacheTest extends AbstractGraphqlCacheTest
{
    private function assertPageCacheMissWithTagsForCmsPage(string $pageId, string $name, HttpResponse $response): void
    {
        $this->assertEquals(
            'MISS',
            $response->getHeader('X-Magento-Cache-Debug')->getFieldValue(),
            "expected MISS on page {$name} id {$pageId}"
        );
        $this->assertCmsPageCacheTags($pageId, $response);
    }

    private function assertPageCacheHitWithTagsForCmsPage(string $pageId, string $name, HttpResponse $response): void
    {
        $this->assertEquals(
            'HIT',
            $response->getHeader('X-Magento-Cache-Debug')->getFieldValue(),
            "expected HIT on page {$name} id {$pageId}"
        );
        $this->assertCmsPageCacheTags($pageId, $response);
    }
    
    private function assertCmsPageCacheTags(string $pageId, HttpResponse $response): void
    {
        $requestedCacheTags = explode(',', $response->getHeader('X-Magento-Tags')->getFieldValue());
        $expectedCacheTags  = ['cms_p', 'cms_p_' . $pageId, 'FPC'];
        $this->assertEquals($expectedCacheTags, $requestedCacheTags);
    }

    private function buildQuery(string $id): string
    {
        $queryCmsPage = <<<QUERY
        {
         cmsPage(id: $id) {
                   url_key
                   title
                   content
                   content_heading
                   page_layout
                   meta_title
                   meta_description
                   meta_keywords
                   }
         }
QUERY;
        return $queryCmsPage;
    }

    private function updateCmsPageTitle(string $pageId100, string $newTitle): void
    {
        /** @var PageRepository $pageRepository */
        $pageRepository = $this->objectManager->get(PageRepository::class);
        $page           = $pageRepository->getById($pageId100);
        $page->setTitle($newTitle);
        $pageRepository->save($page);
    }

    /**
     * @magentoDataFixture Magento/Cms/_files/pages.php
     */
    public function testCmsPageRequestCacheTags(): void
    {
        /** @var PageInterface $cmsPage100 */
        $cmsPage100 = $this->objectManager->get(GetPageByIdentifier::class)->execute('page100', 0);
        $pageId100  = (string) $cmsPage100->getId();

        /** @var PageInterface $cmsPageBlank */
        $cmsPageBlank = $this->objectManager->get(GetPageByIdentifier::class)->execute('page_design_blank', 0);
        $pageIdBlank  = (string) $cmsPageBlank->getId();

        $queryCmsPage100   = $this->buildQuery($pageId100);
        $queryCmsPageBlank = $this->buildQuery($pageIdBlank);

        // check to see that the first entity gets a MISS when called the first time
        $response = $this->dispatchGraphQlGETRequest(['query' => $queryCmsPage100]);
        $this->assertPageCacheMissWithTagsForCmsPage($pageId100, 'page100', $response);

        // check to see that the second entity gets a MISS when called the first time
        $response = $this->dispatchGraphQlGETRequest(['query' => $queryCmsPageBlank]);
        $this->assertPageCacheMissWithTagsForCmsPage($pageIdBlank, 'pageBlank', $response);

        // check to see that the first entity gets a HIT when called the second time
        $response = $this->dispatchGraphQlGETRequest(['query' => $queryCmsPage100]);
        $this->assertPageCacheHitWithTagsForCmsPage($pageId100, 'page100', $response);

        // check to see that the second entity gets a HIT when called the second time
        $response = $this->dispatchGraphQlGETRequest(['query' => $queryCmsPageBlank]);
        $this->assertPageCacheHitWithTagsForCmsPage($pageIdBlank, 'pageBlank', $response);

        // invalidate first entity
        $this->updateCmsPageTitle($pageId100, 'something else that causes invalidation');

        // check to see that the second entity gets a HIT to confirm only the first was invalidated
        $response = $this->dispatchGraphQlGETRequest(['query' => $queryCmsPageBlank]);
        $this->assertPageCacheHitWithTagsForCmsPage($pageIdBlank, 'pageBlank', $response);

        // check to see that the first entity gets a MISS because it was invalidated
        $response = $this->dispatchGraphQlGETRequest(['query' => $queryCmsPage100]);
        $this->assertPageCacheMissWithTagsForCmsPage($pageId100, 'page100', $response);
    }
}
