<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Test\Unit\Frontend\Adapter;

use Magento\Framework\Cache\Backend\ExtendedBackendInterface;
use Magento\Framework\Cache\Backend\SymfonyL2Cache;
use Magento\Framework\Cache\Frontend\Adapter\RemoteSynchronizedLowLevelFrontend;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Cache\LowLevelFrontendInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for RemoteSynchronizedLowLevelFrontend
 */
class RemoteSynchronizedLowLevelFrontendTest extends TestCase
{
    /**
     * @var ExtendedBackendInterface|MockObject
     */
    private $backend;

    /**
     * @var RemoteSynchronizedLowLevelFrontend
     */
    private RemoteSynchronizedLowLevelFrontend $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->backend = $this->createMock(ExtendedBackendInterface::class);
        $this->model = new RemoteSynchronizedLowLevelFrontend($this->backend);
    }

    /**
     * It must expose the common low-level frontend contract.
     */
    public function testImplementsLowLevelFrontendInterface(): void
    {
        $this->assertInstanceOf(LowLevelFrontendInterface::class, $this->model);
    }

    /**
     * clean() must delegate mode and tags to the backend and return its result.
     */
    public function testCleanDelegatesToBackend(): void
    {
        $this->backend->expects($this->once())
            ->method('clean')
            ->with('matchingTag', ['TAG_A'])
            ->willReturn(true);

        $this->assertTrue($this->model->clean('matchingTag', ['TAG_A']));
    }

    /**
     * clean() must forward its default arguments unchanged.
     */
    public function testCleanUsesDefaultArguments(): void
    {
        $this->backend->expects($this->once())
            ->method('clean')
            ->with('all', [])
            ->willReturn(false);

        $this->assertFalse($this->model->clean());
    }

    /**
     * getMetadatas() must delegate to the backend contract (guaranteed by ExtendedBackendInterface).
     */
    public function testGetMetadatasDelegatesToBackend(): void
    {
        $this->backend->expects($this->once())
            ->method('getMetadatas')
            ->with('id1')
            ->willReturn(['mtime' => 123]);

        $this->assertSame(['mtime' => 123], $this->model->getMetadatas('id1'));
    }

    /**
     * getBackend() must reach through backend->getRemote()->getLowLevelFrontend()->getBackend().
     */
    public function testGetBackendReachesThroughToRemoteLowLevelFrontendBackend(): void
    {
        $remoteBackend = new \stdClass();
        $remoteLowLevelFrontend = new class ($remoteBackend) {
            public function __construct(private $backend)
            {
            }

            public function getBackend()
            {
                return $this->backend;
            }
        };
        $remote = $this->createMock(FrontendInterface::class);
        $remote->method('getLowLevelFrontend')->willReturn($remoteLowLevelFrontend);

        // Use the real two-tier backend so getRemote() is exercised as in production
        // (SymfonyL2Cache is the ExtendedBackendInterface that exposes it).
        $backend = new SymfonyL2Cache($remote, $this->createMock(FrontendInterface::class));

        $model = new RemoteSynchronizedLowLevelFrontend($backend);

        $this->assertSame($remoteBackend, $model->getBackend());
    }

    /**
     * getBackend() must return null when the backend is not two-tier (no getRemote()).
     */
    public function testGetBackendReturnsNullWhenBackendIsNotTwoTier(): void
    {
        $this->assertNull($this->model->getBackend());
    }
}
