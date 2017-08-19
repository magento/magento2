<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Test\Unit\Console\Command\App;

use Magento\Deploy\Console\Command\App\ConfigImportCommand;
use Magento\Deploy\Console\Command\App\ConfigImport\Processor;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\RuntimeException;
use Symfony\Component\Console\Tester\CommandTester;

class ConfigImportCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Processor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $processorMock;

    /**
     * @var CommandTester
     */
    private $commandTester;

    /**
     * @return void
     */
    protected function setUp()
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
        $this->assertContains('Some error', $this->commandTester->getDisplay());
    }
}
