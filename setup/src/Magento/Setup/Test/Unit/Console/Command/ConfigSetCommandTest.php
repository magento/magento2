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
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Module\ModuleList
     */
    private $moduleList;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\DeploymentConfig
     */
    private $option;

    public function setUp()
    {
        $this->option = $this->getMock('Magento\Framework\Setup\Option\TextConfigOption', [], [], '', false);
        $this->configModel = $this->getMock('Magento\Setup\Model\ConfigModel', [], [], '', false);
        $this->configModel
            ->expects($this->exactly(2))
            ->method('getAvailableOptions')
            ->will($this->returnValue([$this->option]));
        $this->moduleList = $this->getMock('Magento\Framework\Module\ModuleList', [], [], '', false);
        $this->deploymentConfig = $this->getMock('Magento\Framework\App\DeploymentConfig', [], [], '', false);
        $this->deploymentConfig
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue('localhost'));
    }

    public function testExecuteNoInteractive()
    {
        $command = new ConfigSetCommand($this->configModel, $this->moduleList, $this->deploymentConfig);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        $this->assertSame(
            'No module configuration is available, so all modules are enabled.' . PHP_EOL
            . 'You saved the deployment config.' . PHP_EOL,
            $commandTester->getDisplay()
        );
    }

    public function testExecuteInteractiveWithYes()
    {
        $this->option
            ->expects($this->exactly(7))
            ->method('getName')
            ->will($this->returnValue('db_host'));
        $this->checkInteraction(true);
    }

    public function testExecuteInteractiveWithNo()
    {
        $this->option
            ->expects($this->exactly(8))
            ->method('getName')
            ->will($this->returnValue('db_host'));
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
        $command = new ConfigSetCommand($this->configModel, $this->moduleList, $this->deploymentConfig);
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
        $command->setHelperSet($helperSet);

        $commandTester = new CommandTester($command);
        $commandTester->execute(['--db_host' => 'host']);
        $this->assertSame(
            'No module configuration is available, so all modules are enabled.' . PHP_EOL
            . 'You saved the deployment config.' . PHP_EOL,
            $commandTester->getDisplay()
        );
    }
}
