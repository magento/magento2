<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;

class ConfigOptionsCollectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerProvider;

    public function setUp()
    {
        $this->objectManagerProvider = $this->getMock('Magento\Setup\Model\ObjectManagerProvider', [], [], '', false);
        $this->objectManagerProvider
            ->expects($this->any())
            ->method('get')
            ->willReturn(\Magento\TestFramework\Helper\Bootstrap::getObjectManager());
    }

    public function testCollectOptionsDeploymentConfigAvailable()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $moduleListMock = $this->getMock('Magento\Framework\Module\ModuleList', [], [], '', false);
        $moduleListMock->expects($this->once())->method('isModuleInfoAvailable')->willReturn(true);
        $moduleListMock->expects($this->once())->method('getNames')->willReturn(['Magento_Backend']);
        $fullModuleListMock = $this->getMock('Magento\Framework\Module\FullModuleList', [], [], '', false);
        $fullModuleListMock->expects($this->never())->method('getNames');
        /** @var \Magento\Setup\Model\ConfigOptionsCollector $object */
        $object = $objectManager->create(
            'Magento\Setup\Model\ConfigOptionsCollector',
            [
                'objectManagerProvider' => $this->objectManagerProvider,
                'moduleList' => $moduleListMock,
                'fullModuleList' => $fullModuleListMock,
            ]
        );
        $result = $object->collectOptions();

        $setupOptions = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Setup\Model\ConfigOptions');
        $backendOptions = new \Magento\Backend\Setup\ConfigOptions();
        $expected = [
            'setup' => $setupOptions,
            'Magento_Backend' => $backendOptions,
        ];

        $this->assertEquals($expected, $result);

    }

    public function testCollectOptionsDeploymentConfigUnavailable()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $moduleListMock = $this->getMock('Magento\Framework\Module\ModuleList', [], [], '', false);
        $moduleListMock->expects($this->once())->method('isModuleInfoAvailable')->willReturn(false);
        $moduleListMock->expects($this->never())->method('getNames');
        $object = $objectManager->create(
            'Magento\Setup\Model\ConfigOptionsCollector',
            [
                'objectManagerProvider' => $this->objectManagerProvider,
                'moduleList' => $moduleListMock,
            ]
        );
        $result = $object->collectOptions();

        $backendOptions = new \Magento\Backend\Setup\ConfigOptions();
        $expected = [
            'setup' => \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
                ->get('Magento\Setup\Model\ConfigOptions'),
            'Magento_Backend' => $backendOptions,
        ];

        $this->assertEquals($expected, $result);
    }
}
