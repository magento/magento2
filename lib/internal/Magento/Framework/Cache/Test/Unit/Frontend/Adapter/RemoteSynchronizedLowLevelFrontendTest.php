<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Test\Unit\Frontend\Adapter;

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
     * @var FrontendInterface|MockObject
     */
    private $frontend;

    /**
     * @var RemoteSynchronizedLowLevelFrontend
     */
    private RemoteSynchronizedLowLevelFrontend $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->frontend = $this->createMock(FrontendInterface::class);
        $this->model = new RemoteSynchronizedLowLevelFrontend($this->frontend);
    }

    /**
     * It must expose the common low-level frontend contract.
     */
    public function testImplementsLowLevelFrontendInterface(): void
    {
        $this->assertInstanceOf(LowLevelFrontendInterface::class, $this->model);
    }

    /**
     * clean() must delegate mode and tags to the wrapped frontend and return its result.
     */
    public function testCleanDelegatesToFrontend(): void
    {
        $this->frontend->expects($this->once())
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
        $this->frontend->expects($this->once())
            ->method('clean')
            ->with('all', [])
            ->willReturn(false);

        $this->assertFalse($this->model->clean());
    }

    /**
     * getBackend() must reach through frontend->getBackend()->getRemote()->getLowLevelFrontend()->getBackend().
     */
    public function testGetBackendReachesThroughToRemoteLowLevelFrontendBackend(): void
    {
        $remoteBackend = new class {
            public function getIdsMatchingTags(array $tags): array
            {
                return ['id1'];
            }
        };
        $remoteLowLevelFrontend = new class ($remoteBackend) {
            public function __construct(private $backend)
            {
            }

            public function getBackend()
            {
                return $this->backend;
            }
        };
        $remote = new class ($remoteLowLevelFrontend) {
            public function __construct(private $lowLevelFrontend)
            {
            }

            public function getLowLevelFrontend()
            {
                return $this->lowLevelFrontend;
            }
        };
        $backend = new class ($remote) {
            public function __construct(private $remote)
            {
            }

            public function getRemote()
            {
                return $this->remote;
            }
        };

        $this->frontend->expects($this->once())
            ->method('getBackend')
            ->willReturn($backend);

        $this->assertSame($remoteBackend, $this->model->getBackend());
    }

    /**
     * getBackend() must return null when the wrapped frontend can't expose a backend.
     */
    public function testGetBackendReturnsNullWhenUnavailable(): void
    {
        $this->assertNull($this->model->getBackend());
    }
}
