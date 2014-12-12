<?php
/**
 * Magento object manager. Responsible for instantiating objects taking itno account:
 * - constructor arguments (using configured, and provided parameters)
 * - class instances life style (singleton, transient)
 * - interface preferences
 *
 * Intentionally contains multiple concerns for best performance
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\ObjectManager;

class ObjectManager implements \Magento\Framework\ObjectManagerInterface
{
    /**
     * @var \Magento\Framework\ObjectManager\FactoryInterface
     */
    protected $_factory;

    /**
     * List of shared instances
     *
     * @var array
     */
    protected $_sharedInstances = [];

    /**
     * @var Config\Config
     */
    protected $_config;

    /**
     * @param FactoryInterface $factory
     * @param ConfigInterface $config
     * @param array $sharedInstances
     */
    public function __construct(FactoryInterface $factory, ConfigInterface $config, array $sharedInstances = [])
    {
        $this->_config = $config;
        $this->_factory = $factory;
        $this->_sharedInstances = $sharedInstances;
        $this->_sharedInstances['Magento\Framework\ObjectManagerInterface'] = $this;
    }

    /**
     * Create new object instance
     *
     * @param string $type
     * @param array $arguments
     * @return mixed
     */
    public function create($type, array $arguments = [])
    {
        return $this->_factory->create($this->_config->getPreference($type), $arguments);
    }

    /**
     * Retrieve cached object instance
     *
     * @param string $type
     * @return mixed
     */
    public function get($type)
    {
        $type = $this->_config->getPreference($type);
        if (!isset($this->_sharedInstances[$type])) {
            $this->_sharedInstances[$type] = $this->_factory->create($type);
        }
        return $this->_sharedInstances[$type];
    }

    /**
     * Configure di instance
     *
     * @param array $configuration
     * @return void
     */
    public function configure(array $configuration)
    {
        $this->_config->extend($configuration);
    }
}
