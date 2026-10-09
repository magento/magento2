<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Unit\Model\Processor;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\Event\Manager as EventManager;
use Magento\Framework\Indexer\ActionInterface;
use Magento\Framework\Indexer\CacheContext;
use Magento\Indexer\Model\Indexer\CacheCleaner;
use Magento\Indexer\Model\Indexer\DeferredCacheCleaner;
use Magento\Indexer\Model\Indexer\DeferredCacheContext;
use Magento\Indexer\Model\Processor;
use Magento\Indexer\Model\Processor\CleanCache;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test cache clean plugin
 */
class CleanCacheTest extends TestCase
{
    /**
     * Tested plugin
     *
     * @var CleanCache
     */
    private $plugin;

    /**
     * Mock for context
     *
     * @var DeferredCacheCleaner|MockObject
     */
    private $cacheCleaner;

    /**
     * Mocked processor
     *
     * @var Processor|MockObject
     */
    private $subjectMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->subjectMock = $this->createMock(Processor::class);
        $this->cacheCleaner = $this->createMock(DeferredCacheCleaner::class);
        $this->plugin = new CleanCache($this->cacheCleaner);
    }

    /**
     * Test beforeUpdateMview()
     */
    public function testBeforeUpdateMview(): void
    {
        $this->cacheCleaner->expects($this->once())
            ->method('start');

        $this->plugin->beforeUpdateMview($this->subjectMock);
    }

    /**
     * Test afterUpdateMview()
     */
    public function testAfterUpdateMview(): void
    {
        $this->cacheCleaner->expects($this->once())
            ->method('flush');

        $this->plugin->afterUpdateMview($this->subjectMock);
    }

    /**
     * Test beforeReindexAllInvalid()
     */
    public function testBeforeReindexAllInvalid(): void
    {
        $this->cacheCleaner->expects($this->once())
            ->method('start');

        $this->plugin->beforeReindexAllInvalid($this->subjectMock);
    }

    /**
     * Test afterReindexAllInvalid()
     */
    public function testAfterReindexAllInvalid(): void
    {
        $this->cacheCleaner->expects($this->once())
            ->method('flush');

        $this->plugin->afterReindexAllInvalid($this->subjectMock);
    }

    #[DataProvider('aroundMethodsDataProvider')]
    public function testAroundFlushesAndRethrowsWhenProcessorFails(string $method): void
    {
        $exception = new \RuntimeException('Reindex failed');
        $this->cacheCleaner->expects($this->never())
            ->method('start');
        $this->cacheCleaner->expects($this->once())
            ->method('flush');

        $this->expectExceptionObject($exception);
        $this->plugin->$method(
            $this->subjectMock,
            function () use ($exception) {
                throw $exception;
            }
        );
    }

    #[DataProvider('aroundMethodsDataProvider')]
    public function testAroundDoesNotFlushWhenProcessorSucceeds(string $method): void
    {
        $this->cacheCleaner->expects($this->never())
            ->method('flush');

        $result = $this->plugin->$method(
            $this->subjectMock,
            function () {
                return 'result';
            }
        );

        $this->assertSame('result', $result);
    }

    public static function aroundMethodsDataProvider(): array
    {
        return [
            'update mview' => ['aroundUpdateMview'],
            'reindex all invalid' => ['aroundReindexAllInvalid'],
        ];
    }

    public function testFailedReindexAllInvalidDoesNotBlockCacheCleaningOfNextMviewUpdate(): void
    {
        $cacheContext = new CacheContext();
        $deferredCacheContext = new DeferredCacheContext($cacheContext);
        $appCache = $this->createMock(CacheInterface::class);
        $deferredCacheCleaner = new DeferredCacheCleaner(
            $this->createStub(EventManager::class),
            $appCache,
            $deferredCacheContext,
            $cacheContext
        );
        $plugin = new CleanCache($deferredCacheCleaner);
        $actionPlugin = new CacheCleaner($deferredCacheCleaner);
        $action = $this->createStub(ActionInterface::class);
        $cleanedTags = [];
        $appCache->expects($this->exactly(2))
            ->method('clean')
            ->willReturnCallback(function (array $tags) use (&$cleanedTags) {
                $cleanedTags[] = $tags;
                return true;
            });

        $plugin->beforeReindexAllInvalid($this->subjectMock);
        try {
            $plugin->aroundReindexAllInvalid(
                $this->subjectMock,
                function () use ($actionPlugin, $action, $deferredCacheContext) {
                    $actionPlugin->beforeExecuteFull($action);
                    $actionPlugin->aroundExecuteFull(
                        $action,
                        $this->createFailingReindex($deferredCacheContext, 'cat_c_1')
                    );
                    $actionPlugin->afterExecuteFull($action);
                }
            );
            $this->fail('The exception thrown by the indexer must not be swallowed.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Reindex failed', $exception->getMessage());
        }
        $this->assertFalse($deferredCacheContext->isActive());

        $plugin->beforeUpdateMview($this->subjectMock);
        $plugin->aroundUpdateMview(
            $this->subjectMock,
            function () use ($deferredCacheContext) {
                $deferredCacheContext->registerTags(['cat_p_2']);
            }
        );
        $plugin->afterUpdateMview($this->subjectMock);

        $this->assertFalse($deferredCacheContext->isActive());
        $this->assertSame([['cat_c_1'], ['cat_p_2']], $cleanedTags);
    }

    private function createFailingReindex(DeferredCacheContext $deferredCacheContext, string $tag): \Closure
    {
        return function () use ($deferredCacheContext, $tag) {
            $deferredCacheContext->registerTags([$tag]);
            throw new \RuntimeException('Reindex failed');
        };
    }
}
