<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework;

/**
 * ObjectManager for integration test framework.
 */
class ObjectManager extends \Magento\Framework\App\ObjectManager
{
    /**
     * Classes with xml properties to explicitly call __destruct() due to https://bugs.php.net/bug.php?id=62468
     *
     * @var array
     */
    protected $_classesToDestruct = [
        \Magento\Framework\View\Layout::class,
        \Magento\Framework\Registry::class
    ];

    /**
     * @var array
     */
    protected $persistedInstances = [
        \Magento\Framework\App\ResourceConnection::class,
        \Magento\Framework\Config\Scope::class,
        \Magento\Framework\ObjectManager\RelationsInterface::class,
        \Magento\Framework\ObjectManager\ConfigInterface::class,
        \Magento\Framework\Interception\DefinitionInterface::class,
        \Magento\Framework\ObjectManager\DefinitionInterface::class,
        \Magento\Framework\Session\Config::class,
        \Magento\Framework\ObjectManager\Config\Mapper\Dom::class,
    ];

    /**
     * Clear InstanceManager cache.
     *
     * @return \Magento\TestFramework\ObjectManager
     */
    public function clearCache()
    {
        foreach ($this->_classesToDestruct as $className) {
            if (isset($this->_sharedInstances[$className])) {
                $this->_sharedInstances[$className] = null;
            }
        }

        \Magento\Framework\App\Config\Base::destroy();
        $sharedInstances = [
            \Magento\Framework\ObjectManagerInterface::class => $this,
            \Magento\Framework\App\ObjectManager::class => $this,
        ];
        foreach ($this->persistedInstances as $persistedClass) {
            if (isset($this->_sharedInstances[$persistedClass])) {
                $sharedInstances[$persistedClass] = $this->_sharedInstances[$persistedClass];
            }
        }
        $this->_sharedInstances = $sharedInstances;
        $this->_config->clean();
        $this->clearMappedTableNames();

        return $this;
    }

    /**
     * Clear mapped table names list.
     *
     * @return void
     */
    private function clearMappedTableNames()
    {
        $resourceConnection = $this->get(\Magento\Framework\App\ResourceConnection::class);
        if ($resourceConnection) {
            $reflection = new \ReflectionClass($resourceConnection);
            $dataProperty = $reflection->getProperty('mappedTableNames');
            $dataProperty->setAccessible(true);
            $dataProperty->setValue($resourceConnection, null);
        }
    }

    /**
     * Add shared instance.
     *
     * @param mixed $instance
     * @param string $className
     * @param bool $forPreference Resolve preference for class
     * @return void
     */
    public function addSharedInstance($instance, $className, $forPreference = false)
    {
        $className  = $forPreference ? $this->_config->getPreference($className) : $className;
        $this->_sharedInstances[$className] = $instance;
    }

    /**
     * Remove shared instance.
     *
     * @param string $className
     * @param bool $forPreference Resolve preference for class
     * @return void
     */
    public function removeSharedInstance($className, $forPreference = false)
    {
        $className  = $forPreference ? $this->_config->getPreference($className) : $className;
        unset($this->_sharedInstances[$className]);
    }

    /**
     * Set objectManager.
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @return \Magento\Framework\ObjectManagerInterface
     */
    public static function setInstance(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        return self::$_instance = $objectManager;
    }

    /**
     * Get object factory
     *
     * @return \Magento\Framework\ObjectManager\FactoryInterface|\Magento\Framework\ObjectManager\Factory\Factory
     */
    public function getFactory()
    {
        return $this->_factory;
    }
}
