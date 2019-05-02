<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Controller\Cms;

use Magento\Cms\Model\GetPageByIdentifier;
use Magento\GraphQl\Controller\GraphQl;
use Magento\GraphQlCache\Controller\AbstractGraphqlCacheTest;

/**
 * Test caching works for CMS page
 *
 * @magentoAppArea graphql
 * @magentoCache full_page enabled
 * @magentoDbIsolation disabled
 *
 */
class CmsPageCacheTest extends AbstractGraphqlCacheTest
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
        $this->graphqlController = $this->objectManager->get(\Magento\GraphQl\Controller\GraphQl::class);
    }

    /**
     * Test that the correct cache tags get added to request for cmsPage query
     *
     * @magentoDataFixture Magento/Cms/_files/pages.php
     */
    public function testToCheckCmsPageRequestCacheTags(): void
    {
        $cmsPage100 = $this->objectManager->get(GetPageByIdentifier::class)->execute('page100', 0);
        $pageId100 = $cmsPage100->getId();

        $cmsPageBlank = $this->objectManager->get(GetPageByIdentifier::class)->execute('page_design_blank', 0);
        $pageIdBlank = $cmsPageBlank->getId();

        $queryCmsPage100 =
            <<<QUERY
        {
         cmsPage(id: $pageId100) {
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

        $queryCmsPageBlank =
            <<<QUERY
        {
         cmsPage(id: $pageIdBlank) {
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

        $request = $this->prepareRequest($queryCmsPage100);
        $response = $this->graphqlController->dispatch($request);
        $this->assertEquals(
            'MISS',
            $response->getHeader('X-Magento-Cache-Debug')->getFieldValue(),
            "expected MISS on page page100 id {$queryCmsPage100}"
        );
        $requestedCacheTags = explode(',', $response->getHeader('X-Magento-Tags')->getFieldValue());
        $expectedCacheTags = ['cms_p', 'cms_p_' .$pageId100 , 'FPC'];
        $this->assertEquals($expectedCacheTags, $requestedCacheTags);
        
        $request = $this->prepareRequest($queryCmsPageBlank);
        $response = $this->graphqlController->dispatch($request);
        $this->assertEquals(
            'MISS',
            $response->getHeader('X-Magento-Cache-Debug')->getFieldValue(),
            "expected MISS on page pageBlank dsdss id {$pageIdBlank}"
        );
        $requestedCacheTags = explode(',', $response->getHeader('X-Magento-Tags')->getFieldValue());
        $expectedCacheTags = ['cms_p', 'cms_p_' .$pageIdBlank , 'FPC'];
        $this->assertEquals($expectedCacheTags, $requestedCacheTags);

        $request = $this->prepareRequest($queryCmsPage100);
        $response = $this->graphqlController->dispatch($request);
        $this->assertEquals(
            'HIT',
            $response->getHeader('X-Magento-Cache-Debug')->getFieldValue(),
            "expected HIT on page page100 id {$queryCmsPage100}"
        );
        $requestedCacheTags = explode(',', $response->getHeader('X-Magento-Tags')->getFieldValue());
        $expectedCacheTags = ['cms_p', 'cms_p_' .$pageId100 , 'FPC'];
        $this->assertEquals($expectedCacheTags, $requestedCacheTags);

        $request = $this->prepareRequest($queryCmsPageBlank);
        $response = $this->graphqlController->dispatch($request);
        $this->assertEquals(
            'HIT',
            $response->getHeader('X-Magento-Cache-Debug')->getFieldValue(),
            "expected HIT on page pageBlank id {$pageIdBlank}"
        );
        $requestedCacheTags = explode(',', $response->getHeader('X-Magento-Tags')->getFieldValue());
        $expectedCacheTags = ['cms_p', 'cms_p_' .$pageIdBlank , 'FPC'];
        $this->assertEquals($expectedCacheTags, $requestedCacheTags);
    }
}
