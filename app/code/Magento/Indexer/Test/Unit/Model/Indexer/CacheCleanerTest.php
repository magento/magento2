<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Unit\Model\Indexer;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\Event\Manager as EventManager;
use Magento\Framework\Indexer\ActionInterface;
use Magento\Framework\Indexer\CacheContext;
use Magento\Indexer\Model\Indexer\CacheCleaner;
use Magento\Indexer\Model\Indexer\DeferredCacheCleaner;
use Magento\Indexer\Model\Indexer\DeferredCacheContext;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test cache cleaner plugin
 */
class CacheCleanerTest extends TestCase
{
    /**
     * @var CacheCleaner
     */
    private $model;

    /**
     * @var ActionInterface|MockObject
     */
    private $action;

    /**
     * @var DeferredCacheCleaner|MockObject
     */
    private $cacheCleaner;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->action = $this->createMock(ActionInterface::class);
        $this->cacheCleaner = $this->createMock(DeferredCacheCleaner::class);
        $this->model = new CacheCleaner($this->cacheCleaner);
    }

    /**
     * Test beforeExecuteFull()
     */
    public function testBeforeExecuteFull(): void
    {
        $this->cacheCleaner->expects($this->once())
            ->method('start');
        $this->model->beforeExecuteFull($this->action);
    }

    /**
     * Test afterExecuteFull()
     */
    public function testAfterExecuteFull(): void
    {
        $this->cacheCleaner->expects($this->once())
            ->method('flush');
        $this->model->afterExecuteFull($this->action);
    }

    /**
     * Test beforeExecuteList()
     */
    public function testBeforeExecuteList(): void
    {
        $this->cacheCleaner->expects($this->once())
            ->method('start');
        $this->model->beforeExecuteList($this->action);
    }

    /**
     * Test afterExecuteList()
     */
    public function testAfterExecuteList(): void
    {
        $this->cacheCleaner->expects($this->once())
            ->method('flush');
        $this->model->afterExecuteList($this->action);
    }

    /**
     * Test beforeExecuteRow()
     */
    public function testBeforeExecuteRow(): void
    {
        $this->cacheCleaner->expects($this->once())
            ->method('start');
        $this->model->beforeExecuteRow($this->action);
    }

    /**
     * Test afterExecuteRow()
     */
    public function testAfterExecuteRow(): void
    {
        $this->cacheCleaner->expects($this->once())
            ->method('flush');
        $this->model->afterExecuteRow($this->action);
    }

    #[DataProvider('aroundMethodsDataProvider')]
    public function testAroundFlushesAndRethrowsWhenExecuteFails(string $method, array $arguments): void
    {
        $exception = new \RuntimeException('Reindex failed');
        $this->cacheCleaner->expects($this->never())
            ->method('start');
        $this->cacheCleaner->expects($this->once())
            ->method('flush');

        $this->expectExceptionObject($exception);
        $this->model->$method(
            $this->action,
            function () use ($exception) {
                throw $exception;
            },
            ...$arguments
        );
    }

    #[DataProvider('aroundMethodsDataProvider')]
    public function testAroundDoesNotFlushWhenExecuteSucceeds(string $method, array $arguments): void
    {
        $this->cacheCleaner->expects($this->never())
            ->method('flush');

        $receivedArguments = null;
        $result = $this->model->$method(
            $this->action,
            function (...$proceedArguments) use (&$receivedArguments) {
                $receivedArguments = $proceedArguments;
                return 'result';
            },
            ...$arguments
        );

        $this->assertSame('result', $result);
        $this->assertSame($arguments, $receivedArguments);
    }

    public static function aroundMethodsDataProvider(): array
    {
        return [
            'full' => ['aroundExecuteFull', []],
            'list' => ['aroundExecuteList', [[1, 2]]],
            'row' => ['aroundExecuteRow', [1]],
        ];
    }

    public function testFailedExecuteListDoesNotBlockCacheCleaningOfNextReindex(): void
    {
        $cacheContext = new CacheContext();
        $deferredCacheContext = new DeferredCacheContext($cacheContext);
        $appCache = $this->createMock(CacheInterface::class);
        $model = new CacheCleaner(
            new DeferredCacheCleaner(
                $this->createStub(EventManager::class),
                $appCache,
                $deferredCacheContext,
                $cacheContext
            )
        );
        $cleanedTags = [];
        $appCache->expects($this->exactly(2))
            ->method('clean')
            ->willReturnCallback(function (array $tags) use (&$cleanedTags) {
                $cleanedTags[] = $tags;
                return true;
            });

        $model->beforeExecuteList($this->action);
        try {
            $model->aroundExecuteList(
                $this->action,
                $this->createFailingReindex($deferredCacheContext, 'cat_p_1'),
                [1]
            );
            $this->fail('The exception thrown by the indexer action must not be swallowed.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Reindex failed', $exception->getMessage());
        }
        $this->assertFalse($deferredCacheContext->isActive());

        $model->beforeExecuteList($this->action);
        $model->aroundExecuteList(
            $this->action,
            function () use ($deferredCacheContext) {
                $deferredCacheContext->registerTags(['cat_p_2']);
            },
            [2]
        );
        $model->afterExecuteList($this->action);

        $this->assertFalse($deferredCacheContext->isActive());
        $this->assertSame([['cat_p_1'], ['cat_p_2']], $cleanedTags);
    }

    private function createFailingReindex(DeferredCacheContext $deferredCacheContext, string $tag): \Closure
    {
        return function () use ($deferredCacheContext, $tag) {
            $deferredCacheContext->registerTags([$tag]);
            throw new \RuntimeException('Reindex failed');
        };
    }
}
