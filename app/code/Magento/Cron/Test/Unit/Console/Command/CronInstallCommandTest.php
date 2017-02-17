<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Test\Unit\Console\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Magento\Cron\Console\Command\CronInstallCommand;
use Magento\Framework\Crontab\CrontabManagerInterface;
use Magento\Framework\Crontab\TasksProviderInterface;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class CronInstallCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CrontabManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $crontabManagerMock;

    /**
     * @var TasksProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tasksProviderMock;

    /**
     * @var CommandTester
     */
    private $commandTester;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->crontabManagerMock = $this->getMockBuilder(CrontabManagerInterface::class)
            ->getMockForAbstractClass();
        $this->tasksProviderMock = $this->getMockBuilder(TasksProviderInterface::class)
            ->getMockForAbstractClass();

        $this->commandTester = new CommandTester(
            new CronInstallCommand($this->crontabManagerMock, $this->tasksProviderMock)
        );
    }

    /**
     * @return void
     */
    public function testExecuteAlreadyInstalled()
    {
        $this->crontabManagerMock->expects($this->once())
            ->method('getTasks')
            ->willReturn([['* * * * * /bin/php /var/run.php']]);
        $this->tasksProviderMock->expects($this->never())
            ->method('getTasks');

        $this->commandTester->execute([]);
        $this->assertEquals(
            'Crontab has already been generated and saved' . PHP_EOL,
            $this->commandTester->getDisplay()
        );
        $this->assertEquals(Cli::RETURN_FAILURE, $this->commandTester->getStatusCode());
    }

    /**
     * @return void
     */
    public function testExecuteWithException()
    {
        $this->crontabManagerMock->expects($this->once())
            ->method('getTasks')
            ->willReturn([]);
        $this->tasksProviderMock->expects($this->once())
            ->method('getTasks')
            ->willReturn([]);
        $this->crontabManagerMock->expects($this->once())
            ->method('saveTasks')
            ->willThrowException(new LocalizedException(new Phrase('Some error')));

        $this->commandTester->execute([]);
        $this->assertEquals(
            'Some error' . PHP_EOL,
            $this->commandTester->getDisplay()
        );
        $this->assertEquals(Cli::RETURN_FAILURE, $this->commandTester->getStatusCode());
    }

    /**
     * @param array $existingTasks
     * @param array $options
     * @return void
     * @dataProvider executeDataProvider
     */
    public function testExecute($existingTasks, $options)
    {
        $this->crontabManagerMock->expects($this->once())
            ->method('getTasks')
            ->willReturn($existingTasks);
        $this->tasksProviderMock->expects($this->once())
            ->method('getTasks')
            ->willReturn([]);
        $this->crontabManagerMock->expects($this->once())
            ->method('saveTasks')
            ->with([]);

        $this->commandTester->execute($options);
        $this->assertEquals(
            'Crontab has been generated and saved' . PHP_EOL,
            $this->commandTester->getDisplay()
        );
        $this->assertEquals(Cli::RETURN_SUCCESS, $this->commandTester->getStatusCode());
    }

    /**
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            ['existingTasks' => [], 'options' => []],
            ['existingTasks' => ['* * * * * /bin/php /var/www/run.php'], 'options' => ['-f'=> true]]
        ];
    }
}
