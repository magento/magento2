<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Test\Unit;

use Magento\Framework\Setup\ConsoleLogger;

class ConsoleLoggerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\Console\Output\OutputInterface
     */
    private $console;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ConsoleLogger
     */
    private $consoleLoggerModel;

    protected function setUp(): void
    {
        $this->console = $this->createMock(\Symfony\Component\Console\Output\OutputInterface::class);
        $outputFormatter = $this->createMock(\Symfony\Component\Console\Formatter\OutputFormatterInterface::class);
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
        $exception = $this->createMock(\Exception::class);
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
