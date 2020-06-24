<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Module\ModuleList;
use Magento\Framework\Setup\Option\TextConfigOption;
use Magento\Setup\Console\Command\ConfigSetCommand;
use Magento\Setup\Model\ConfigModel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Tester\CommandTester;

class ConfigSetCommandTest extends TestCase
{
    /**
     * @var MockObject|ConfigModel
     */
    private $configModel;

    /**
     * @var MockObject|DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var MockObject|ConfigSetCommand
     */
    private $command;

    protected function setUp(): void
    {
        $option = $this->createMock(TextConfigOption::class);
        $option
            ->expects($this->any())
            ->method('getName')
            ->willReturn('db-host');
        $this->configModel = $this->createMock(ConfigModel::class);
        $this->configModel
            ->expects($this->exactly(2))
            ->method('getAvailableOptions')
            ->willReturn([$option]);
        $moduleList = $this->createMock(ModuleList::class);
        $this->deploymentConfig = $this->createMock(DeploymentConfig::class);
        $this->command = new ConfigSetCommand($this->configModel, $moduleList, $this->deploymentConfig);
    }

    public function testExecuteNoInteractive()
    {
        $this->deploymentConfig
            ->expects($this->once())
            ->method('get')
            ->willReturn(null);
        $this->configModel
            ->expects($this->once())
            ->method('process')
            ->with(['db-host' => 'host']);
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['--db-host' => 'host']);
        $this->assertSame(
            'You saved the new configuration.' . PHP_EOL,
            $commandTester->getDisplay()
        );
    }

    public function testExecuteInteractiveWithYes()
    {
        $this->deploymentConfig
            ->expects($this->once())
            ->method('get')
            ->willReturn('localhost');
        $this->configModel
            ->expects($this->once())
            ->method('process')
            ->with(['db-host' => 'host']);
        $this->checkInteraction('Y');
    }

    public function testExecuteInteractiveWithNo()
    {
        $this->deploymentConfig
            ->expects($this->once())
            ->method('get')
            ->willReturn('localhost');
        $this->configModel
            ->expects($this->once())
            ->method('process')
            ->with([]);
        $this->checkInteraction('n');
    }

    /**
     * Checks interaction with users on CLI
     *
     * @param string $interactionType
     * @return void
     */
    private function checkInteraction($interactionType)
    {
        $dialog = $this->createMock(QuestionHelper::class);
        $dialog
            ->expects($this->once())
            ->method('ask')
            ->willReturn($interactionType);

        /** @var HelperSet|MockObject $helperSet */
        $helperSet = $this->createMock(HelperSet::class);
        $helperSet
            ->expects($this->once())
            ->method('get')
            ->with('question')
            ->willReturn($dialog);
        $this->command->setHelperSet($helperSet);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['--db-host' => 'host']);
        if (strtolower($interactionType) === 'y') {
            $message = 'You saved the new configuration.' . PHP_EOL;
        } else {
            $message = 'You made no changes to the configuration.' . PHP_EOL;
        }
        $this->assertSame(
            $message,
            $commandTester->getDisplay()
        );
    }
}
