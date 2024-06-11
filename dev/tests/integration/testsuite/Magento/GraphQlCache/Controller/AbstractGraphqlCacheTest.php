<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Controller;

use Magento\Framework\App\DeploymentConfig\Reader;
use Magento\Framework\App\DeploymentConfig\Writer\PhpFormatter;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\Response\HttpInterface as HttpResponse;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Filesystem;
use Magento\Framework\Registry;
use Magento\GraphQl\Controller\GraphQl as GraphQlController;
use Magento\GraphQlCache\Model\CacheableQuery;
use Magento\GraphQlCache\Model\CacheId\CacheIdCalculator;
use Magento\PageCache\Model\Cache\Type as PageCache;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Abstract test class for Graphql cache tests
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractGraphqlCacheTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $envConfigPath;

    /**
     * @var Reader
     */
    private $envConfigReader;

    /**
     * @var PhpFormatter
     */
    private $formatter;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->enablePageCachePlugin();
        $this->enableCachebleQueryTestProxy();
    }

    /**
     * If the cache id salt didn't exist in env.php before a GraphQL request it gets added. To prevent test failures
     * due to a config getting changed (which is normally illegal), the salt needs to be removed from env.php after
     * a test if it wasn't there before.
     *
     * @see \Magento\TestFramework\Isolation\DeploymentConfig
     *
     * @inheritdoc
     */
    protected function runTest(): mixed
    {
        /** @var Reader $reader */
        if (!$this->envConfigPath) {
            /** @var ConfigFilePool $configFilePool */
            $configFilePool = $this->objectManager->get(ConfigFilePool::class);
            $this->envConfigPath = $configFilePool->getPath(ConfigFilePool::APP_ENV);
        }
        $this->envConfigReader = $this->envConfigReader ?: $this->objectManager->get(Reader::class);
        $initialConfig = $this->envConfigReader->load(ConfigFilePool::APP_ENV);

        try {
            return parent::runTest();
        } finally {
            $this->formatter = $this->formatter ?: new PhpFormatter();
            $this->filesystem = $this->filesystem ?: $this->objectManager->get(Filesystem::class);
            $cacheSaltPathChunks = explode('/', CacheIdCalculator::SALT_CONFIG_PATH);
            $currentConfig = $this->envConfigReader->load(ConfigFilePool::APP_ENV);
            $resetConfig = $this->resetAddedSection($initialConfig, $currentConfig, $cacheSaltPathChunks);
            $resetFileContents = $this->formatter->format($resetConfig);
            $directoryWrite = $this->filesystem->getDirectoryWrite(DirectoryList::CONFIG);
            $directoryWrite->writeFile($this->envConfigPath, $resetFileContents);
        }
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

    /**
     * Go over the current deployment config and unset a section that was not present in the pre-test deployment config
     *
     * @param array $initial
     * @param array $current
     * @param string[] $chunks
     * @return array
     */
    private function resetAddedSection(array $initial, array $current, array $chunks): array
    {
        if ($chunks) {
            $chunk = array_shift($chunks);
            if (!isset($initial[$chunk])) {
                if (isset($current[$chunk])) {
                    unset($current[$chunk]);
                }
            } elseif (isset($current[$chunk]) && is_array($initial[$chunk]) && is_array($current[$chunk])) {
                $current[$chunk] = $this->resetAddedSection($initial[$chunk], $current[$chunk], $chunks);
            }
        }
        return $current;
    }
}
