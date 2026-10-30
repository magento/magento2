<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Test\Unit\Frontend\Adapter;

use Magento\Framework\Cache\Frontend\Adapter\Symfony;
use Magento\Framework\Cache\Frontend\Adapter\Symfony\BackendWrapper;
use Magento\Framework\Cache\Frontend\Adapter\Symfony\BackendWrapperFactory;
use Magento\Framework\Cache\Frontend\Adapter\Symfony\LowLevelFrontend;
use Magento\Framework\Cache\Frontend\Adapter\SymfonyAdapters\TagAdapterInterface;
use Magento\Framework\Cache\FrontendInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

/**
 * Unit test for the Symfony cache frontend adapter.
 *
 * Uses a real in-memory Symfony ArrayAdapter as the PSR-6 pool and a mocked tag adapter,
 * so the adapter's own store/read/clean logic is exercised end-to-end.
 */
class SymfonyTest extends TestCase
{
    /**
     * @var ArrayAdapter
     */
    private ArrayAdapter $pool;

    /**
     * @var TagAdapterInterface|MockObject
     */
    private $tagAdapter;

    /**
     * @var BackendWrapperFactory|MockObject
     */
    private $backendWrapperFactory;

    /**
     * @var Symfony
     */
    private Symfony $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->pool = new ArrayAdapter();
        $this->tagAdapter = $this->createMock(TagAdapterInterface::class);
        $this->backendWrapperFactory = $this->createMock(BackendWrapperFactory::class);
        $this->model = new Symfony(
            fn () => $this->pool,
            $this->tagAdapter,
            7200,
            'test_',
            null,
            $this->backendWrapperFactory
        );
    }

    /**
     * It must implement the public frontend contract.
     */
    public function testImplementsFrontendInterface(): void
    {
        $this->assertInstanceOf(FrontendInterface::class, $this->model);
    }

    /**
     * getFrontend() returns the adapter itself.
     */
    public function testGetFrontendReturnsSelf(): void
    {
        $this->assertSame($this->model, $this->model->getFrontend());
    }

    /**
     * A saved value can be loaded back unchanged.
     */
    public function testSaveAndLoad(): void
    {
        $this->assertTrue($this->model->save('cached-value', 'my_id'));
        $this->assertSame('cached-value', $this->model->load('my_id'));
    }

    /**
     * load() returns false for an unknown identifier.
     */
    public function testLoadReturnsFalseOnMiss(): void
    {
        $this->assertFalse($this->model->load('missing_id'));
    }

    /**
     * test() returns a positive modification timestamp for a stored entry.
     */
    public function testTestReturnsTimestampForStoredEntry(): void
    {
        $this->model->save('cached-value', 'my_id');

        $result = $this->model->test('my_id');

        $this->assertIsInt($result);
        $this->assertGreaterThan(0, $result);
    }

    /**
     * test() returns false for an unknown identifier.
     */
    public function testTestReturnsFalseOnMiss(): void
    {
        $this->assertFalse($this->model->test('missing_id'));
    }

    /**
     * remove() deletes the entry so a subsequent load misses.
     */
    public function testRemove(): void
    {
        $this->model->save('cached-value', 'my_id');

        $this->assertTrue($this->model->remove('my_id'));
        $this->assertFalse($this->model->load('my_id'));
    }

    /**
     * clean(ALL) clears the pool and the tag indices.
     */
    public function testCleanAllClearsPoolAndIndices(): void
    {
        $this->model->save('cached-value', 'my_id');

        $this->tagAdapter->expects($this->once())->method('clearAllIndices');

        $this->assertTrue($this->model->clean('all'));
        $this->assertFalse($this->model->load('my_id'));
    }

    /**
     * clean() with an unsupported mode throws.
     */
    public function testCleanWithInvalidModeThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->model->clean('bogus_mode');
    }

    /**
     * clean(MATCHING_ANY_TAG) resolves ids through the tag adapter and batch-deletes them.
     */
    public function testCleanMatchingAnyTagDeletesResolvedIds(): void
    {
        $this->tagAdapter->expects($this->once())
            ->method('getIdsMatchingAnyTags')
            ->willReturn(['ID_X', 'ID_Y']);
        $this->tagAdapter->expects($this->once())
            ->method('deleteByIds')
            ->with(['ID_X', 'ID_Y'])
            ->willReturn(true);

        $this->assertTrue($this->model->clean('matchingAnyTag', ['some_tag']));
    }

    /**
     * getBackend() builds the wrapper through the injected factory, passing the pool, adapter and self.
     */
    public function testGetBackendReturnsBackendWrapper(): void
    {
        $wrapper = $this->createMock(BackendWrapper::class);
        $this->backendWrapperFactory->expects($this->once())
            ->method('create')
            ->with([
                'cache' => $this->pool,
                'adapter' => $this->tagAdapter,
                'symfony' => $this->model,
            ])
            ->willReturn($wrapper);

        $this->assertSame($wrapper, $this->model->getBackend());
    }

    /**
     * getLowLevelFrontend() returns the Symfony low-level frontend.
     */
    public function testGetLowLevelFrontendReturnsLowLevelFrontend(): void
    {
        $this->assertInstanceOf(LowLevelFrontend::class, $this->model->getLowLevelFrontend());
    }

    /**
     * loadMultiple() returns only the hits, keyed by the original identifiers.
     */
    public function testLoadMultipleReturnsHitsOnly(): void
    {
        $this->model->save('v1', 'id_one');
        $this->model->save('v2', 'id_two');

        $result = $this->model->loadMultiple(['id_one', 'id_two', 'missing_id']);

        $this->assertSame(['id_one' => 'v1', 'id_two' => 'v2'], $result);
    }
}
