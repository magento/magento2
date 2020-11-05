<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Unit\Model\Indexer;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\Event\Manager;
use Magento\Framework\Indexer\ActionInterface;
use Magento\Framework\Indexer\CacheContext;
use Magento\Indexer\Model\Indexer\CacheCleaner;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test cache cleaner plugin
 */
class CacheCleanerTest extends TestCase
{
    /**
     * @var Manager|MockObject
     */
    private $eventManager;
    /**
     * @var CacheContext|MockObject
     */
    private $cacheContext;
    /**
     * @var CacheInterface|MockObject
     */
    private $cache;
    /**
     * @var CacheCleaner
     */
    private $model;
    /**
     * @var ActionInterface|MockObject
     */
    private $action;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->action = $this->getMockForAbstractClass(ActionInterface::class);
        $this->cacheContext = $this->createMock(CacheContext::class);
        $this->eventManager = $this->createMock(Manager::class);
        $this->cache = $this->getMockForAbstractClass(CacheInterface::class);
        $this->model = new CacheCleaner(
            $this->eventManager,
            $this->cacheContext,
            $this->cache
        );
    }

    /**
     * @param array $tags
     * @param bool $isCacheClean
     * @dataProvider cacheTagsDataProvider
     */
    public function testAfterExecuteFull(array $tags, bool $isCacheClean = true): void
    {
        $this->expectCacheClean($tags, $isCacheClean);
        $this->model->afterExecuteFull($this->action);
    }

    /**
     * @param array $tags
     * @param bool $isCacheClean
     * @dataProvider cacheTagsDataProvider
     */
    public function testAfterExecuteList(array $tags, bool $isCacheClean = true): void
    {
        $this->expectCacheClean($tags, $isCacheClean);
        $this->model->afterExecuteList($this->action);
    }

    /**
     * @param array $tags
     * @param bool $isCacheClean
     * @dataProvider cacheTagsDataProvider
     */
    public function testAfterExecuteRow(array $tags, bool $isCacheClean = true): void
    {
        $this->expectCacheClean($tags, $isCacheClean);
        $this->model->afterExecuteRow($this->action);
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

    /**
     * @param array $tags
     * @param bool $isCacheClean
     */
    private function expectCacheClean(array $tags, bool $isCacheClean = true): void
    {
        $this->eventManager->expects($this->once())
            ->method('dispatch')
            ->with(
                'clean_cache_by_tags',
                ['object' => $this->cacheContext]
            );

        $this->cacheContext->expects($this->atLeastOnce())
            ->method('getIdentities')
            ->willReturn($tags);

        $this->cache->expects($this->exactly($isCacheClean ? 1 : 0))
            ->method('clean')
            ->with($tags);

        $this->cacheContext->expects($this->exactly($isCacheClean ? 1 : 0))
            ->method('flush');
    }
}
