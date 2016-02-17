<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\ServiceManager;

class Config implements ConfigInterface
{
    /**
     * @var array
     */
    protected $config = array();

    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct($config = array())
    {
        $this->config = $config;
    }

    /**
     * Get allow override
     *
     * @return null|bool
     */
    public function getAllowOverride()
    {
        return (isset($this->config['allow_override'])) ? $this->config['allow_override'] : null;
    }

    /**
     * Get factories
     *
     * @return array
     */
    public function getFactories()
    {
        return (isset($this->config['factories'])) ? $this->config['factories'] : array();
    }

    /**
     * Get abstract factories
     *
     * @return array
     */
    public function getAbstractFactories()
    {
        return (isset($this->config['abstract_factories'])) ? $this->config['abstract_factories'] : array();
    }

    /**
     * Get invokables
     *
     * @return array
     */
    public function getInvokables()
    {
        return (isset($this->config['invokables'])) ? $this->config['invokables'] : array();
    }

    /**
     * Get services
     *
     * @return array
     */
    public function getServices()
    {
        return (isset($this->config['services'])) ? $this->config['services'] : array();
    }

    /**
     * Get aliases
     *
     * @return array
     */
    public function getAliases()
    {
        return (isset($this->config['aliases'])) ? $this->config['aliases'] : array();
    }

    /**
     * Get initializers
     *
     * @return array
     */
    public function getInitializers()
    {
        return (isset($this->config['initializers'])) ? $this->config['initializers'] : array();
    }

    /**
     * Get shared
     *
     * @return array
     */
    public function getShared()
    {
        return (isset($this->config['shared'])) ? $this->config['shared'] : array();
    }

    /**
     * Get the delegator services map, with keys being the services acting as delegates,
     * and values being the delegator factories names
     *
     * @return array
     */
    public function getDelegators()
    {
        return (isset($this->config['delegators'])) ? $this->config['delegators'] : array();
    }

    /**
     * Configure service manager
     *
     * @param ServiceManager $serviceManager
     * @return void
     */
    public function configureServiceManager(ServiceManager $serviceManager)
    {
        if (($allowOverride = $this->getAllowOverride()) !== null) {
            $serviceManager->setAllowOverride($allowOverride);
        }

        foreach ($this->getFactories() as $name => $factory) {
            $serviceManager->setFactory($name, $factory);
        }

        foreach ($this->getAbstractFactories() as $factory) {
            $serviceManager->addAbstractFactory($factory);
        }

        foreach ($this->getInvokables() as $name => $invokable) {
            $serviceManager->setInvokableClass($name, $invokable);
        }

        foreach ($this->getServices() as $name => $service) {
            $serviceManager->setService($name, $service);
        }

        foreach ($this->getAliases() as $alias => $nameOrAlias) {
            $serviceManager->setAlias($alias, $nameOrAlias);
        }

        foreach ($this->getInitializers() as $initializer) {
            $serviceManager->addInitializer($initializer);
        }

        foreach ($this->getShared() as $name => $isShared) {
            $serviceManager->setShared($name, $isShared);
        }

        foreach ($this->getDelegators() as $originalServiceName => $delegators) {
            foreach ($delegators as $delegator) {
                $serviceManager->addDelegator($originalServiceName, $delegator);
            }
        }
    }
}
