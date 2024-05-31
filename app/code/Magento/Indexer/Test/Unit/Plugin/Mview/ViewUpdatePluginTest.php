<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Unit\Plugin\Mview;

use Magento\Framework\Indexer\ConfigInterface;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Indexer\StateInterface;
use Magento\Framework\Mview\ViewInterface;
use Magento\Indexer\Plugin\Mview\ViewUpdatePlugin;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ViewUpdatePluginTest extends TestCase
{
    /**
     * @var ViewUpdatePlugin|MockObject
     */
    private $plugin;

    /**
     * @var IndexerRegistry|MockObject
     */
    private $indexerRegistryMock;

    /**
     * @var ConfigInterface|MockObject
     */
    private $configMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->indexerRegistryMock = $this->createMock(IndexerRegistry::class);
        $this->configMock = $this->createMock(ConfigInterface::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $this->plugin = new ViewUpdatePlugin(
            $this->indexerRegistryMock,
            $this->configMock,
            $this->loggerMock
        );
    }

    /**
     * Tests the behavior when the associated indexer is suspended.
     */
    public function testAroundUpdateWithSuspendedIndexer(): void
    {
        $viewId = 'test_view';
        $indexerId = 'test_indexer';
        $viewMock = $this->createMock(ViewInterface::class);
        $indexerMock = $this->createMock(IndexerInterface::class);

        $viewMock->expects($this->once())->method('getId')->willReturn($viewId);
        $this->configMock->expects($this->once())
            ->method('getIndexers')
            ->willReturn([$indexerId => ['view_id' => $viewId]]);

        $this->indexerRegistryMock->expects($this->once())
            ->method('get')
            ->with($indexerId)
            ->willReturn($indexerMock);

        $indexerMock->expects($this->once())
            ->method('getStatus')
            ->willReturn(StateInterface::STATUS_SUSPENDED);

        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with($this->stringContains("Suspended status detected for indexer"));

        $proceed = function () {
            // This should not be called
            $this->fail('Proceed should not be called when indexer is suspended');
        };

        $this->plugin->aroundUpdate($viewMock, $proceed);
    }

    /**
     * Tests the behavior when the associated indexer is not suspended.
     */
    public function testAroundUpdateWithNonSuspendedIndexer(): void
    {
        $viewId = 'test_view';
        $indexerId = 'test_indexer';
        $viewMock = $this->createMock(ViewInterface::class);
        $indexerMock = $this->createMock(IndexerInterface::class);

        $viewMock->expects($this->once())->method('getId')->willReturn($viewId);
        $this->configMock->expects($this->once())
            ->method('getIndexers')
            ->willReturn([$indexerId => ['view_id' => $viewId]]);

        $this->indexerRegistryMock->expects($this->once())
            ->method('get')
            ->with($indexerId)
            ->willReturn($indexerMock);

        $indexerMock->expects($this->once())
            ->method('getStatus')
            ->willReturn(StateInterface::STATUS_VALID);

        $proceedCalled = false;
        $proceed = function () use (&$proceedCalled) {
            $proceedCalled = true;
        };

        $this->plugin->aroundUpdate($viewMock, $proceed);

        $this->assertTrue($proceedCalled, 'Proceed should be called when indexer is not suspended');
    }

    /**
     * Tests the behavior when an indexer sharing the same shared_index is suspended.
     */
    public function testAroundUpdateWithSharedIndexSuspended(): void
    {
        $viewId = 'test_view';
        $indexerId = 'test_indexer';
        $sharedIndexId = 'shared_index';
        $viewMock = $this->createMock(ViewInterface::class);

        $viewMock->expects($this->once())->method('getId')->willReturn($viewId);

        $this->configMock->method('getIndexers')->willReturnOnConsecutiveCalls(
            [
                $indexerId => ['view_id' => $viewId]
            ],
            [
                'test_indexer' => [
                    'view_id' => 'test_view',
                    'shared_index' => 'shared_index'
                ],
                'another_test_indexer' => [
                    'view_id' => 'another_view_id',
                    'shared_index' => 'shared_index',
                ]
            ]
        );

        $this->configMock->expects($this->once())
            ->method('getIndexer')
            ->willReturn(['view_id' => $viewId, 'shared_index' => $sharedIndexId]);

        $indexerMock1 = $this->createMock(IndexerInterface::class);
        $indexerMock2 = $this->createMock(IndexerInterface::class);
        $indexerMock3 = $this->createMock(IndexerInterface::class);
        $this->indexerRegistryMock->expects($this->exactly(3))
            ->method('get')
            ->willReturnOnConsecutiveCalls($indexerMock1, $indexerMock2, $indexerMock3);
        $indexerMock1->expects($this->once())
            ->method('getStatus')
            ->willReturn(StateInterface::STATUS_INVALID);
        $indexerMock2->expects($this->once())
            ->method('getStatus')
            ->willReturn(StateInterface::STATUS_INVALID);
        $indexerMock3->expects($this->once())
            ->method('getStatus')
            ->willReturn(StateInterface::STATUS_SUSPENDED);

        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with($this->stringContains("Suspended status detected for indexer"));

        $proceed = function () {
            // This should not be called
            $this->fail('Proceed should not be called when any indexer with the same shared_index is suspended');
        };

        $this->plugin->aroundUpdate($viewMock, $proceed);
    }

    /**
     * Tests that the proceed function is called when there's no matching indexer ID for the given view ID.
     */
    public function testAroundUpdateProceedsWhenIndexerIdIsNull()
    {
        $viewMock = $this->createMock(ViewInterface::class);
        $viewMock->method('getId')->willReturn('nonexistent_view_id');

        $this->configMock->method('getIndexers')->willReturn([]);

        // The proceed function should be called since no matching indexer ID was found
        $proceedCalled = false;
        $proceed = function () use (&$proceedCalled) {
            $proceedCalled = true;
        };

        $this->plugin->aroundUpdate($viewMock, $proceed);

        $this->assertTrue($proceedCalled, 'Proceed should have been called when no matching indexer ID is found.');
    }
}
