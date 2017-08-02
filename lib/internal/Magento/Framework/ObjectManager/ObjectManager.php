<?php
/**
 * Magento object manager. Responsible for instantiating objects taking into account:
 * - constructor arguments (using configured, and provided parameters)
 * - class instances life style (singleton, transient)
 * - interface preferences
 *
 * Intentionally contains multiple concerns for best performance
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager;

/**
 * Class \Magento\Framework\ObjectManager\ObjectManager
 *
 * @since 2.0.0
 */
class ObjectManager implements \Magento\Framework\ObjectManagerInterface
{
    /**
     * @var \Magento\Framework\ObjectManager\FactoryInterface
     * @since 2.0.0
     */
    protected $_factory;

    /**
     * List of shared instances
     *
     * @var array
     * @since 2.0.0
     */
    protected $_sharedInstances = [];

    /**
     * @var ConfigInterface
     * @since 2.0.0
     */
    protected $_config;

    /**
     * @param FactoryInterface $factory
     * @param ConfigInterface $config
     * @param array &$sharedInstances
     * @since 2.0.0
     */
    public function __construct(FactoryInterface $factory, ConfigInterface $config, &$sharedInstances = [])
    {
        $this->_config = $config;
        $this->_factory = $factory;
        $this->_sharedInstances = &$sharedInstances;
        $this->_sharedInstances[\Magento\Framework\ObjectManagerInterface::class] = $this;
    }

    /**
     * Create new object instance
     *
     * @param string $type
     * @param array $arguments
     * @return mixed
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function get($type)
    {
        $type = ltrim($type, '\\');
        $type = $this->_config->getPreference($type);
        if (!isset($this->_sharedInstances[$type])) {
            $this->_sharedInstances[$type] = $this->_factory->create($type);
        }
        return $this->_sharedInstances[$type];
    }

    /**
     * Configure di instance
     * Note: All arguments should be pre-processed (sort order, translations, etc) before passing to method configure.
     *
     * @param array $configuration
     * @return void
     * @since 2.0.0
     */
    public function configure(array $configuration)
    {
        $this->_config->extend($configuration);
    }
}
