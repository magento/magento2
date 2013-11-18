<?php
/**
 * Object manager factory decorator. Wraps intercepted objects by Interceptor instance
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
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Interception;

class FactoryDecorator implements \Magento\ObjectManager\Factory
{
    /**
     * Configurable factory
     *
     * @var \Magento\ObjectManager\Factory
     */
    protected $_factory;

    /**
     * Object manager
     *
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    /**
     * Object manager config
     *
     * @var \Magento\Interception\Config
     */
    protected $_config;

    /**
     * List of plugins configured for instance
     *
     * @var \Magento\Interception\PluginList
     */
    protected $_pluginList;

    /**
     * @param \Magento\ObjectManager\Factory $factory
     * @param \Magento\Interception\Config $config
     * @param \Magento\Interception\PluginList $pluginList
     * @param \Magento\ObjectManager $objectManager
     */
    public function __construct(
        \Magento\ObjectManager\Factory $factory,
        \Magento\Interception\Config $config,
        \Magento\Interception\PluginList $pluginList,
        \Magento\ObjectManager $objectManager
    ) {
        $this->_factory = $factory;
        $this->_pluginList = $pluginList;
        $this->_objectManager = $objectManager;
        $this->_config = $config;
    }

    /**
     * Set object manager
     *
     * @param \Magento\ObjectManager $objectManager
     */
    public function setObjectManager(\Magento\ObjectManager $objectManager)
    {
        $this->_objectManager = $objectManager;
        $this->_factory->setObjectManager($objectManager);
    }

    /**
     * Set application arguments
     *
     * @param array $arguments
     */
    public function setArguments($arguments)
    {
        $this->_factory->setArguments($arguments);
    }

    /**
     * Create instance of requested type with requested arguments
     *
     * @param string $type
     * @param array $arguments
     * @return mixed
     */
    public function create($type, array $arguments = array())
    {
        if ($this->_config->hasPlugins($type)) {
            $interceptorClass = $this->_config->getInterceptorClassName($type);
            return new $interceptorClass(
                $this->_factory,
                $this->_objectManager,
                $type,
                $this->_pluginList,
                $arguments
            );
        }
        return $this->_factory->create($type, $arguments);
    }
}
