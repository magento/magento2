<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CmsGraphQl\Test\Integration\Model\Resolver;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Model\PageRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Cache\StateInterface as CacheStateInterface;
use Magento\Framework\App\Cache\Type\FrontendPool;
use Magento\Framework\ObjectManagerInterface;
use Magento\GraphQl\Service\GraphQlRequest;
use Magento\GraphQlResolverCache\Model\Plugin\Resolver\Cache as ResolverResultCachePlugin;
use Magento\GraphQlResolverCache\Model\Resolver\Result\Type as GraphQlResolverCache;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test GraphQl Resolver cache saves and loads properly
 * @magentoAppArea graphql
 */
class PageTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var GraphQlRequest
     */
    private $graphQlRequest;

    /**
     * @var ResolverResultCachePlugin
     */
    private $originalResolverResultCachePlugin;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var PageRepository
     */
    private $pageRepository;

    /**
     * @var CacheStateInterface
     */
    private $cacheState;

    /**
     * @var bool
     */
    private $originalCacheStateEnabledStatus;

    protected function setUp(): void
    {
        $this->objectManager = $objectManager = Bootstrap::getObjectManager();
        $this->graphQlRequest = $objectManager->create(GraphQlRequest::class);
        $this->searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
        $this->pageRepository = $objectManager->get(PageRepository::class);
        $this->originalResolverResultCachePlugin = $objectManager->get(ResolverResultCachePlugin::class);

        $this->cacheState = $objectManager->get(CacheStateInterface::class);
        $this->originalCacheStateEnabledStatus = $this->cacheState->isEnabled(GraphQlResolverCache::TYPE_IDENTIFIER);
        $this->cacheState->setEnabled(GraphQlResolverCache::TYPE_IDENTIFIER, true);
    }

    protected function tearDown(): void
    {
        $objectManager = $this->objectManager;

        // reset to original resolver plugin
        $objectManager->addSharedInstance($this->originalResolverResultCachePlugin, ResolverResultCachePlugin::class);

        // clean graphql resolver cache and reset to original enablement status
        $objectManager->get(GraphQlResolverCache::class)->clean();
        $this->cacheState->setEnabled(GraphQlResolverCache::TYPE_IDENTIFIER, $this->originalCacheStateEnabledStatus);
    }

    /**
     * Test that result can be loaded continuously after saving once when passing the same arguments
     *
     * @magentoDataFixture Magento/Cms/Fixtures/page_list.php
     * @return void
     */
    public function testResultIsLoadedMultipleTimesAfterOnlyBeingSavedOnce()
    {
        $objectManager = $this->objectManager;
        $page = $this->getPageByTitle('Page with 1column layout');

        $frontendPool = $objectManager->get(FrontendPool::class);

        $cacheProxy = $this->getMockBuilder(GraphQlResolverCache::class)
            ->enableProxyingToOriginalMethods()
            ->setConstructorArgs([
                $frontendPool
            ])
            ->getMock();

        // assert cache proxy calls load at least once for the same CMS page query
        $cacheProxy
            ->expects($this->atLeastOnce())
            ->method('load');

        // assert save is called at most once for the same CMS page query
        $cacheProxy
            ->expects($this->once())
            ->method('save');

        $resolverPluginWithCacheProxy = $objectManager->create(ResolverResultCachePlugin::class, [
            'graphQlResolverCache' => $cacheProxy,
        ]);

        // override resolver plugin with plugin instance containing cache proxy class
        $objectManager->addSharedInstance($resolverPluginWithCacheProxy, ResolverResultCachePlugin::class);

        $query = $this->getQuery($page->getIdentifier());

        // send request and assert save is called
        $this->graphQlRequest->send($query);

        // send again and assert save is not called (i.e. result is loaded from resolver cache)
        $this->graphQlRequest->send($query);

        // send again with whitespace appended and assert save is not called (i.e. result is loaded from resolver cache)
        $this->graphQlRequest->send($query . '   ');

        // send again with a different field and assert save is not called (i.e. result is loaded from resolver cache)
        $differentQuery = $this->getQuery($page->getIdentifier(), ['meta_title']);
        $this->graphQlRequest->send($differentQuery);
    }

    /**
     * Test that resolver plugin does not call GraphQlResolverCache's save or load methods when it is disabled
     *
     * @magentoDataFixture Magento/Cms/Fixtures/page_list.php
     * @return void
     */
    public function testNeitherSaveNorLoadAreCalledWhenResolverCacheIsDisabled()
    {
        $objectManager = $this->objectManager;
        $page = $this->getPageByTitle('Page with 1column layout');

        // disable graphql resolver cache
        $this->cacheState->setEnabled(GraphQlResolverCache::TYPE_IDENTIFIER, false);

        $frontendPool = $objectManager->get(FrontendPool::class);

        $cacheProxy = $this->getMockBuilder(GraphQlResolverCache::class)
            ->enableProxyingToOriginalMethods()
            ->setConstructorArgs([
                $frontendPool
            ])
            ->getMock();

        // assert cache proxy never calls load
        $cacheProxy
            ->expects($this->never())
            ->method('load');

        // assert save is also never called
        $cacheProxy
            ->expects($this->never())
            ->method('save');

        $resolverPluginWithCacheProxy = $objectManager->create(ResolverResultCachePlugin::class, [
            'graphQlResolverCache' => $cacheProxy,
        ]);

        // override resolver plugin with plugin instance containing cache proxy class
        $objectManager->addSharedInstance($resolverPluginWithCacheProxy, ResolverResultCachePlugin::class);

        $query = $this->getQuery($page->getIdentifier());

        // send request multiple times and assert neither save nor load are called
        $this->graphQlRequest->send($query);
        $this->graphQlRequest->send($query);
    }

    public function testSaveIsNeverCalledWhenMissingRequiredArgumentInQuery()
    {
        $objectManager = $this->objectManager;

        $frontendPool = $objectManager->get(FrontendPool::class);

        $cacheProxy = $this->getMockBuilder(GraphQlResolverCache::class)
            ->enableProxyingToOriginalMethods()
            ->setConstructorArgs([
                $frontendPool
            ])
            ->getMock();

        // assert cache proxy never calls save
        $cacheProxy
            ->expects($this->never())
            ->method('save');

        $resolverPluginWithCacheProxy = $objectManager->create(ResolverResultCachePlugin::class, [
            'graphQlResolverCache' => $cacheProxy,
        ]);

        // override resolver plugin with plugin instance containing cache proxy class
        $objectManager->addSharedInstance($resolverPluginWithCacheProxy, ResolverResultCachePlugin::class);

        $query = <<<QUERY
{
  cmsPage {
    title
  }
}
QUERY;

        // send request multiple times and assert save is never called
        $this->graphQlRequest->send($query);
        $this->graphQlRequest->send($query);
    }

    private function getQuery(string $identifier, array $fields = ['title']): string
    {
        $fields = implode(PHP_EOL, $fields);

        return <<<QUERY
{
  cmsPage(identifier: "$identifier") {
    $fields
  }
}
QUERY;
    }

    private function getPageByTitle(string $title): PageInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('title', $title)
            ->create();

        $pages = $this->pageRepository->getList($searchCriteria)->getItems();

        /** @var PageInterface $page */
        $page = reset($pages);

        return $page;
    }
}
