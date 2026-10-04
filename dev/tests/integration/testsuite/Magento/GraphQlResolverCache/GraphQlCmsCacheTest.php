<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQlResolverCache;

use Magento\GraphQl\Service\GraphQlRequest;
use Magento\GraphQlResolverCache\Model\Resolver\Result\Type;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Test\Fixture\Page as PageFixture;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Verifies CMS resolver caching through a real GraphQL request.
 *
 * @magentoAppArea graphql
 * @magentoAppIsolation enabled
 */
class GraphQlCmsCacheTest extends TestCase
{
    public function testCmsPageQueryCanBeServedRepeatedlyFromResolverCache(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $request = $objectManager->get(GraphQlRequest::class);
        $cache = $objectManager->get(Type::class);
        $cache->clean();
        $query = '{ cmsPage(identifier: "home") { identifier title url_key } }';

        try {
            $first = $request->send($query);
            $this->assertSame(200, $first->getHttpResponseCode());
            $this->assertStringNotContainsString('errors', strtolower((string) $first->getBody()));

            $second = $request->send($query);
            $this->assertSame(200, $second->getHttpResponseCode());
            $this->assertSame((string) $first->getBody(), (string) $second->getBody());
        } finally {
            $cache->clean();
        }
    }

    #[
        DataFixture(
            PageFixture::class,
            [
                'identifier' => 'graphql_cache_page',
                'title' => 'GraphQL Cache Page',
                'content' => '<p>GraphQL cache page content</p>',
                'active' => true,
                'page_layout' => '1column',
            ],
            'child_page'
        )
    ]
    public function testCmsPageUpdateInvalidatesResolverCache(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $request = $objectManager->get(GraphQlRequest::class);
        $cache = $objectManager->get(Type::class);
        $repository = $objectManager->get(PageRepositoryInterface::class);
        $cache->clean();
        $query = '{ cmsPage(identifier: "graphql_cache_page") { identifier title content } }';

        try {
            $first = $request->send($query);
            $this->assertStringContainsString('GraphQL Cache Page', (string) $first->getBody());
            $collection = $objectManager->get(CollectionFactory::class)->create();
            $page = $collection->addFieldToFilter('identifier', 'graphql_cache_page')->getFirstItem();
            $this->assertNotEmpty($page->getId());
            $page->setTitle('GraphQL Cache Page Updated');
            $repository->save($page);
            $updated = $request->send($query);
            $this->assertStringContainsString('GraphQL Cache Page Updated', (string) $updated->getBody());
        } finally {
            $cache->clean();
        }
    }
}
