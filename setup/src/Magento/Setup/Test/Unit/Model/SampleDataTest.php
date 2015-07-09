<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model;

use Magento\Setup\Model\SampleData;
/**
 * Test Magento\Setup\Model\SampleData
 */
class SampleDataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Setup\Model\SampleData
     */
    protected $sampleDataInstall;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerInterface;

    /**
     * @var \Magento\Framework\Setup\LoggerInterface
     */
    protected $loggerInterface;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;

    protected function setUp()
    {
        $this->objectManagerInterface = $this->getMockForAbstractClass('Magento\Framework\ObjectManagerInterface');
        $this->loggerInterface = $this->getMockForAbstractClass('Magento\Framework\Setup\LoggerInterface');
        $this->directoryList = $this->getMock('Magento\Framework\App\Filesystem\DirectoryList', [], [], '', false);
        $this->sampleDataInstall = new SampleData($this->directoryList);
    }

    public function testInstall()
    {
        $areaCode = 'adminhtml';
        $userName = 'admin';
        $modules = ['module_1', 'module_2'];
        $configData = ['config_data'];
        $sampleDataLogger = $this->getMock('Magento\SampleData\Model\Logger', ['setSubject'], [], '', false);
        $appState = $this->getMock('Magento\Framework\App\State', ['setAreaCode'], [], '', false);
        $configLoader = $this->getMock('Magento\Framework\App\ObjectManager\ConfigLoader', ['load'], [], '', false);
        $installer = $this->getMock('Magento\SampleData\Model\Installer', ['run'], [], '', false);
        $this->objectManagerInterface->expects($this->any())->method('get')->willReturnMap(
            [
                ['Magento\SampleData\Model\Logger', $sampleDataLogger],
                ['Magento\Framework\App\State', $appState],
                ['Magento\Framework\App\ObjectManager\ConfigLoader', $configLoader],
                ['Magento\SampleData\Model\Installer', $installer]
            ]
        );
        $sampleDataLogger->expects($this->any())->method('setSubject')->with($this->loggerInterface)->willReturnSelf();
        $appState->expects($this->once())->method('setAreaCode')->with($areaCode)->willReturnSelf();
        $configLoader->expects($this->once())->method('load')->with($areaCode)->willReturn($configData);
        $this->objectManagerInterface->expects($this->once())->method('configure')->with($configData)
            ->willReturnSelf();
        $installer->expects($this->once())->method('run')->with($userName, $modules);
        $this->sampleDataInstall->install($this->objectManagerInterface, $this->loggerInterface, $userName, $modules);
    }

    public function testIsDeployed()
    {
        $this->directoryList->expects($this->once())->method('getPath')->with('code');
        $this->sampleDataInstall->isDeployed();
    }

    /**
     * Test SampleData installation check method.
     * Can be tested only negative case because file_exists method used in the tested class
     */
    public function testIsInstalledSuccessfully()
    {
        $this->assertFalse($this->sampleDataInstall->isInstalledSuccessfully());
    }
}
