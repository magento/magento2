<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Framework\Module\ModuleList;
use Magento\Setup\Console\Command\ConfigSetCommand;
use Symfony\Component\Console\Tester\CommandTester;

class ConfigSetCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\ConfigModel
     */
    private $configModel;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Console\Command\ConfigSetCommand
     */
    private $command;

    public function setUp()
    {
        $option = $this->getMock('Magento\Framework\Setup\Option\TextConfigOption', [], [], '', false);
        $option
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('db_host'));
        $this->configModel = $this->getMock('Magento\Setup\Model\ConfigModel', [], [], '', false);
        $this->configModel
            ->expects($this->exactly(2))
            ->method('getAvailableOptions')
            ->will($this->returnValue([$option]));
        $moduleList = $this->getMock('Magento\Framework\Module\ModuleList', [], [], '', false);
        $deploymentConfig = $this->getMock('Magento\Framework\App\DeploymentConfig', [], [], '', false);
        $deploymentConfig
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue('localhost'));
        $this->command = new ConfigSetCommand($this->configModel, $moduleList, $deploymentConfig);
    }

    public function testExecuteNoInteractive()
    {
        $this->configModel
            ->expects($this->once())
            ->method('process')
            ->with([]);
        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);
        $this->assertSame(
            'No module configuration is available, so all modules are enabled.' . PHP_EOL
            . 'You saved the deployment config.' . PHP_EOL,
            $commandTester->getDisplay()
        );
    }

    public function testExecuteInteractiveWithYes()
    {
        $this->configModel
            ->expects($this->once())
            ->method('process')
            ->with(['db_host' => 'host']);
        $this->checkInteraction(true);
    }

    public function testExecuteInteractiveWithNo()
    {
        $this->configModel
            ->expects($this->once())
            ->method('process')
            ->with([]);
        $this->checkInteraction(false);
    }

    /**
     * Checks interaction with users on CLI
     *
     * @param bool $interactionType
     * @return void
     */
    private function checkInteraction($interactionType)
    {
        $dialog = $this->getMock('Symfony\Component\Console\Helper\DialogHelper', [], [], '', false);
        $dialog
            ->expects($this->once())
            ->method('askConfirmation')
            ->will($this->returnValue($interactionType));

        /** @var \Symfony\Component\Console\Helper\HelperSet|\PHPUnit_Framework_MockObject_MockObject $helperSet */
        $helperSet = $this->getMock('Symfony\Component\Console\Helper\HelperSet', [], [], '', false);
        $helperSet
            ->expects($this->once())
            ->method('get')
            ->with('dialog')
            ->will($this->returnValue($dialog));
        $this->command->setHelperSet($helperSet);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['--db_host' => 'host']);
        $this->assertSame(
            'No module configuration is available, so all modules are enabled.' . PHP_EOL
            . 'You saved the deployment config.' . PHP_EOL,
            $commandTester->getDisplay()
        );
    }
}
