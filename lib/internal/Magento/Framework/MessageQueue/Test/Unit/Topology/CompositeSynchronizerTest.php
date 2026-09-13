<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue\Test\Unit\Topology;

use Magento\Framework\MessageQueue\Topology\CompositeSynchronizer;
use Magento\Framework\MessageQueue\Topology\SynchronizerInterface;
use PHPUnit\Framework\TestCase;

class CompositeSynchronizerTest extends TestCase
{
    public function testSynchronizeMergesChangesInOrder()
    {
        $first = $this->createMock(SynchronizerInterface::class);
        $first->expects($this->once())->method('synchronize')->willReturn(['first change']);
        $second = $this->createMock(SynchronizerInterface::class);
        $second->expects($this->once())->method('synchronize')->willReturn(['second change', 'third change']);

        $model = new CompositeSynchronizer(['first' => $first, 'second' => $second]);

        $this->assertSame(['first change', 'second change', 'third change'], $model->synchronize());
    }

    public function testSynchronizeWithoutChanges()
    {
        $synchronizer = $this->createMock(SynchronizerInterface::class);
        $synchronizer->expects($this->once())->method('synchronize')->willReturn([]);

        $model = new CompositeSynchronizer(['db' => $synchronizer]);

        $this->assertSame([], $model->synchronize());
    }

    public function testSynchronizeWithoutSynchronizers()
    {
        $this->assertSame([], (new CompositeSynchronizer())->synchronize());
    }

    public function testConstructorRejectsInvalidSynchronizer()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Synchronizer "broken" must implement ' . SynchronizerInterface::class . ', stdClass given.'
        );

        new CompositeSynchronizer(['broken' => new \stdClass()]);
    }
}
