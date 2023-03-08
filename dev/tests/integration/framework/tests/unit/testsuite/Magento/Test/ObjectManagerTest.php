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

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ObjectManagerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Instances that shouldn't be destroyed by clearing cache.
     *
     * @var array
     */
    private $persistedInstances;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->persistedInstances = [
            ResourceConnection::class,
            \Magento\Framework\Config\Scope::class,
            \Magento\Framework\ObjectManager\RelationsInterface::class,
            \Magento\Framework\ObjectManager\ConfigInterface::class,
            \Magento\Framework\Interception\DefinitionInterface::class,
            \Magento\Framework\ObjectManager\DefinitionInterface::class,
            \Magento\Framework\Session\Config::class,
            \Magento\Framework\ObjectManager\Config\Mapper\Dom::class,
        ];
    }

    /**
     * Tests that the scope of persisted instances doesn't clear after Object Manager cache clearing.
     *
     * @covers \Magento\TestFramework\ObjectManager::clearCache()
     */
    public function testInstancePersistingAfterClearCache()
    {
        $sharedInstances = [];
        foreach ($this->persistedInstances as $className) {
            $sharedInstances[$className] = $this->createMock($className);
        }
        $objectManager = $this->createObjectManager($sharedInstances);
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
        foreach ($this->persistedInstances as $className) {
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
        $notPersistedInstance = CacheInterface::class;
        $sharedInstances = [$notPersistedInstance => $this->createMock($notPersistedInstance)];
        $objectManager = $this->createObjectManager($sharedInstances);
        $objectManager->clearCache();

        $this->assertNotSame(
            $objectManager->get($notPersistedInstance),
            'Instance of ' . $notPersistedInstance . ' should be destroyed after cache clearing.'
        );
    }

    /**
     * Tests that instance is recreated after Object Manager cache clearing.
     *
     * @covers \Magento\TestFramework\ObjectManager::clearCache()
     */
    public function testInstanceRecreatingAfterClearCache()
    {
        $objectManager = $this->createObjectManager();
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
        $objectManager = $this->createObjectManager();
        $objectManager->setPersistedInstances($this->persistedInstances);

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
     * Returns ResourceConnection.
     *
     * @return ResourceConnection
     */
    private function getResourceConnection(): ResourceConnection
    {
        $configInterface = $this->createMock(ConfigInterface::class);
        $connectionFactory = $this->createMock(ConnectionFactoryInterface::class);
        $deploymentConfig = $this->createMock(DeploymentConfig::class);
        $resourceConnection = new ResourceConnection(
            $configInterface,
            $connectionFactory,
            $deploymentConfig
        );

        return $resourceConnection;
    }

    /**
     * Create instance of object manager.
     *
     * @param array $sharedInstances
     * @return ObjectManager
     */
    private function createObjectManager(array $sharedInstances = []): ObjectManager
    {
        $factory = $this->createMock(FactoryInterface::class);
        $factory->method('create')
            ->willReturnCallback(
                function ($className) {
                    return $this->createMock($className);
                }
            );
        $configMock = $this->createMock(Config::class);
        $configMock->method('getPreference')
            ->willReturnCallback(
                function ($className) {
                    return $className;
                }
            );
        $objectManager = new ObjectManager($factory, $configMock, $sharedInstances);
        $objectManager->setPersistedInstances($this->persistedInstances);

        return $objectManager;
    }
}
