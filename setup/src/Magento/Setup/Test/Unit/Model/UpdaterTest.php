<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Phrase;
use Magento\Setup\Model\Updater;

class UpdaterTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateUpdaterTaskUpdate()
    {
        $queue = $this->getMock('Magento\Setup\Model\Cron\Queue', [], [], '', false);
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
        $queue = $this->getMock('Magento\Setup\Model\Cron\Queue', [], [], '', false);
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
