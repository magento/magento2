<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Controller\UrlRewrite;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\GetPageByIdentifierInterface;
use Magento\GraphQl\Controller\GraphQl;
use Magento\GraphQlCache\Controller\AbstractGraphqlCacheTest;

/**
 * Test caching works for cmsPage UrlResolver
 *
 * @magentoAppArea graphql
 * @magentoCache full_page enabled
 * @magentoDbIsolation disabled
 */
class CmsPageUrlResolverCacheTest extends AbstractGraphqlCacheTest
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
     * @magentoDataFixture Magento/Cms/_files/pages.php
     */
    public function testCmsUrlResolverRequestHasCorrectTags()
    {
        /** @var GetPageByIdentifierInterface $page */
        $page = $this->objectManager->get(GetPageByIdentifierInterface::class);
        /** @var PageInterface $cmsPage */
        $cmsPage = $page->execute('page100', 0);
        $cmsPageId = $cmsPage->getId();
        $requestPath = $cmsPage->getIdentifier();
        $query
            = <<<QUERY
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
        $request = $this->prepareRequest($query);
        /** @var \Magento\Framework\App\Response\Http $response */
        $response = $this->graphqlController->dispatch($request);
        $this->assertEquals('MISS', $response->getHeader('X-Magento-Cache-Debug')->getFieldValue());
        $expectedCacheTags = ['cms_p','cms_p_' . $cmsPageId,'FPC'];
        $rawActualCacheTags = $response->getHeader('X-Magento-Tags')->getFieldValue();
        $actualCacheTags = explode(',', $rawActualCacheTags);
        $this->assertEquals($expectedCacheTags, $actualCacheTags);
    }
}
