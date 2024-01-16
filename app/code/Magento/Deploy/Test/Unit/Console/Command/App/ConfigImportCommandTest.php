<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Deploy\Test\Unit\Console\Command\App;

use Magento\Config\Console\Command\EmulatedAdminhtmlAreaProcessor;
use Magento\Deploy\Console\Command\App\ConfigImport\Processor;
use Magento\Deploy\Console\Command\App\ConfigImportCommand;
use Magento\Framework\App\AreaList;
use Magento\Framework\App\DeploymentConfig;
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
     * @var DeploymentConfig|MockObject
     */
    private $deploymentConfigMock;

    /**
     * @var EmulatedAdminhtmlAreaProcessor|MockObject
     */
    private $adminhtmlAreaProcessorMock;

    /**
     * @var AreaList|MockObject
     */
    private $areaListMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->processorMock = $this->createMock(Processor::class);
        $this->deploymentConfigMock = $this->createMock(DeploymentConfig::class);
        $this->adminhtmlAreaProcessorMock = $this->createMock(EmulatedAdminhtmlAreaProcessor::class);
        $this->areaListMock = $this->createMock(AreaList::class);

        $configImportCommand = new ConfigImportCommand(
            $this->processorMock,
            $this->deploymentConfigMock,
            $this->adminhtmlAreaProcessorMock,
            $this->areaListMock
        );

        $this->commandTester = new CommandTester($configImportCommand);
    }

    /**
     * @return void
     */
    public function testExecute()
    {
        $this->deploymentConfigMock->expects($this->once())->method('isAvailable')->willReturn(true);
        $this->adminhtmlAreaProcessorMock->expects($this->once())
            ->method('process')->willReturnCallback(function (callable $callback, array $params = []) {
                return $callback(...$params);
            });
        $this->areaListMock->expects($this->once())->method('getCodes')->willReturn(['adminhtml']);

        $this->processorMock->expects($this->once())
            ->method('execute');

        $this->assertSame(Cli::RETURN_SUCCESS, $this->commandTester->execute([]));
    }

    /**
     * @return void
     */
    public function testExecuteWithException()
    {
        $this->deploymentConfigMock->expects($this->once())->method('isAvailable')->willReturn(true);
        $this->adminhtmlAreaProcessorMock->expects($this->once())
            ->method('process')->willReturnCallback(function (callable $callback, array $params = []) {
                return $callback(...$params);
            });
        $this->areaListMock->expects($this->once())->method('getCodes')->willReturn(['adminhtml']);

        $this->processorMock->expects($this->once())
            ->method('execute')
            ->willThrowException(new RuntimeException(__('Some error')));

        $this->assertSame(Cli::RETURN_FAILURE, $this->commandTester->execute([]));
        $this->assertStringContainsString('Some error', $this->commandTester->getDisplay());
    }

    /**
     * @return void
     */
    public function testExecuteWithDeploymentConfigNotAvailable()
    {
        $this->deploymentConfigMock->expects($this->once())->method('isAvailable')->willReturn(false);
        $this->adminhtmlAreaProcessorMock->expects($this->never())->method('process');
        $this->areaListMock->expects($this->never())->method('getCodes');

        $this->processorMock->expects($this->once())
            ->method('execute');

        $this->assertSame(Cli::RETURN_SUCCESS, $this->commandTester->execute([]));
    }

    /**
     * @return void
     */
    public function testExecuteWithMissingAdminhtmlLocale()
    {
        $this->deploymentConfigMock->expects($this->once())->method('isAvailable')->willReturn(true);
        $this->adminhtmlAreaProcessorMock->expects($this->never())->method('process');
        $this->areaListMock->expects($this->once())->method('getCodes')->willReturn([]);

        $this->processorMock->expects($this->once())
            ->method('execute');

        $this->assertSame(Cli::RETURN_SUCCESS, $this->commandTester->execute([]));
    }
}
