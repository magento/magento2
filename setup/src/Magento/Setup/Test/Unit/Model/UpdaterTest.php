<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model;

use Magento\Setup\Model\Cron\Queue;
use Magento\Setup\Model\Updater;
use PHPUnit\Framework\TestCase;

class UpdaterTest extends TestCase
{
    public function testCreateUpdaterTaskUpdate()
    {
        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('addJobs')
            ->with(
                [
                    [
                        'name' => 'update',
                        'params' => ['components' => [['name' => 'vendor/package', 'version' => 'dev-master']]]
                    ]
                ]
            );
        $updater = new Updater($queue);
        $updater->createUpdaterTask(
            [['name' => 'vendor/package', 'version' => 'dev-master']],
            Updater::TASK_TYPE_UPDATE
        );
    }

    public function testCreateUpdaterTaskUninstall()
    {
        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('addJobs')
            ->with(
                [
                    [
                        'name' => 'uninstall',
                        'params' => ['components' => [['name' => 'vendor/package']], 'dataOption' => true]
                    ]
                ]
            );
        $updater = new Updater($queue);
        $updater->createUpdaterTask(
            [['name' => 'vendor/package']],
            Updater::TASK_TYPE_UNINSTALL,
            ['dataOption' => true]
        );
    }
}
