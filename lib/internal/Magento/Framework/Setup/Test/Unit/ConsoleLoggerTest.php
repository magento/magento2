<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Test\Unit;

use Magento\Framework\Setup\ConsoleLogger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleLoggerTest extends TestCase
{
    /**
     * @var MockObject|OutputInterface
     */
    private $console;

    /**
     * @var MockObject|ConsoleLogger
     */
    private $consoleLoggerModel;

    protected function setUp(): void
    {
        $this->console = $this->getMockForAbstractClass(OutputInterface::class);
        $outputFormatter = $this->getMockForAbstractClass(OutputFormatterInterface::class);
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
