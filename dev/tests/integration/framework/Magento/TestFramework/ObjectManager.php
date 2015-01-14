<?php
/**
 * Test object manager
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework;

class ObjectManager extends \Magento\Framework\App\ObjectManager
{
    /**
     * Classes with xml properties to explicitly call __destruct() due to https://bugs.php.net/bug.php?id=62468
     *
     * @var array
     */
    protected $_classesToDestruct = ['Magento\Framework\View\Layout', 'Magento\Framework\Registry'];

    /**
     * @var array
     */
    protected $persistedInstances = [
        'Magento\Framework\App\Resource',
        'Magento\Framework\Config\Scope',
        'Magento\Framework\ObjectManager\RelationsInterface',
        'Magento\Framework\ObjectManager\ConfigInterface',
        'Magento\Framework\Interception\DefinitionInterface',
        'Magento\Framework\ObjectManager\DefinitionInterface',
        'Magento\Framework\Session\Config',
        'Magento\Framework\ObjectManager\Config\Mapper\Dom',
    ];

    /**
     * Clear InstanceManager cache
     *
     * @return \Magento\TestFramework\ObjectManager
     */
    public function clearCache()
    {
        foreach ($this->_classesToDestruct as $className) {
            if (isset($this->_sharedInstances[$className])) {
                $this->_sharedInstances[$className]->__destruct();
            }
        }

        \Magento\Framework\App\Config\Base::destroy();
        $sharedInstances = [
            'Magento\Framework\ObjectManagerInterface' => $this,
            'Magento\Framework\App\ObjectManager' => $this,
        ];
        foreach ($this->persistedInstances as $persistedClass) {
            if (isset($this->_sharedInstances[$persistedClass])) {
                $sharedInstances[$persistedClass] = $this->_sharedInstances[$persistedClass];
            }
        }
        $this->_sharedInstances = $sharedInstances;
        $this->_config->clean();

        return $this;
    }

    /**
     * Add shared instance
     *
     * @param mixed $instance
     * @param string $className
     */
    public function addSharedInstance($instance, $className)
    {
        $this->_sharedInstances[$className] = $instance;
    }

    /**
     * Remove shared instance
     *
     * @param string $className
     */
    public function removeSharedInstance($className)
    {
        unset($this->_sharedInstances[$className]);
    }

    /**
     * Set objectManager
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @return \Magento\Framework\ObjectManagerInterface
     */
    public static function setInstance(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        return self::$_instance = $objectManager;
    }

    /**
     * @return \Magento\Framework\ObjectManager\FactoryInterface|\Magento\Framework\ObjectManager\Factory\Factory
     */
    public function getFactory()
    {
        return $this->_factory;
    }
}
