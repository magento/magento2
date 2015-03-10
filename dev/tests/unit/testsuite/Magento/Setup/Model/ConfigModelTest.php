<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Magento\Framework\Config\Data\ConfigData;

class ConfigModelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\ConfigModel
     */
    private $configModel;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\ConfigOptionsCollector
     */
    private $collector;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\DeploymentConfig\Writer
     */
    private $writer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject |\Magento\Framework\Config\Data\ConfigData
     */
    private $configData;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Backend\Setup\ConfigOptions
     */
    private $configOptions;

    public function setUp()
    {
        $this->collector = $this->getMock('Magento\Setup\Model\ConfigOptionsCollector', [], [], '', false);
        $this->writer = $this->getMock('Magento\Framework\App\DeploymentConfig\Writer', [], [], '', false);
        $this->configOptions = $this->getMock('Magento\Backend\Setup\ConfigOptions', [], [], '', false);
        $this->configData = $this->getMock('Magento\Framework\Config\Data\ConfigData', [], [], '', false);

        $this->configModel = new ConfigModel($this->collector, $this->writer);
    }

    public function testValidate()
    {
        $option = $this->getMock('Magento\Framework\Setup\Option\TextConfigOption', [], [], '', false);
        $option->expects($this->exactly(3))->method('getName')->willReturn('Fake');
        $optionsSet = [
            $option,
            $option,
            $option
        ];
        $configOption = $this->configOptions;
        $configOption->expects($this->once())->method('getOptions')->will($this->returnValue($optionsSet));
        $configOption->expects($this->once())->method('validate')->will($this->returnValue([]));

        $this->collector
            ->expects($this->exactly(2))
            ->method('collectOptions')
            ->will($this->returnValue([$configOption]));

        $this->configModel->validate(['Fake' => null]);
    }

    public function testProcess()
    {
        $dataSet = [
            'test' => 'fake'
        ];
        $configData = $this->configData;
        $configData->expects($this->once())->method('getData')->will($this->returnValue($dataSet));

        $configOption = $this->configOptions;
        $configOption->expects($this->once())
            ->method('createConfig')
            ->will($this->returnValue([$configData]));

        $configOptions = [
            'Fake_Module' => $configOption
        ];

        $this->collector->expects($this->once())->method('collectOptions')->will($this->returnValue($configOptions));
        $this->writer->expects($this->once())->method('saveConfig');

        $this->configModel->process([]);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage In module : Fake_ModuleConfigOption::createConfig
     */
    public function testProcessException()
    {
        $configOption = $this->configOptions;
        $configOption->expects($this->once())
            ->method('createConfig')
            ->will($this->returnValue([null]));

        $wrongData = [
            'Fake_Module' => $configOption
        ];

        $this->collector->expects($this->once())->method('collectOptions')->will($this->returnValue($wrongData));

        $this->configModel->process([]);
    }
}
