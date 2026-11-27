<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Plugin\Search;

use Magento\CatalogSearch\Plugin\Search\SkipQueryLoggingForBots;
use Magento\Customer\Model\Visitor;
use Magento\Search\Model\Query;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SkipQueryLoggingForBotsTest extends TestCase
{
    /**
     * @var Visitor|MockObject
     */
    private $visitorMock;

    /**
     * @var Query|MockObject
     */
    private $queryMock;

    /**
     * @var SkipQueryLoggingForBots
     */
    private $plugin;

    protected function setUp(): void
    {
        $this->visitorMock = $this->createMock(Visitor::class);
        $this->queryMock = $this->createMock(Query::class);
        $this->plugin = new SkipQueryLoggingForBots($this->visitorMock);
    }

    public function testSaveIncrementalPopularitySkippedForBots(): void
    {
        $this->visitorMock->method('isRequestLoggingSkipped')->willReturn(true);
        $proceed = function () {
            $this->fail('Popularity must not be saved for bots');
        };

        $this->assertSame(
            $this->queryMock,
            $this->plugin->aroundSaveIncrementalPopularity($this->queryMock, $proceed)
        );
    }

    public function testSaveIncrementalPopularityProceedsForVisitors(): void
    {
        $this->visitorMock->method('isRequestLoggingSkipped')->willReturn(false);
        $proceed = fn () => $this->queryMock;

        $this->assertSame(
            $this->queryMock,
            $this->plugin->aroundSaveIncrementalPopularity($this->queryMock, $proceed)
        );
    }

    public function testSaveNumResultsSkippedForBots(): void
    {
        $this->visitorMock->method('isRequestLoggingSkipped')->willReturn(true);
        $this->queryMock->expects($this->once())->method('__call')
            ->with('setNumResults', [5])
            ->willReturnSelf();
        $proceed = function () {
            $this->fail('Number of results must not be saved for bots');
        };

        $this->assertSame($this->queryMock, $this->plugin->aroundSaveNumResults($this->queryMock, $proceed, 5));
    }

    public function testSaveNumResultsProceedsForVisitors(): void
    {
        $this->visitorMock->method('isRequestLoggingSkipped')->willReturn(false);
        $proceed = function ($numResults) {
            $this->assertSame(5, $numResults);
            return $this->queryMock;
        };

        $this->assertSame($this->queryMock, $this->plugin->aroundSaveNumResults($this->queryMock, $proceed, 5));
    }
}
