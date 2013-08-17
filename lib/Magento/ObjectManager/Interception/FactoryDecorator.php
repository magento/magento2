<?php
/**
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
class Magento_ObjectManager_Interception_FactoryDecorator implements Magento_ObjectManager_Factory
{
    /**
     * List of virtual types
     *
     * @var array
     */
    protected $_virtualTypes = array();

    /**
     * List of configured interceptors
     *
     * @var array
     */
    protected $_plugins = array();

    /**
     * Configurable factory
     *
     * @var Magento_ObjectManager_Factory
     */
    protected $_factory;

    /**
     * List of plugin definitions
     *
     * @var Magento_ObjectManager_Interception_Definition
     */
    protected $_definitions;

    /**
     * Object manager
     *
     * @var Magento_ObjectManager
     */
    protected $_objectManager;

    /**
     * Object manager config
     *
     * @var Magento_ObjectManager_Config
     */
    protected $_config;

    /**
     * Interceptor class builder
     *
     * @var Magento_ObjectManager_Interception_ClassBuilder
     */
    protected $_classBuilder;

    /**
     * @param Magento_ObjectManager_Factory $factory
     * @param Magento_ObjectManager_Config $config
     * @param Magento_ObjectManager_ObjectManager $objectManager
     * @param Magento_ObjectManager_Interception_Definition $definitions
     * @param Magento_ObjectManager_Interception_ClassBuilder $classBuilder
     */
    public function __construct(
        Magento_ObjectManager_Factory $factory,
        Magento_ObjectManager_Config $config,
        Magento_ObjectManager_ObjectManager $objectManager = null,
        Magento_ObjectManager_Interception_Definition $definitions = null,
        Magento_ObjectManager_Interception_ClassBuilder $classBuilder = null
    ) {
        $this->_factory = $factory;
        $this->_config = $config;
        $this->_objectManager = $objectManager;
        $this->_definitions = $definitions ?: new Magento_ObjectManager_Interception_Definition_Runtime();
        $this->_classBuilder = $classBuilder ?: new Magento_ObjectManager_Interception_ClassBuilder_General();
    }

    /**
     * Set object manager
     *
     * @param Magento_ObjectManager $objectManager
     */
    public function setObjectManager(Magento_ObjectManager $objectManager)
    {
        $this->_objectManager = $objectManager;
        $this->_factory->setObjectManager($objectManager);
    }

    /**
     * Set object manager config
     *
     * @param Magento_ObjectManager_Config $config
     */
    public function setConfig(Magento_ObjectManager_Config $config)
    {
        $this->_config = $config;
        $this->_factory->setConfig($config);
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
            $interceptorClass = $this->_classBuilder
                ->composeInterceptorClassName($this->_config->getInstanceType($type));
            $config = array();
            foreach ($this->_config->getPlugins($type) as $plugin) {
                if (isset($plugin['disabled']) && (!$plugin['disabled'] || $plugin['disabled'] === 'false')) {
                    continue;
                }
                $pluginMethods = $this->_definitions->getMethodList(
                    $this->_config->getInstanceType($plugin['instance'])
                );
                foreach ($pluginMethods as $method) {
                    if (isset($config[$method])) {
                        $config[$method][] = $plugin['instance'];
                    } else {
                        $config[$method] = array($plugin['instance']);
                    }
                }
            }
            return new $interceptorClass(
                $this->_factory,
                $this->_objectManager,
                $type,
                $config,
                $arguments
            );
        }
        return $this->_factory->create($type, $arguments);
    }

    /**
     * Retrieve definitions
     *
     * @return Magento_ObjectManager_Definition
     */
    public function getDefinitions()
    {
        return $this->_factory->getDefinitions();
    }
}
