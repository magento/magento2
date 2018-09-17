<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\ObjectManager\Environment;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ObjectManager\Environment\Developer;

class DeveloperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Developer
     */
    protected $_developer;

    protected function setUp()
    {
        $envFactoryMock = $this->getMock('Magento\Framework\App\EnvironmentFactory', [], [], '', false);
        $this->_developer = new Developer($envFactoryMock);
    }

    public function testGetMode()
    {
        $this->assertEquals(Developer::MODE, $this->_developer->getMode());
    }

    public function testGetObjectManagerConfigLoader()
    {
        $this->assertNull($this->_developer->getObjectManagerConfigLoader());
    }

    public function testConfigureObjectManager()
    {
        try {
            $origObjectManager = ObjectManager::getInstance();
        } catch (\Exception $e) {
            $origObjectManager = null;
        }


        $objectManagerMock = $this->getMockBuilder('Magento\Framework\App\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();
        ObjectManager::setInstance($objectManagerMock);
        $diConfigMock = $this->getMockBuilder('\Magento\Framework\Interception\ObjectManager\ConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $configLoaderMock = $this->getMockBuilder('Magento\Framework\App\ObjectManager\ConfigLoader')
            ->disableOriginalConstructor()
            ->getMock();
        $configLoaderMock->expects($this->any())->method('load')->willReturn([]);
        $omReturnMap = [
            ['Magento\Framework\App\ObjectManager\ConfigLoader',  $configLoaderMock],
            [
                'Magento\Framework\Config\ScopeInterface',
                $this->getMockBuilder('Magento\Framework\Config\ScopeInterface')
                    ->disableOriginalConstructor()
                    ->getMock()
            ],
            [
                'Magento\Framework\App\ObjectManager\ConfigCache',
                $this->getMockBuilder('Magento\Framework\App\ObjectManager\ConfigCache')
                    ->disableOriginalConstructor()
                    ->getMock()
            ],
            [
                'Magento\Framework\Interception\Config\Config',
                $this->getMockBuilder('Magento\Framework\Interception\Config\Config')
                    ->disableOriginalConstructor()
                    ->getMock()
            ]
        ];
        $objectManagerMock->expects($this->any())->method('get')->willReturnMap($omReturnMap);

        $sharedInstances = ['class_name' => 'shared_object'];
        $this->_developer->configureObjectManager($diConfigMock, $sharedInstances);

        $expectedSharedInstances = [
            'class_name' => 'shared_object',
            'Magento\Framework\ObjectManager\ConfigLoaderInterface' =>  $configLoaderMock
        ];
        $this->assertSame($expectedSharedInstances, $sharedInstances);
        if (isset($origObjectManager)) {
            ObjectManager::setInstance($origObjectManager);
        }
    }
}
