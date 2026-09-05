<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Test\Unit\Frontend\Adapter;

use Magento\Framework\Cache\Backend\ExtendedBackendInterface;
use Magento\Framework\Cache\Frontend\Adapter\RemoteSynchronizedLowLevelFrontend;
use Magento\Framework\Cache\Frontend\Adapter\RemoteSynchronizedLowLevelFrontendFactory;
use Magento\Framework\Cache\Frontend\Adapter\RemoteSynchronizedSymfonyAdapter;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Cache\MultiLoadInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for RemoteSynchronizedSymfonyAdapter
 */
class RemoteSynchronizedSymfonyAdapterTest extends TestCase
{
    /**
     * @var ExtendedBackendInterface|MockObject
     */
    private $backend;

    /**
     * @var RemoteSynchronizedLowLevelFrontendFactory|MockObject
     */
    private $lowLevelFrontendFactory;

    /**
     * @var RemoteSynchronizedSymfonyAdapter
     */
    private RemoteSynchronizedSymfonyAdapter $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->backend = $this->createMock(ExtendedBackendInterface::class);
        $this->lowLevelFrontendFactory = $this->createMock(RemoteSynchronizedLowLevelFrontendFactory::class);
        $this->model = new RemoteSynchronizedSymfonyAdapter($this->backend, $this->lowLevelFrontendFactory);
    }

    /**
     * It must implement the public frontend contract.
     */
    public function testImplementsFrontendInterface(): void
    {
        $this->assertInstanceOf(FrontendInterface::class, $this->model);
    }

    /**
     * test() delegates to the backend.
     */
    public function testTestDelegatesToBackend(): void
    {
        $this->backend->expects($this->once())
            ->method('test')
            ->with('id')
            ->willReturn(12345);

        $this->assertSame(12345, $this->model->test('id'));
    }

    /**
     * load() delegates to the backend.
     */
    public function testLoadDelegatesToBackend(): void
    {
        $this->backend->expects($this->once())
            ->method('load')
            ->with('id')
            ->willReturn('value');

        $this->assertSame('value', $this->model->load('id'));
    }

    /**
     * save() forwards data, id, tags and lifetime unchanged (including null lifetime).
     */
    public function testSaveDelegatesToBackend(): void
    {
        $this->backend->expects($this->once())
            ->method('save')
            ->with('value', 'id', ['TAG'], null)
            ->willReturn(true);

        $this->assertTrue($this->model->save('value', 'id', ['TAG']));
    }

    /**
     * remove() delegates to the backend.
     */
    public function testRemoveDelegatesToBackend(): void
    {
        $this->backend->expects($this->once())
            ->method('remove')
            ->with('id')
            ->willReturn(true);

        $this->assertTrue($this->model->remove('id'));
    }

    /**
     * clean() delegates mode and tags to the backend.
     */
    public function testCleanDelegatesToBackend(): void
    {
        $this->backend->expects($this->once())
            ->method('clean')
            ->with('all', [])
            ->willReturn(true);

        $this->assertTrue($this->model->clean());
    }

    /**
     * getBackend() returns the wrapped backend.
     */
    public function testGetBackendReturnsWrappedBackend(): void
    {
        $this->assertSame($this->backend, $this->model->getBackend());
    }

    /**
     * getMetadatas() delegates to the backend.
     */
    public function testGetMetadatasDelegatesToBackend(): void
    {
        $metadata = ['expire' => 123, 'tags' => ['TAG'], 'mtime' => 100];
        $this->backend->expects($this->once())
            ->method('getMetadatas')
            ->with('id')
            ->willReturn($metadata);

        $this->assertSame($metadata, $this->model->getMetadatas('id'));
    }

    /**
     * getLowLevelFrontend() returns a memoized low-level frontend wrapper.
     */
    public function testGetLowLevelFrontendReturnsMemoizedWrapper(): void
    {
        $wrapper = $this->createMock(RemoteSynchronizedLowLevelFrontend::class);
        $this->lowLevelFrontendFactory->expects($this->once())
            ->method('create')
            ->with(['backend' => $this->backend])
            ->willReturn($wrapper);

        $first = $this->model->getLowLevelFrontend();
        $second = $this->model->getLowLevelFrontend();

        $this->assertSame($wrapper, $first);
        $this->assertSame($first, $second, 'The low-level frontend must be created only once');
    }

    /**
     * loadMultiple() delegates to the backend's MultiLoadInterface contract in one round-trip.
     */
    public function testLoadMultipleDelegatesToBatchCapableBackend(): void
    {
        $backend = $this->createMockForIntersectionOfInterfaces(
            [ExtendedBackendInterface::class, MultiLoadInterface::class]
        );
        $backend->expects($this->once())
            ->method('loadMultiple')
            ->with(['id1', 'id2'])
            ->willReturn(['id1' => 'value1', 'id2' => 'value2']);

        $model = new RemoteSynchronizedSymfonyAdapter($backend, $this->lowLevelFrontendFactory);

        $this->assertSame(['id1' => 'value1', 'id2' => 'value2'], $model->loadMultiple(['id1', 'id2']));
    }

    /**
     * loadMultiple() returns nothing when the backend has no batch support: partial per-key emulation
     * provides no benefit, so the caller falls back to on-demand load() instead.
     */
    public function testLoadMultipleReturnsEmptyWhenBackendNotBatchCapable(): void
    {
        $this->backend->expects($this->never())->method('load');

        $this->assertSame([], $this->model->loadMultiple(['id1', 'id2']));
    }
}
