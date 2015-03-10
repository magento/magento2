<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Console\Command;

use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Module\ModuleList;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigInstallCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\ConfigModel
     */
    private $configModel;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject| ConfigFilePool
     */
    private $configFilePool;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Module\ModuleList
     */
    private $moduleList;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|InputInterface
     */
    private $input;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|OutputInterface
     */
    private $output;

    public function setUp()
    {
        $this->configModel = $this->getMock('Magento\Setup\Model\ConfigModel', [], [], '', false);
        $this->configFilePool = $this->getMock('Magento\Framework\Config\File\ConfigFilePool', [], [], '', false);
        $this->moduleList = $this->getMock('Magento\Framework\Module\ModuleList', [], [], '', false);
        $this->input = $this->getMock('Symfony\Component\Console\Input\InputInterface', [], [], '', false);
        $this->output = $this->getMock('Symfony\Component\Console\Output\OutputInterface', [], [], '', false);
    }

    public function testInitialize()
    {
        $option = $this->getMock('Magento\Framework\Setup\Option\TextConfigOption', [], [], '', false);
        $optionsSet = [
            $option
        ];
        $this->input->expects($this->once())->method('getOptions')->will($this->returnValue([]));

        $this->moduleList->expects($this->once())->method('isModuleInfoAvailable')->will($this->returnValue(false));

        $this->configModel->expects($this->once())
            ->method('validate')
            ->will($this->returnValue([]));
        $this->configModel
            ->expects($this->once())
            ->method('getAvailableOptions')
            ->will($this->returnValue($optionsSet));

        $this->output->expects($this->once())
            ->method('writeln')
            ->with('<info>No module configuration is available, so all modules are enabled.</info>');

        $command = new ConfigInstallCommand($this->configModel, $this->moduleList);
        $command->initialize($this->input, $this->output);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Parameters validation is failed
     */
    public function testInitializeFailedValidation()
    {
        $option = $this->getMock('Magento\Framework\Setup\Option\TextConfigOption', [], [], '', false);
        $optionsSet = [
            $option
        ];

        $this->input->expects($this->once())->method('getOptions')->will($this->returnValue([]));

        $this->moduleList->expects($this->once())->method('isModuleInfoAvailable')->will($this->returnValue(true));

        $this->configModel->expects($this->once())
            ->method('validate')
            ->will($this->returnValue(['Error message']));

        $this->output->expects($this->once())->method('writeln')->with('<error>Error message</error>');

        $this->configModel
            ->expects($this->once())
            ->method('getAvailableOptions')
            ->will($this->returnValue($optionsSet));

        $command = new ConfigInstallCommand($this->configModel, $this->moduleList);
        $command->initialize($this->input, $this->output);
    }
}
