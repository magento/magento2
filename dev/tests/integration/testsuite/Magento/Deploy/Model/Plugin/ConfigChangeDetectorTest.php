<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Deploy\Model\Plugin;

use Magento\Deploy\Model\DeploymentConfig\ChangeDetector;
use Magento\Deploy\Model\DeploymentConfig\DataCollector;
use Magento\Deploy\Model\DeploymentConfig\Hash;
use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\FrontControllerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Cache\CacheConstants;
use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\Fixture\Cache;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Verify deployment configuration checks use the config cache and invalidate stale results.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
#[DbIsolation(true)]
#[Cache('config', true)]
class ConfigChangeDetectorTest extends TestCase
{
    /**
     * @var Config
     */
    private Config $cache;

    /**
     * @var \Zend_Db_Profiler
     */
    private \Zend_Db_Profiler $profiler;

    /**
     * @var bool
     */
    private bool $profilerEnabled;

    /**
     * @var string
     */
    private string $flagTable;

    /**
     * Initialize the real config cache and database profiler.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->cache = $objectManager->get(Config::class);
        $this->cache->clean(CacheConstants::CLEANING_MODE_MATCHING_TAG, [Hash::CACHE_TAG]);
        $resource = $objectManager->get(ResourceConnection::class);
        $connection = $resource->getConnection();
        $this->flagTable = $connection->quoteIdentifier($resource->getTableName('flag'));
        $this->profiler = $connection->getProfiler();
        $this->profilerEnabled = $this->profiler->getEnabled();
        $this->profiler->setEnabled(true);
    }

    /**
     * Remove cached checks and restore profiling; database isolation restores the flag.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        try {
            $this->cache->clean(CacheConstants::CLEANING_MODE_MATCHING_TAG, [Hash::CACHE_TAG]);
        } finally {
            $this->profiler->setEnabled($this->profilerEnabled);
            parent::tearDown();
        }
    }

    /**
     * Verify the first check reads the flag and a new plugin instance reuses the cached result.
     *
     * @return void
     */
    public function testWarmCacheSkipsFlagSelect(): void
    {
        $collector = $this->createCollector('A');
        $this->createHash($collector)->regenerate();
        $selects = $this->getFlagSelectCount();

        $this->dispatch($this->createPlugin($collector));
        $this->assertSame($selects + 1, $this->getFlagSelectCount());

        $unavailableHash = $this->createMock(Hash::class);
        $unavailableHash->expects($this->never())->method('get');
        $this->dispatch($this->createPlugin($collector, $unavailableHash));
        $this->assertSame($selects + 1, $this->getFlagSelectCount());
    }

    /**
     * Verify importing B invalidates the cached success for A before A is restored.
     *
     * @return void
     */
    public function testRestoredConfigurationRequiresImport(): void
    {
        $collector = $this->createCollector('A');
        $this->createHash($collector)->regenerate();
        $this->dispatch($this->createPlugin($collector));

        $this->createHash($this->createCollector('B'))->regenerate();

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('The configuration file has changed.');
        $this->dispatch($this->createPlugin($collector));
    }

    /**
     * Provide deterministic deployment configuration without changing files.
     *
     * @param string $value
     * @return DataCollector
     */
    private function createCollector(string $value): DataCollector
    {
        $collector = $this->createStub(DataCollector::class);
        $collector->method('getConfig')->willReturn(['system' => ['default' => ['value' => $value]]]);
        return $collector;
    }

    /**
     * Create hash storage backed by the integration database and config cache.
     *
     * @param DataCollector $collector
     * @return Hash
     */
    private function createHash(DataCollector $collector): Hash
    {
        return Bootstrap::getObjectManager()->create(Hash::class, [
            'dataConfigCollector' => $collector,
            'cache' => $this->cache,
        ]);
    }

    /**
     * Create an independent plugin with blue/green deployment disabled.
     *
     * @param DataCollector $collector
     * @param Hash|null $hash
     * @return ConfigChangeDetector
     */
    private function createPlugin(DataCollector $collector, ?Hash $hash = null): ConfigChangeDetector
    {
        $objectManager = Bootstrap::getObjectManager();
        $deploymentConfig = $this->createStub(DeploymentConfig::class);
        $deploymentConfig->method('get')->willReturn(false);
        $detector = $objectManager->create(ChangeDetector::class, [
            'configHash' => $hash ?? $this->createHash($collector),
            'dataConfigCollector' => $collector,
        ]);
        return $objectManager->create(ConfigChangeDetector::class, [
            'changeDetector' => $detector,
            'deploymentConfig' => $deploymentConfig,
            'cache' => $this->cache,
            'dataCollector' => $collector,
        ]);
    }

    /**
     * Invoke the front-controller check without dispatching a request.
     *
     * @param ConfigChangeDetector $plugin
     * @return void
     */
    private function dispatch(ConfigChangeDetector $plugin): void
    {
        $plugin->beforeDispatch(
            $this->createStub(FrontControllerInterface::class),
            $this->createStub(RequestInterface::class)
        );
    }

    /**
     * Count SELECT statements referencing the flag table, including any table prefix.
     *
     * @return int
     */
    private function getFlagSelectCount(): int
    {
        $profiles = $this->profiler->getQueryProfiles(\Zend_Db_Profiler::SELECT) ?: [];
        return count(array_filter(
            $profiles,
            fn (\Zend_Db_Profiler_Query $profile): bool => str_contains($profile->getQuery(), $this->flagTable)
        ));
    }
}
