<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Console\Command;

use Magento\Config\Console\Command\ConfigSet\ProcessorFacade;
use Magento\Config\Console\Command\ConfigSet\ProcessorFacadeFactory;
use Magento\Config\Console\Command\ConfigSetCommand;
use Magento\Config\Console\Command\EmulatedAdminhtmlAreaProcessor;
use Magento\Config\Console\Command\LocaleEmulatorInterface;
use Magento\Deploy\Model\DeploymentConfig\ChangeDetector;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\ValidatorException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\MockObject as Mock;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Test for ConfigSetCommand.
 *
 * @see ConfigSetCommand
 */
class ConfigSetCommandTest extends TestCase
{
    /**
     * @var ConfigSetCommand
     */
    private $command;

    /**
     * @var EmulatedAdminhtmlAreaProcessor|Mock
     */
    private $emulatedAreProcessorMock;

    /**
     * @var ChangeDetector|Mock
     */
    private $changeDetectorMock;

    /**
     * @var ProcessorFacadeFactory|Mock
     */
    private $processorFacadeFactoryMock;

    /**
     * @var DeploymentConfig|Mock
     */
    private $deploymentConfigMock;

    /**
     * @var ProcessorFacade|Mock
     */
    private $processorFacadeMock;

    /**
     * @var LocaleEmulatorInterface|MockObject
     */
    private $localeEmulatorMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->emulatedAreProcessorMock = $this->getMockBuilder(EmulatedAdminhtmlAreaProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->changeDetectorMock = $this->getMockBuilder(ChangeDetector::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->processorFacadeFactoryMock = $this->getMockBuilder(ProcessorFacadeFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->processorFacadeMock = $this->getMockBuilder(ProcessorFacade::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->deploymentConfigMock = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->localeEmulatorMock = $this->getMockBuilder(LocaleEmulatorInterface::class)
            ->getMockForAbstractClass();

        $this->command = new ConfigSetCommand(
            $this->emulatedAreProcessorMock,
            $this->changeDetectorMock,
            $this->processorFacadeFactoryMock,
            $this->deploymentConfigMock,
            $this->localeEmulatorMock
        );
    }

    public function testExecute()
    {
        $this->deploymentConfigMock->expects($this->once())
            ->method('isAvailable')
            ->willReturn(true);
        $this->changeDetectorMock->expects($this->once())
            ->method('hasChanges')
            ->willReturn(false);
        $this->processorFacadeFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->processorFacadeMock);
        $this->processorFacadeMock->expects($this->once())
            ->method('processWithLockTarget')
            ->willReturn('Some message');
        $this->emulatedAreProcessorMock->expects($this->once())
            ->method('process')
            ->willReturnCallback(function ($function) {
                return $function();
            });
        $this->localeEmulatorMock->expects($this->once())
            ->method('emulate')
            ->willReturnCallback(function ($callback) {
                return $callback();
            });

        $tester = new CommandTester($this->command);
        $tester->execute([
            ConfigSetCommand::ARG_PATH => 'test/test/test',
            ConfigSetCommand::ARG_VALUE => 'value'
        ]);

        $this->assertStringContainsString(
            __('Some message')->render(),
            $tester->getDisplay()
        );
        $this->assertSame(Cli::RETURN_SUCCESS, $tester->getStatusCode());
    }

    public function testExecuteMagentoUninstalled()
    {
        $this->deploymentConfigMock->expects($this->once())
            ->method('isAvailable')
            ->willReturn(false);
        $this->emulatedAreProcessorMock->expects($this->never())
            ->method('process');

        $tester = new CommandTester($this->command);
        $tester->execute([
            ConfigSetCommand::ARG_PATH => 'test/test/test',
            ConfigSetCommand::ARG_VALUE => 'value'
        ]);

        $this->assertStringContainsString(
            __('You cannot run this command because the Magento application is not installed.')->render(),
            $tester->getDisplay()
        );
        $this->assertSame(Cli::RETURN_FAILURE, $tester->getStatusCode());
    }

    public function testExecuteNeedsRegeneration()
    {
        $this->deploymentConfigMock->expects($this->once())
            ->method('isAvailable')
            ->willReturn(true);
        $this->changeDetectorMock->expects($this->once())
            ->method('hasChanges')
            ->willReturn(true);
        $this->emulatedAreProcessorMock->expects($this->never())
            ->method('process');

        $tester = new CommandTester($this->command);
        $tester->execute([
            ConfigSetCommand::ARG_PATH => 'test/test/test',
            ConfigSetCommand::ARG_VALUE => 'value'
        ]);

        $this->assertStringContainsString(
            __('This command is unavailable right now.')->render(),
            $tester->getDisplay()
        );
        $this->assertSame(Cli::RETURN_FAILURE, $tester->getStatusCode());
    }

    public function testExecuteWithException()
    {
        $this->deploymentConfigMock->expects($this->once())
            ->method('isAvailable')
            ->willReturn(true);
        $this->changeDetectorMock->expects($this->once())
            ->method('hasChanges')
            ->willReturn(false);
        $this->emulatedAreProcessorMock->expects($this->once())
            ->method('process')
            ->willThrowException(new ValidatorException(__('The "test/test/test" path does not exists')));

        $tester = new CommandTester($this->command);
        $tester->execute([
            ConfigSetCommand::ARG_PATH => 'test/test/test',
            ConfigSetCommand::ARG_VALUE => 'value'
        ]);

        $this->assertStringContainsString(
            __('The "test/test/test" path does not exists')->render(),
            $tester->getDisplay()
        );
        $this->assertSame(Cli::RETURN_FAILURE, $tester->getStatusCode());
    }
}
