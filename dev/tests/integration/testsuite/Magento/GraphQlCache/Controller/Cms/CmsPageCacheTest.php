<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Controller\Cms;

use Magento\Cms\Model\GetPageByIdentifier;
use Magento\Framework\App\Request\Http;
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
     * Test that the correct cache tags get added to request for cmsPage query
     *
     * @magentoDataFixture Magento/Cms/_files/pages.php
     */
    public function testToCheckCmsPageRequestCacheTags(): void
    {
        $cmsPage = $this->objectManager->get(GetPageByIdentifier::class)->execute('page100', 0);
        $pageId = $cmsPage->getId();

        $query =
            <<<QUERY
        {
         cmsPage(id: $pageId) {
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

        $this->request->setPathInfo('/graphql');
        $this->request->setMethod('GET');
        $this->request->setQueryValue('query', $query);
        $response = $this->graphqlController->dispatch($this->request);
        $this->assertEquals('MISS', $response->getHeader('X-Magento-Cache-Debug')->getFieldValue());
        $requestedCacheTags = explode(',', $response->getHeader('X-Magento-Tags')->getFieldValue());
        $expectedCacheTags = ['cms_p', 'cms_p_' .$pageId , 'FPC'];
        $this->assertEquals($expectedCacheTags, $requestedCacheTags);
    }
}
