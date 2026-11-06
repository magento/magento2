<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Deploy\Test\Unit\Model\Plugin;

use Magento\Deploy\Model\DeploymentConfig\ChangeDetector;
use Magento\Deploy\Model\DeploymentConfig\DataCollector;
use Magento\Deploy\Model\DeploymentConfig\Hash;
use Magento\Deploy\Model\DeploymentConfig\Hash\Generator as HashGenerator;
use Magento\Deploy\Model\Plugin\ConfigChangeDetector;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\FrontControllerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Config\CacheInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Verify deployment configuration checks and cached results before dispatch.
 */
class ConfigChangeDetectorTest extends TestCase
{
    /**
     * @var ConfigChangeDetector
     */
    private $configChangeDetectorPlugin;

    /**
     * @var ChangeDetector|MockObject
     */
    private $changeDetectorMock;

    /**
     * @var FrontControllerInterface|MockObject
     */
    private $frontControllerMock;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var DeploymentConfig|mixed|MockObject
     */
    private $deploymentConfig;

    /**
     * @var CacheInterface|MockObject
     */
    private $cache;

    /**
     * @var DataCollector|MockObject
     */
    private $dataCollector;

    /**
     * @var HashGenerator
     */
    private HashGenerator $hashGenerator;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->changeDetectorMock = $this->getMockBuilder(ChangeDetector::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->frontControllerMock = $this->createMock(FrontControllerInterface::class);
        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->deploymentConfig = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cache = $this->createMock(CacheInterface::class);
        $this->dataCollector = $this->createMock(DataCollector::class);
        $this->dataCollector->method('getConfig')->willReturn(['section' => 'some data']);
        $this->hashGenerator = new HashGenerator(new Json());

        $this->configChangeDetectorPlugin = new ConfigChangeDetector(
            $this->changeDetectorMock,
            $this->deploymentConfig,
            $this->cache,
            $this->dataCollector,
            $this->hashGenerator
        );
    }

    /**
     * @return void
     */
    public function testBeforeDispatchWithoutException()
    {
        $this->changeDetectorMock->expects($this->once())
            ->method('hasChanges')
            ->willReturn(false);
        $this->configChangeDetectorPlugin->beforeDispatch($this->frontControllerMock, $this->requestMock);
    }

    /**
     * @return void
     */
    public function testBeforeDispatchWithException()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage(
            'The configuration file has changed. Run the "app:config:import" '
                . 'or the "setup:upgrade" command to synchronize the configuration.'
        );
        $this->changeDetectorMock->expects($this->once())
            ->method('hasChanges')
            ->willReturn(true);
        $this->configChangeDetectorPlugin->beforeDispatch($this->frontControllerMock, $this->requestMock);
    }

    /**
     * Verify blue/green deployment skips configuration checks and caching.
     *
     * @return void
     */
    public function testBeforeDispatchWithBlueGreen()
    {
        $this->cache->expects($this->never())->method('load');
        $this->cache->expects($this->never())->method('save');
        $this->changeDetectorMock->expects($this->never())->method('hasChanges');
        $this->deploymentConfig->expects($this->atLeastOnce())
            ->method('get')
            ->with('deployment/blue_green/enabled')
            ->willReturn(1);
        $this->configChangeDetectorPlugin->beforeDispatch($this->frontControllerMock, $this->requestMock);
    }

    /**
     * Verify only successful configuration checks are cached.
     *
     * @param bool $cached
     * @param array $savedHashes
     * @param bool $changed
     * @return void
     */
    #[DataProvider('configHashCacheDataProvider')]
    public function testConfigHashCache(bool $cached, array $savedHashes, bool $changed): void
    {
        $configHash = $this->createMock(Hash::class);
        $configHash->expects($cached ? $this->never() : $this->once())
            ->method('get')
            ->willReturn($savedHashes);
        $changeDetector = new ChangeDetector($configHash, $this->hashGenerator, $this->dataCollector);
        $plugin = new ConfigChangeDetector(
            $changeDetector,
            $this->deploymentConfig,
            $this->cache,
            $this->dataCollector,
            $this->hashGenerator
        );
        $cacheKey = 'deployment_config_hash_in_sync_' . $this->hashGenerator->generate(
            ['section' => $this->hashGenerator->generate('some data')]
        );
        $this->cache->expects($this->once())->method('load')->with($cacheKey)->willReturn($cached ? 'true' : false);
        $this->cache->expects(!$cached && !$changed ? $this->once() : $this->never())
            ->method('save')
            ->with('true', $cacheKey, [Hash::CACHE_TAG]);

        if ($changed) {
            $this->expectException(LocalizedException::class);
            $this->expectExceptionMessage('The configuration file has changed.');
        }

        $plugin->beforeDispatch($this->frontControllerMock, $this->requestMock);
    }

    /**
     * Provide cache states and stored hashes for configuration checks.
     *
     * @return array
     */
    public static function configHashCacheDataProvider(): array
    {
        $hash = (new HashGenerator(new Json()))->generate('some data');
        return [
            'warm cache skips flag read' => [true, [], false],
            'in sync result is cached' => [false, ['section' => $hash], false],
            'changed result is not cached' => [false, ['section' => 'old hash'], true],
            'missing stored hash is not cached' => [false, [], true],
        ];
    }

    /**
     * Verify edited configuration misses an existing cache entry.
     *
     * @return void
     */
    public function testChangedConfigurationMissesWarmCache(): void
    {
        $dataCollector = $this->createMock(DataCollector::class);
        $dataCollector->expects($this->exactly(2))->method('getConfig')->willReturnOnConsecutiveCalls(
            ['section' => 'some data'],
            ['section' => 'changed data']
        );
        $oldKey = 'deployment_config_hash_in_sync_' . $this->hashGenerator->generate(
            ['section' => $this->hashGenerator->generate('some data')]
        );
        $newKey = 'deployment_config_hash_in_sync_' . $this->hashGenerator->generate(
            ['section' => $this->hashGenerator->generate('changed data')]
        );
        $this->cache->expects($this->exactly(2))->method('load')->willReturnMap([
            [$oldKey, 'true'],
            [$newKey, false],
        ]);
        $this->cache->expects($this->never())->method('save');
        $this->changeDetectorMock->expects($this->once())->method('hasChanges')->willReturn(true);
        $plugin = new ConfigChangeDetector(
            $this->changeDetectorMock,
            $this->deploymentConfig,
            $this->cache,
            $dataCollector,
            $this->hashGenerator
        );

        $plugin->beforeDispatch($this->frontControllerMock, $this->requestMock);
        $this->expectException(LocalizedException::class);
        $plugin->beforeDispatch($this->frontControllerMock, $this->requestMock);
    }
}
