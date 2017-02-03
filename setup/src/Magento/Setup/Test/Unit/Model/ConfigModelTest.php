<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model;

use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Setup\Model\ConfigModel;

class ConfigModelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\ConfigModel
     */
    private $configModel;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\ConfigOptionsListCollector
     */
    private $collector;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\DeploymentConfig\Writer
     */
    private $writer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject |\Magento\Framework\Config\Data\ConfigData
     */
    private $configData;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\FilePermissions
     */
    private $filePermissions;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Backend\Setup\ConfigOptionsList
     */
    private $configOptionsList;

    public function setUp()
    {
        $this->collector = $this->getMock('Magento\Setup\Model\ConfigOptionsListCollector', [], [], '', false);
        $this->writer = $this->getMock('Magento\Framework\App\DeploymentConfig\Writer', [], [], '', false);
        $this->deploymentConfig = $this->getMock('Magento\Framework\App\DeploymentConfig', [], [], '', false);
        $this->configOptionsList = $this->getMock('Magento\Backend\Setup\ConfigOptionsList', [], [], '', false);
        $this->configData = $this->getMock('Magento\Framework\Config\Data\ConfigData', [], [], '', false);
        $this->filePermissions = $this->getMock('\Magento\Setup\Model\FilePermissions', [], [], '', false);

        $this->deploymentConfig->expects($this->any())->method('get');

        $this->configModel = new ConfigModel(
            $this->collector,
            $this->writer,
            $this->deploymentConfig,
            $this->filePermissions
        );
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
        $configOption = $this->configOptionsList;
        $configOption->expects($this->once())->method('getOptions')->will($this->returnValue($optionsSet));
        $configOption->expects($this->once())->method('validate')->will($this->returnValue([]));

        $this->collector
            ->expects($this->exactly(2))
            ->method('collectOptionsLists')
            ->will($this->returnValue([$configOption]));

        $this->configModel->validate(['Fake' => null]);
    }

    public function testProcess()
    {
        $testSet1 = [
            ConfigFilePool::APP_CONFIG => [
                'segment' => [
                    'someKey' => 'value',
                    'test' => 'value1'
                ]
            ]
        ];

        $testSet2 = [
            ConfigFilePool::APP_CONFIG => [
                'segment' => [
                    'test' => 'value2'
                ]
            ]
        ];

        $testSetExpected1 = [
            ConfigFilePool::APP_CONFIG => [
                'segment' => [
                    'someKey' => 'value',
                    'test' => 'value1'
                ]
            ]
        ];

        $testSetExpected2 = [
            ConfigFilePool::APP_CONFIG => [
                'segment' => [
                    'test' => 'value2'
                ]
            ]
        ];

        $configData1 = clone $this->configData;
        $configData2 = clone $this->configData;

        $configData1->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($testSet1[ConfigFilePool::APP_CONFIG]));
        $configData1->expects($this->any())->method('getFileKey')->will($this->returnValue(ConfigFilePool::APP_CONFIG));
        $configData1->expects($this->once())->method('isOverrideWhenSave')->willReturn(false);

        $configData2->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($testSet2[ConfigFilePool::APP_CONFIG]));
        $configData2->expects($this->any())->method('getFileKey')->will($this->returnValue(ConfigFilePool::APP_CONFIG));
        $configData2->expects($this->once())->method('isOverrideWhenSave')->willReturn(false);

        $configOption = $this->configOptionsList;
        $configOption->expects($this->once())
            ->method('createConfig')
            ->will($this->returnValue([$configData1, $configData2]));

        $configOptionsList = [
            'Fake_Module' => $configOption
        ];
        $this->collector->expects($this->once())
            ->method('collectOptionsLists')
            ->will($this->returnValue($configOptionsList));

        $this->writer->expects($this->at(0))->method('saveConfig')->with($testSetExpected1);
        $this->writer->expects($this->at(1))->method('saveConfig')->with($testSetExpected2);

        $this->configModel->process([]);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage In module : Fake_ModuleConfigOption::createConfig
     */
    public function testProcessException()
    {
        $configOption = $this->configOptionsList;
        $configOption->expects($this->once())
            ->method('createConfig')
            ->will($this->returnValue([null]));

        $wrongData = [
            'Fake_Module' => $configOption
        ];

        $this->collector->expects($this->once())->method('collectOptionsLists')->will($this->returnValue($wrongData));

        $this->configModel->process([]);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Missing write permissions to the following directories: '/a/ro/dir', '/media'
     */
    public function testWritePermissionErrors()
    {
        $this->filePermissions->expects($this->once())->method('getMissingWritableDirectoriesForInstallation')
            ->willReturn(['/a/ro/dir', '/media']);
        $this->configModel->process([]);
    }
}
