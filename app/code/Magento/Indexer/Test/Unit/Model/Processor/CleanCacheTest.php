<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Unit\Model\Processor;

use Magento\Indexer\Model\Indexer\DeferredCacheCleaner;
use Magento\Indexer\Model\Processor;
use Magento\Indexer\Model\Processor\CleanCache;
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
     * Subject mock
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
}
