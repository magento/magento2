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

    /**
     * @var array
     */
    private $expected;

    public function setUp()
    {
        $this->objectManagerProvider = $this->getMock('Magento\Setup\Model\ObjectManagerProvider', [], [], '', false);
        $this->objectManagerProvider
            ->expects($this->any())
            ->method('get')
            ->willReturn(\Magento\TestFramework\Helper\Bootstrap::getObjectManager());
        $setupOptions = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Setup\Model\ConfigOptions');
        $backendOptions = new \Magento\Backend\Setup\ConfigOptions();
        $this->expected = [
            'Magento\Setup\Model\ConfigOptions' => ['options' => $setupOptions->getOptions(), 'enabled' => true],
            'Magento\Backend\Setup\ConfigOptions' => ['options' => $backendOptions->getOptions()],
        ];
    }

    public function testCollectOptions()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Setup\Model\ConfigOptionsCollector $object */
        $object = $objectManager->create(
            'Magento\Setup\Model\ConfigOptionsCollector',
            ['objectManagerProvider' => $this->objectManagerProvider]
        );
        $result = $object->collectOptions();
        $this->assertOptions($result, true);

    }

    public function testCollectOptionsDisabledModules()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $moduleListMock = $this->getMock('Magento\Framework\Module\ModuleList', [], [], '', false);
        $moduleListMock->expects($this->any())->method('has')->willReturn(false);
        $object = $objectManager->create(
            'Magento\Setup\Model\ConfigOptionsCollector',
            [
                'objectManagerProvider' => $this->objectManagerProvider,
                'moduleList' => $moduleListMock,
            ]
        );
        $result = $object->collectOptions();
        $this->assertOptions($result, false);
    }

    /**
     * Assert options array
     *
     * @param $actual
     * @param $enabled
     */
    private function assertOptions($actual, $enabled)
    {
        $expected = [];
        foreach ($this->expected as $key => $value) {
            $expected[$key] = $value;
            if (!isset($value['enabled'])) {
                $expected[$key]['enabled'] = $enabled;
            }
        }
        $this->assertEquals($expected, $actual);
    }
}
