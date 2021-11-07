<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Unit\Model\Indexer;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\Event\Manager;
use Magento\Framework\Indexer\CacheContext;
use Magento\Indexer\Model\Indexer\DeferredCacheCleaner;
use Magento\Indexer\Model\Indexer\DeferredCacheContext;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test Deferred cache cleaner for indexers
 */
class DeferredCacheCleanerTest extends TestCase
{
    /**
     * @var Manager|MockObject
     */
    private $eventManager;

    /**
     * @var CacheInterface|MockObject
     */
    private $cache;

    /**
     * @var DeferredCacheContext|MockObject
     */
    private $deferredCacheContext;

    /**
     * @var CacheContext|MockObject
     */
    private $cacheContext;

    /**
     * @var DeferredCacheCleaner
     */
    private $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->cacheContext = $this->createMock(CacheContext::class);
        $this->deferredCacheContext = $this->createMock(DeferredCacheContext::class);
        $this->eventManager = $this->createMock(Manager::class);
        $this->cache = $this->getMockForAbstractClass(CacheInterface::class);
        $this->model = new DeferredCacheCleaner(
            $this->eventManager,
            $this->cache,
            $this->deferredCacheContext,
            $this->cacheContext,
        );
    }

    /**
     * Test start()
     */
    public function testStart(): void
    {
        $this->deferredCacheContext->expects($this->once())
            ->method('start');
        $this->model->start();
    }

    /**
     * Test flush()
     *
     * @param array $tags
     * @param bool $isCacheClean
     * @dataProvider cacheTagsDataProvider
     */
    public function testFlush(array $tags, bool $isCacheClean = true): void
    {
        $this->eventManager->expects($this->once())
            ->method('dispatch')
            ->with(
                'clean_cache_by_tags',
                ['object' => $this->cacheContext]
            );

        $this->deferredCacheContext->expects($this->once())
            ->method('commit');

        $this->cacheContext->expects($this->once())
            ->method('getIdentities')
            ->willReturn($tags);

        $this->cacheContext->expects($this->exactly($isCacheClean ? 1 : 0))
            ->method('flush');

        $this->cache->expects($this->exactly($isCacheClean ? 1 : 0))
            ->method('clean')
            ->with($tags);

        $this->model->flush();
    }

    /**
     * @return array[]
     */
    public function cacheTagsDataProvider(): array
    {
        return [
            [[], false],
            [['cat_c_1', 'cat_c_2'], true]
        ];
    }
}
