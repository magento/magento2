<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Test\Unit\Console\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Magento\Cron\Console\Command\CronRemoveCommand;
use Magento\Framework\Crontab\CrontabManagerInterface;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class CronRemoveCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CrontabManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $crontabManagerMock;

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

        $this->commandTester = new CommandTester(
            new CronRemoveCommand($this->crontabManagerMock)
        );
    }

    /**
     * @return void
     */
    public function testExecute()
    {
        $this->crontabManagerMock->expects($this->once())
            ->method('RemoveTasks');

        $this->commandTester->execute([]);
        $this->assertEquals(
            'Magento cron tasks have been removed' . PHP_EOL,
            $this->commandTester->getDisplay()
        );
        $this->assertEquals(Cli::RETURN_SUCCESS, $this->commandTester->getStatusCode());
    }

    /**
     * @return void
     */
    public function testExecuteFailed()
    {
        $this->crontabManagerMock->expects($this->once())
            ->method('RemoveTasks')
            ->willThrowException(new LocalizedException(new Phrase('Some error')));

        $this->commandTester->execute([]);
        $this->assertEquals(
            'Some error' . PHP_EOL,
            $this->commandTester->getDisplay()
        );
        $this->assertEquals(Cli::RETURN_FAILURE, $this->commandTester->getStatusCode());
    }
}
