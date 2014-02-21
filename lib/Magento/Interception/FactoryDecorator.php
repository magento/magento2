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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Interception;

use Magento\ObjectManager;
use Magento\Interception\Config;
use Magento\Interception\PluginList;
use Magento\ObjectManager\Factory;

class FactoryDecorator implements Factory
{
    /**
     * Configurable factory
     *
     * @var Factory
     */
    protected $_factory;

    /**
     * Object manager
     *
     * @var ObjectManager
     */
    protected $_objectManager;

    /**
     * Object manager config
     *
     * @var Config
     */
    protected $_config;

    /**
     * List of plugins configured for instance
     *
     * @var PluginList
     */
    protected $_pluginList;

    /**
     * @param Factory $factory
     * @param Config $config
     * @param PluginList $pluginList
     * @param ObjectManager $objectManager
     */
    public function __construct(
        Factory $factory,
        Config $config,
        PluginList $pluginList,
        ObjectManager $objectManager
    ) {
        $this->_factory = $factory;
        $this->_pluginList = $pluginList;
        $this->_objectManager = $objectManager;
        $this->_config = $config;
    }

    /**
     * Create instance of requested type with requested arguments
     *
     * @param string $type
     * @param array $arguments
     * @return object
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
