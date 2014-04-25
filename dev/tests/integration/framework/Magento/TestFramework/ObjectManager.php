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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\TestFramework;

class ObjectManager extends \Magento\Framework\App\ObjectManager
{
    /**
     * Classes with xml properties to explicitly call __destruct() due to https://bugs.php.net/bug.php?id=62468
     *
     * @var array
     */
    protected $_classesToDestruct = array('Magento\Framework\View\Layout', 'Magento\Framework\Registry');

    /**
     * @var array
     */
    protected $persistedInstances = array(
        'Magento\Framework\App\Resource',
        'Magento\Framework\Config\Scope',
        'Magento\Framework\ObjectManager\Relations',
        'Magento\Framework\ObjectManager\Config',
        'Magento\Framework\Interception\Definition',
        'Magento\Framework\ObjectManager\Definition',
        'Magento\Framework\Session\Config',
        'Magento\Framework\ObjectManager\Config\Mapper\Dom'
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

        \Magento\Framework\App\Config\Base::destroy();
        $sharedInstances = array(
            'Magento\Framework\ObjectManager' => $this,
            'Magento\Framework\App\ObjectManager' => $this
        );
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
     * @param \Magento\Framework\ObjectManager $objectManager
     * @return \Magento\Framework\ObjectManager
     */
    public static function setInstance(\Magento\Framework\ObjectManager $objectManager)
    {
        return self::$_instance = $objectManager;
    }

    /**
     * @return \Magento\Framework\ObjectManager\Factory|\Magento\Framework\ObjectManager\Factory\Factory
     */
    public function getFactory()
    {
        return $this->_factory;
    }
}
