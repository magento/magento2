<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Test\Unit\Frontend\Adapter;

use Magento\Framework\Cache\Backend\ExtendedBackendInterface;
use Magento\Framework\Cache\Frontend\Adapter\RemoteSynchronizedLowLevelFrontend;
use Magento\Framework\Cache\Frontend\Adapter\RemoteSynchronizedSymfonyAdapter;
use Magento\Framework\Cache\FrontendInterface;
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
     * @var RemoteSynchronizedSymfonyAdapter
     */
    private RemoteSynchronizedSymfonyAdapter $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->backend = $this->createMock(ExtendedBackendInterface::class);
        $this->model = new RemoteSynchronizedSymfonyAdapter($this->backend);
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
     * getLowLevelFrontend() returns a memoized low-level frontend wrapper.
     */
    public function testGetLowLevelFrontendReturnsMemoizedWrapper(): void
    {
        $first = $this->model->getLowLevelFrontend();
        $second = $this->model->getLowLevelFrontend();

        $this->assertInstanceOf(RemoteSynchronizedLowLevelFrontend::class, $first);
        $this->assertSame($first, $second, 'The low-level frontend must be created only once');
    }

    /**
     * loadMultiple() falls back to per-key loads when the backend has no loadMultiple(),
     * and omits misses (false values).
     */
    public function testLoadMultipleFallsBackToPerKeyLoads(): void
    {
        $this->backend->method('load')
            ->willReturnMap([
                ['id1', 'value1'],
                ['id2', false],
                ['id3', 'value3'],
            ]);

        $this->assertSame(
            ['id1' => 'value1', 'id3' => 'value3'],
            $this->model->loadMultiple(['id1', 'id2', 'id3'])
        );
    }
}
