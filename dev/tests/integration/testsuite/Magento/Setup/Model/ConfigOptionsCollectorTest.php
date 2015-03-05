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

    public function testCollectOptionsAllModules()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Setup\Model\ConfigOptionsCollector $object */
        $object = $objectManager->create(
            'Magento\Setup\Model\ConfigOptionsCollector',
            ['objectManagerProvider' => $this->objectManagerProvider]
        );
        $result = $object->collectOptions(true);

        $setupOptions = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Setup\Model\ConfigOptions');
        $backendOptions = new \Magento\Backend\Setup\ConfigOptions();
        $configOptions = new \Magento\Config\Setup\ConfigOptions();
        $expected = [
            'setup' => $setupOptions,
            'Magento_Backend' => $backendOptions,
            'Magento_Config' => $configOptions,
        ];

        $this->assertEquals($expected, $result);

    }

    public function testCollectOptionsEnabledModules()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $moduleListMock = $this->getMock('Magento\Framework\Module\ModuleList', [], [], '', false);
        $moduleListMock->expects($this->once())->method('getNames')->willReturn([]);
        $object = $objectManager->create(
            'Magento\Setup\Model\ConfigOptionsCollector',
            [
                'objectManagerProvider' => $this->objectManagerProvider,
                'moduleList' => $moduleListMock,
            ]
        );
        $result = $object->collectOptions(false);

        $expected = [
            'setup' => \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
                ->get('Magento\Setup\Model\ConfigOptions'),
        ];

        $this->assertEquals($expected, $result);
    }
}
