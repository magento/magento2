<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\ObjectManager\Environment;

use Magento\Framework\App\EnvironmentFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ObjectManager\ConfigCache;
use Magento\Framework\App\ObjectManager\ConfigLoader;
use Magento\Framework\App\ObjectManager\Environment\Developer;
use Magento\Framework\Config\ScopeInterface;
use Magento\Framework\Interception\Config\Config;
use Magento\Framework\Interception\ObjectManager\ConfigInterface;
use Magento\Framework\ObjectManager\ConfigLoaderInterface;
use PHPUnit\Framework\TestCase;

class DeveloperTest extends TestCase
{
    /**
     * @var Developer
     */
    protected $_developer;

    protected function setUp(): void
    {
        $envFactoryMock = $this->createMock(EnvironmentFactory::class);
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

        $objectManagerMock = $this->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        ObjectManager::setInstance($objectManagerMock);
        $diConfigMock = $this->getMockBuilder(ConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $configLoaderMock = $this->getMockBuilder(ConfigLoader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $configLoaderMock->expects($this->any())->method('load')->willReturn([]);
        $omReturnMap = [
            [ConfigLoader::class,  $configLoaderMock],
            [ScopeInterface::class,
                $this->getMockBuilder(ScopeInterface::class)
                    ->disableOriginalConstructor()
                    ->getMockForAbstractClass()
            ],
            [ConfigCache::class,
                $this->getMockBuilder(ConfigCache::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            ],
            [Config::class,
                $this->getMockBuilder(Config::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            ]
        ];
        $objectManagerMock->expects($this->any())->method('get')->willReturnMap($omReturnMap);

        $sharedInstances = ['class_name' => 'shared_object'];
        $this->_developer->configureObjectManager($diConfigMock, $sharedInstances);

        $expectedSharedInstances = [
            'class_name' => 'shared_object',
            ConfigLoaderInterface::class =>  $configLoaderMock
        ];
        $this->assertSame($expectedSharedInstances, $sharedInstances);
        if (isset($origObjectManager)) {
            ObjectManager::setInstance($origObjectManager);
        }
    }
}
