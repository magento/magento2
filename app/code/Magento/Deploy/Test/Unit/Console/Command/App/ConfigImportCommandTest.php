<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Deploy\Test\Unit\Console\Command\App;

use Magento\Deploy\Console\Command\App\ConfigImport\Processor;
use Magento\Deploy\Console\Command\App\ConfigImportCommand;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\RuntimeException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ConfigImportCommandTest extends TestCase
{
    /**
     * @var Processor|MockObject
     */
    private $processorMock;

    /**
     * @var CommandTester
     */
    private $commandTester;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->processorMock = $this->getMockBuilder(Processor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configImportCommand = new ConfigImportCommand($this->processorMock);

        $this->commandTester = new CommandTester($configImportCommand);
    }

    /**
     * @return void
     */
    public function testExecute()
    {
        $this->processorMock->expects($this->once())
            ->method('execute');

        $this->assertSame(Cli::RETURN_SUCCESS, $this->commandTester->execute([]));
    }

    /**
     * @return void
     */
    public function testExecuteWithException()
    {
        $this->processorMock->expects($this->once())
            ->method('execute')
            ->willThrowException(new RuntimeException(__('Some error')));

        $this->assertSame(Cli::RETURN_FAILURE, $this->commandTester->execute([]));
        $this->assertStringContainsString('Some error', $this->commandTester->getDisplay());
    }
}
