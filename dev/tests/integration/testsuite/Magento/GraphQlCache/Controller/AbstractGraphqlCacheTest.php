<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Controller;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\Response\HttpInterface as HttpResponse;
use Magento\Framework\Registry;
use Magento\GraphQl\Controller\GraphQl as GraphQlController;
use Magento\GraphQlCache\Model\CacheableQuery;
use Magento\PageCache\Model\Cache\Type as PageCache;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Abstract test class for Graphql cache tests
 */
abstract class AbstractGraphqlCacheTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->enablePageCachePlugin();
        $this->enableCachebleQueryTestProxy();
    }

    protected function tearDown(): void
    {
        $this->disableCacheableQueryTestProxy();
        $this->disablePageCachePlugin();
        $this->flushPageCache();
    }

    protected function enablePageCachePlugin(): void
    {
        /** @var  $registry Registry */
        $registry = $this->objectManager->get(Registry::class);
        $registry->register('use_page_cache_plugin', true, true);
    }

    protected function disablePageCachePlugin(): void
    {
        /** @var  $registry Registry */
        $registry = $this->objectManager->get(Registry::class);
        $registry->unregister('use_page_cache_plugin');
    }

    protected function flushPageCache(): void
    {
        /** @var PageCache $fullPageCache */
        $fullPageCache = $this->objectManager->get(PageCache::class);
        $fullPageCache->clean();
    }

    /**
     * Regarding the SuppressWarnings annotation below: the nested class below triggers a false rule match.
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    private function enableCachebleQueryTestProxy(): void
    {
        $cacheableQueryProxy = new class($this->objectManager) extends CacheableQuery {
            /** @var CacheableQuery */
            private $delegate;

            public function __construct(ObjectManager $objectManager)
            {
                $this->reset($objectManager);
            }

            public function reset(ObjectManager $objectManager): void
            {
                $this->delegate = $objectManager->create(CacheableQuery::class);
            }

            public function getCacheTags(): array
            {
                return $this->delegate->getCacheTags();
            }

            public function addCacheTags(array $cacheTags): void
            {
                $this->delegate->addCacheTags($cacheTags);
            }

            public function isCacheable(): bool
            {
                return $this->delegate->isCacheable();
            }

            public function setCacheValidity(bool $cacheable): void
            {
                $this->delegate->setCacheValidity($cacheable);
            }

            public function shouldPopulateCacheHeadersWithTags(): bool
            {
                return $this->delegate->shouldPopulateCacheHeadersWithTags();
            }
        };
        $this->objectManager->addSharedInstance($cacheableQueryProxy, CacheableQuery::class);
    }

    private function disableCacheableQueryTestProxy(): void
    {
        $this->resetQueryCacheTags();
        $this->objectManager->removeSharedInstance(CacheableQuery::class);
    }

    protected function resetQueryCacheTags(): void
    {
        $this->objectManager->get(CacheableQuery::class)->reset($this->objectManager);
    }

    protected function dispatchGraphQlGETRequest(array $queryParams): HttpResponse
    {
        $this->resetQueryCacheTags();

        /** @var HttpRequest $request */
        $request = $this->objectManager->get(HttpRequest::class);
        $request->setPathInfo('/graphql');
        $request->setMethod('GET');
        $request->setParams($queryParams);

        // required for \Magento\Framework\App\PageCache\Identifier to generate the correct cache key
        $request->setUri(implode('?', [$request->getPathInfo(), http_build_query($queryParams)]));

        return $this->objectManager->create(GraphQlController::class)->dispatch($request);
    }
}
