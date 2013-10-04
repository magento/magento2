<?php
/**
 * Test object manager
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\TestFramework;

class ObjectManager extends \Magento\Core\Model\ObjectManager
{
    /**
     * Classes with xml properties to explicitly call __destruct() due to https://bugs.php.net/bug.php?id=62468
     *
     * @var array
     */
    protected $_classesToDestruct = array(
        'Magento\Core\Model\Layout',
        'Magento\Core\Model\Registry'
    );

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

        \Magento\Core\Model\Config\Base::destroy();
        $sharedInstances = array('Magento\ObjectManager' => $this, 'Magento\Core\Model\ObjectManager' => $this);
        if (isset($this->_sharedInstances['Magento\Core\Model\Resource'])) {
            $sharedInstances['Magento\Core\Model\Resource'] = $this->_sharedInstances['Magento\Core\Model\Resource'];
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
     * Load primary DI configuration
     *
     * @param array $configData
     */
    public function loadPrimaryConfig($configData)
    {
        if ($configData) {
            $this->configure($configData);
        }
    }

    /**
     * Set objectManager
     *
     * @param \Magento\ObjectManager $objectManager
     * @return \Magento\ObjectManager
     */
    public static function setInstance(\Magento\ObjectManager $objectManager)
    {
        return self::$_instance = $objectManager;
    }

    /**
     * @return \Magento\ObjectManager\Factory|\Magento\ObjectManager\Factory\Factory
     */
    public function getFactory()
    {
        return $this->_factory;
    }
}
