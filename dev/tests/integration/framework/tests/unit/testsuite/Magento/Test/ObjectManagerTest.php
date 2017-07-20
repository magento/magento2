<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ResourceConnection\ConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Model\ResourceModel\Type\Db\ConnectionFactoryInterface;
use Magento\Framework\ObjectManager\FactoryInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\ObjectManager\Config;

class ObjectManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array Instances that shouldn't be destroyed by clearing cache.
     */
    private static $persistedInstances = [
        ResourceConnection::class,
        \Magento\Framework\Config\Scope::class,
        \Magento\Framework\ObjectManager\RelationsInterface::class,
        \Magento\Framework\ObjectManager\ConfigInterface::class,
        \Magento\Framework\Interception\DefinitionInterface::class,
        \Magento\Framework\ObjectManager\DefinitionInterface::class,
        \Magento\Framework\Session\Config::class,
        \Magento\Framework\ObjectManager\Config\Mapper\Dom::class
    ];

    /**
     * @var string Instance that should be destroyed by clearing cache.
     */
    private static $notPersistedInstance = CacheInterface::class;

    /**
     * Tests that the scope of persisted instances doesn't clear after Object Manager cache clearing.
     *
     * @covers \Magento\TestFramework\ObjectManager::clearCache()
     */
    public function testInstancePersistingAfterClearCache()
    {
        foreach (self::$persistedInstances as $className) {
            $sharedInstances[$className] = $this->createInstanceMock($className);
        }

        $config = $this->getObjectManagerConfigMock();
        $factory = $this->getObjectManagerFactoryMock();

        $objectManager = new ObjectManager($factory, $config, $sharedInstances);
        $objectManager->clearCache();

        $this->assertSame(
            $objectManager,
            $objectManager->get(ObjectManagerInterface::class),
            "Object manager instance should be the same after cache clearing."
        );
        $this->assertSame(
            $objectManager,
            $objectManager->get(\Magento\Framework\App\ObjectManager::class),
            "Object manager instance should be the same after cache clearing."
        );
        foreach (self::$persistedInstances as $className) {
            $this->assertSame(
                $sharedInstances[$className],
                $objectManager->get($className),
                "Instance of {$className} should be the same after cache clearing."
            );
        }
    }

    /**
     * Tests that instance is destroyed after Object Manager cache clearing.
     *
     * @covers \Magento\TestFramework\ObjectManager::clearCache()
     */
    public function testInstanceDestroyingAfterClearCache()
    {
        $sharedInstances[self::$notPersistedInstance] = $this->createInstanceMock(self::$notPersistedInstance);
        $config = $this->getObjectManagerConfigMock();
        $factory = $this->getObjectManagerFactoryMock();

        $objectManager = new ObjectManager($factory, $config, $sharedInstances);
        $objectManager->clearCache();

        $this->assertNull(
            $objectManager->get(self::$notPersistedInstance),
            'Instance of ' . self::$notPersistedInstance . ' should be destroyed after cache clearing.'
        );
    }

    /**
     * Tests that instance is recreated after Object Manager cache clearing.
     *
     * @covers \Magento\TestFramework\ObjectManager::clearCache()
     */
    public function testInstanceRecreatingAfterClearCache()
    {
        $config = $this->getObjectManagerConfigMock();
        $factory = $this->getObjectManagerFactoryMock();

        $objectManager = new ObjectManager($factory, $config);
        $instance = $objectManager->get(DataObject::class);

        $this->assertSame($instance, $objectManager->get(DataObject::class));
        $objectManager->clearCache();
        $this->assertNotSame(
            $instance,
            $objectManager->get(DataObject::class),
            'Instance ' . DataObject::class . ' should be recreated after cache clearing.'
        );
    }

    /**
     * Tests that mapped table names list is empty after Object Manager cache clearing.
     *
     * @covers \Magento\TestFramework\ObjectManager::clearCache()
     */
    public function testIsEmptyMappedTableNamesAfterClearCache()
    {
        $config = $this->getObjectManagerConfigMock();
        $factory = $this->getObjectManagerFactoryMock();

        $objectManager = new ObjectManager($factory, $config);

        $resourceConnection = $this->getResourceConnection();
        $resourceConnection->setMappedTableName('tableName', 'mappedTableName');
        $objectManager->addSharedInstance(
            $resourceConnection,
            ResourceConnection::class
        );
        $objectManager->clearCache();

        $this->assertFalse(
            $objectManager->get(ResourceConnection::class)->getMappedTableName('tableName'),
            'Mapped table names list is not empty after Object Manager cache clearing.'
        );
    }

    /**
     * @return Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getObjectManagerConfigMock()
    {
        $configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $configMock->method('getPreference')
            ->willReturnCallback(
                function ($className) {
                    return $className;
                }
            );

        return $configMock;
    }

    /**
     * @return FactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getObjectManagerFactoryMock()
    {
        $factory = $this->getMockForAbstractClass(FactoryInterface::class);
        $factory->method('create')->willReturnCallback(
            function ($className) {
                if ($className === DataObject::class) {
                    return $this->getMockBuilder(DataObject::class)
                        ->disableOriginalConstructor()
                        ->getMock();
                }
            }
        );

        return $factory;
    }

    /**
     * Returns mock of instance.
     *
     * @param string $className
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createInstanceMock($className)
    {
        return $this->getMockBuilder($className)->disableOriginalConstructor()->getMock();
    }

    /**
     * Returns ResourceConnection.
     *
     * @return ResourceConnection
     */
    private function getResourceConnection()
    {
        $configInterface = $this->getMockForAbstractClass(
            ConfigInterface::class
        );
        $connectionFactory = $this->getMockForAbstractClass(
            ConnectionFactoryInterface::class
        );
        $deploymentConfig = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resourceConnection = new ResourceConnection(
            $configInterface,
            $connectionFactory,
            $deploymentConfig
        );

        return $resourceConnection;
    }
}
