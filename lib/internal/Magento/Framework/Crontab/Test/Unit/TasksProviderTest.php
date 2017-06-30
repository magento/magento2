<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Crontab\Test\Unit;

use Magento\Framework\Crontab\TasksProvider;

class TasksProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return void
     */
    public function testTasksProviderEmpty()
    {
        /** @var $tasksProvider $tasksProvider */
        $tasksProvider = new TasksProvider();
        $this->assertSame([], $tasksProvider->getTasks());
    }

    public function testTasksProvider()
    {
        $tasks = [
            'magentoCron' => ['expressin' => '* * * * *', 'command' => 'bin/magento cron:run'],
            'magentoSetup' => ['command' => 'bin/magento setup:cron:run'],
        ];

        /** @var $tasksProvider $tasksProvider */
        $tasksProvider = new TasksProvider($tasks);
        $this->assertSame($tasks, $tasksProvider->getTasks());
    }
}
