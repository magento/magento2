<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\ObjectManager\Environment;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ObjectManager\Environment\Developer;

class DeveloperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Developer
     */
    protected $_developer;

    protected function setUp(): void
    {
        $envFactoryMock = $this->createMock(\Magento\Framework\App\EnvironmentFactory::class);
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

        $objectManagerMock = $this->getMockBuilder(\Magento\Framework\App\ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        ObjectManager::setInstance($objectManagerMock);
        $diConfigMock = $this->getMockBuilder(\Magento\Framework\Interception\ObjectManager\ConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configLoaderMock = $this->getMockBuilder(\Magento\Framework\App\ObjectManager\ConfigLoader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $configLoaderMock->expects($this->any())->method('load')->willReturn([]);
        $omReturnMap = [
            [\Magento\Framework\App\ObjectManager\ConfigLoader::class,  $configLoaderMock],
            [\Magento\Framework\Config\ScopeInterface::class,
                $this->getMockBuilder(\Magento\Framework\Config\ScopeInterface::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            ],
            [\Magento\Framework\App\ObjectManager\ConfigCache::class,
                $this->getMockBuilder(\Magento\Framework\App\ObjectManager\ConfigCache::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            ],
            [\Magento\Framework\Interception\Config\Config::class,
                $this->getMockBuilder(\Magento\Framework\Interception\Config\Config::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            ]
        ];
        $objectManagerMock->expects($this->any())->method('get')->willReturnMap($omReturnMap);

        $sharedInstances = ['class_name' => 'shared_object'];
        $this->_developer->configureObjectManager($diConfigMock, $sharedInstances);

        $expectedSharedInstances = [
            'class_name' => 'shared_object',
            \Magento\Framework\ObjectManager\ConfigLoaderInterface::class =>  $configLoaderMock
        ];
        $this->assertSame($expectedSharedInstances, $sharedInstances);
        if (isset($origObjectManager)) {
            ObjectManager::setInstance($origObjectManager);
        }
    }
}
