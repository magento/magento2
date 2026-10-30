<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\App\Config\Type;

use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\App\Config\Source\RuntimeConfigSource;
use Magento\Store\App\Config\Type\Scopes;
use Magento\Store\Model\Group;
use Magento\Store\Model\Store;
use Magento\Store\Model\Website;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Verify scopes caching, path resolution and request-local cleanup.
 */
class ScopesTest extends TestCase
{
    /**
     * Scopes configuration under test.
     *
     * @var Scopes
     */
    private $unit;

    /**
     * Aggregated scopes source mock.
     *
     * @var MockObject
     */
    private $sourceMock;

    /**
     * Configuration cache mock.
     *
     * @var Config
     */
    private Config $cache;

    /**
     * Runtime source availability mock.
     *
     * @var RuntimeConfigSource
     */
    private RuntimeConfigSource $runtimeConfigSource;

    /**
     * Create isolated scopes dependencies.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->sourceMock = $this->getMockBuilder(ConfigSourceInterface::class)
            ->getMock();
        $this->cache = $this->createMock(Config::class);
        $this->cache->method('load')->willReturn(false);
        $this->runtimeConfigSource = $this->createMock(RuntimeConfigSource::class);
        $this->runtimeConfigSource->method('isDbAvailable')->willReturn(true);
        $this->unit = new Scopes($this->sourceMock, $this->cache, new Json(), $this->runtimeConfigSource);
    }

    /**
     * Resolve scope data by code or identifier.
     *
     * @param string $path
     * @param mixed $expectedResult
     * @return void
     */
    #[DataProvider('getDataProvider')]
    public function testGet($path, $expectedResult)
    {
        $configData = [
            'websites' => [
                'admin' => [
                    'website_id' => 0,
                    'code' => 'admin',
                ],
                'default' => [
                    'website_id' => 1,
                    'code' => 'default',
                ],
            ],
            'groups' => [
                '0' => [
                    'group_id' => 0,
                    'code' => 'admin',
                ],
            ],
        ];
        $this->sourceMock->expects($this->once())->method('get')->with('')->willReturn($configData);

        $this->assertEquals($expectedResult, $this->unit->get($path));
    }

    /**
     * Return null when an identifier is absent from the aggregate.
     *
     * @return void
     */
    public function testGetConfigWhenDataIsNotPresentInInternalCacheAndNotFound()
    {
        $initConfigData = [
            'websites' => [
                'base' => [
                    'website_id' => 0,
                    'code' => 'base'
                ]
            ]
        ];
        $this->sourceMock->expects($this->any())->method('get')->willReturnMap([
            ['', $initConfigData],
            ['websites/1', null],
        ]);

        $this->assertNull($this->unit->get('websites/1'));
    }

    /**
     * Serve cached scopes without reading either source.
     *
     * @return void
     */
    public function testCacheHitDoesNotReadSources(): void
    {
        $data = ['stores' => ['default' => ['store_id' => 1, 'code' => 'default']]];
        $cache = $this->createMock(Config::class);
        $cache->expects($this->once())->method('load')->with(Scopes::CACHE_ID)
            ->willReturn((new Json())->serialize($data));
        $this->sourceMock->expects($this->never())->method('get');
        $this->runtimeConfigSource->expects($this->never())->method('get');
        $unit = new Scopes($this->sourceMock, $cache, new Json(), $this->runtimeConfigSource);

        $this->assertSame($data, $unit->get());
        $this->assertSame('default', $unit->get('stores/1/code'));
    }

    /**
     * Persist an aggregate backed by runtime data with all scope entity tags.
     *
     * @return void
     */
    public function testCacheMissSavesAggregateWithEntityTags(): void
    {
        $data = ['stores' => ['default' => ['store_id' => 1, 'code' => 'default']]];
        $this->sourceMock->expects($this->once())->method('get')->willReturn($data);
        $this->runtimeConfigSource->method('hasRuntimeData')->willReturn(true);
        $this->cache->expects($this->once())->method('save')->with(
            (new Json())->serialize($data),
            Scopes::CACHE_ID,
            [Store::CACHE_TAG, Group::CACHE_TAG, Website::CACHE_TAG]
        );

        $this->assertSame($data, $this->unit->get());
        $this->assertSame($data, $this->unit->get());
    }

    /**
     * Use deployment-file scopes without accessing cache when no database is configured.
     *
     * @return void
     */
    public function testUnavailableDatabaseBypassesCache(): void
    {
        $data = ['stores' => ['default' => ['store_id' => 1, 'code' => 'default']]];
        $runtime = $this->createMock(RuntimeConfigSource::class);
        $runtime->method('isDbAvailable')->willReturn(false);
        $this->cache->expects($this->never())->method('load');
        $this->cache->expects($this->never())->method('save');
        $this->sourceMock->expects($this->once())->method('get')->willReturn($data);
        $unit = new Scopes($this->sourceMock, $this->cache, new Json(), $runtime);

        $this->assertSame($data, $unit->get());
    }

    /**
     * Avoid persisting deployment-file scopes when runtime tables provide no data.
     *
     * @return void
     */
    public function testDeploymentDataWithoutRuntimeDataIsNotCached(): void
    {
        $data = ['stores' => ['default' => ['store_id' => 1, 'code' => 'default']]];
        $this->sourceMock->method('get')->willReturn($data);
        $this->runtimeConfigSource->method('hasRuntimeData')->willReturn(false);
        $this->cache->expects($this->never())->method('save');

        $this->assertSame($data, $this->unit->get());
    }

    /**
     * Keep shared scopes while clearing request-local data and identifier mappings.
     *
     * @return void
     */
    public function testCleanKeepsSharedCacheAndDropsIdMap(): void
    {
        $this->cache->expects($this->never())->method('clean');
        $this->cache->expects($this->never())->method('remove');
        $this->assertReloadAfterReset('clean');
    }

    /**
     * Reset request-local mappings without cleaning the shared cache.
     *
     * @return void
     */
    public function testRequestResetKeepsSharedCacheAndDropsIdMap(): void
    {
        $this->cache->expects($this->never())->method('clean');
        $this->cache->expects($this->never())->method('remove');
        $this->assertReloadAfterReset('_resetState');
    }

    /**
     * Verify that resetting a scopes instance rebuilds its identifier mappings.
     *
     * @param string $method
     * @return void
     */
    private function assertReloadAfterReset(string $method): void
    {
        $this->sourceMock->expects($this->exactly(2))->method('get')->willReturnOnConsecutiveCalls(
            ['stores' => ['old' => ['store_id' => 1, 'code' => 'old']]],
            ['stores' => ['new' => ['store_id' => 1, 'code' => 'new']]]
        );
        $this->assertSame('old', $this->unit->get('stores/1/code'));
        $this->unit->$method();
        $this->assertSame('new', $this->unit->get('stores/1/code'));
    }

    /**
     * Return path and expected value for test different cases
     *
     * @return array
     */
    public static function getDataProvider()
    {
        return [
            [
                'websites',
                [
                    'admin' => [
                        'website_id' => 0,
                        'code' => 'admin',
                    ],
                    'default' => [
                        'website_id' => 1,
                        'code' => 'default',
                    ]
                ],
            ],
            [
                'websites/admin',
                [
                    'website_id' => 0,
                    'code' => 'admin',
                ],
            ],
            [
                'websites/1',
                [
                    'website_id' => 1,
                    'code' => 'default',
                ],
            ],
            [
                'websites/1/code',
                'default',
            ],
            [
                'groups/0',
                [
                    'group_id' => 0,
                    'code' => 'admin',
                ],
            ],
        ];
    }
}
