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
}
