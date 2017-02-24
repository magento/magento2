<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Test\Unit;

use Magento\Framework\Setup\ConsoleLogger;

class ConsoleLoggerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\Console\Output\OutputInterface
     */
    private $console;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ConsoleLogger
     */
    private $consoleLoggerModel;

    protected function setUp()
    {
        $this->console = $this->getMock(\Symfony\Component\Console\Output\OutputInterface::class, [], [], '', false);
        $outputFormatter = $this->getMock(
            \Symfony\Component\Console\Formatter\OutputFormatterInterface::class,
            [],
            [],
            '',
            false
        );
        $this->console
            ->expects($this->once())
            ->method('getFormatter')
            ->willReturn($outputFormatter);
        $this->consoleLoggerModel = new ConsoleLogger($this->console);
    }

    public function testLogSuccess()
    {
        $this->console
            ->expects($this->once())
            ->method('writeln')
            ->with('<info>[SUCCESS]: Success message.</info>');
        $this->consoleLoggerModel->logSuccess('Success message.');
    }

    public function testLogError()
    {
        $exception = $this->getMock(\Exception::class, [], [], '', false);
        $this->console
            ->expects($this->once())
            ->method('writeln')
            ->with('<error>[ERROR]: </error>');
        $this->consoleLoggerModel->logError($exception);
    }

    public function testLog()
    {
        $this->console
            ->expects($this->once())
            ->method('writeln')
            ->with('<detail>Detail message.</detail>');
        $this->consoleLoggerModel->log('Detail message.');
    }

    public function testLogInline()
    {
        $this->console
            ->expects($this->once())
            ->method('write')
            ->with('<detail>Detail message.</detail>');
        $this->consoleLoggerModel->logInline('Detail message.');
    }

    public function testLogMeta()
    {
        $this->console
            ->expects($this->once())
            ->method('writeln')
            ->with('<metadata>Meta message.</metadata>');
        $this->consoleLoggerModel->logMeta('Meta message.');
    }
}
