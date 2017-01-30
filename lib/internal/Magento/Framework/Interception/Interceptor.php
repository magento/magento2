<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Interception;

use Magento\Framework\App\ObjectManager;

/**
 * Interceptor trait that contains the common logic for all interceptor classes.
 *
 * A trait is used because our interceptor classes need to extend the class that they are intercepting.
 *
 * Any class using this trait is required to implement \Magento\Framework\Interception\InterceptorInterface
 *
 * @see \Magento\Framework\Interception\InterceptorInterface
 */
trait Interceptor
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $pluginLocator = null;

    /**
     * List of plugins
     *
     * @var \Magento\Framework\Interception\PluginListInterface
     */
    protected $pluginList = null;

    /**
     * Invocation chain
     *
     * @var \Magento\Framework\Interception\ChainInterface
     */
    protected $chain = null;

    /**
     * Subject type name
     *
     * @var string
     */
    protected $subjectType = null;

    /**
     * Initialize the Interceptor
     *
     * @return void
     */
    public function ___init()
    {
        $this->pluginLocator = ObjectManager::getInstance();
        $this->pluginList = $this->pluginLocator->get('Magento\Framework\Interception\PluginListInterface');
        $this->chain = $this->pluginLocator->get('Magento\Framework\Interception\ChainInterface');
        $this->subjectType = get_parent_class($this);
        if (method_exists($this->subjectType, '___init')) {
            parent::___init();
        }
    }

    /**
     * Calls parent class method
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function ___callParent($method, array $arguments)
    {
        return parent::$method(...array_values($arguments));
    }

    /**
     * Calls parent class sleep if defined, otherwise provides own implementation
     *
     * @return array
     */
    public function __sleep()
    {
        if (method_exists(get_parent_class($this), '__sleep')) {
            $properties = parent::__sleep();
        } else {
            $properties = array_keys(get_object_vars($this));
        }
        $properties = array_diff($properties, ['pluginLocator', 'pluginList', 'chain', 'subjectType', 'pluginLocator']);
        return $properties;
    }

    /**
     * Causes Interceptor to be initialized
     *
     * @return void
     */
    public function __wakeup()
    {
        if (method_exists(get_parent_class($this), '__wakeup')) {
            parent::__wakeup();
        }
        $this->___init();
    }

    /**
     * Calls plugins for a given method.
     *
     * @param string $method
     * @param array $arguments
     * @param array $pluginInfo
     * @return mixed|null
     */
    protected function ___callPlugins($method, array $arguments, array $pluginInfo)
    {
        $capMethod = ucfirst($method);
        $result = null;
        if (isset($pluginInfo[DefinitionInterface::LISTENER_BEFORE])) {
            // Call 'before' listeners
            foreach ($pluginInfo[DefinitionInterface::LISTENER_BEFORE] as $code) {
                $pluginInstance = $this->pluginList->getPlugin($this->subjectType, $code);
                $pluginMethod = 'before' . $capMethod;
                $beforeResult = $pluginInstance->$pluginMethod($this, ...array_values($arguments));
                if ($beforeResult) {
                    $arguments = $beforeResult;
                }
                unset($pluginInstance, $pluginMethod);
            }
        }
        if (isset($pluginInfo[DefinitionInterface::LISTENER_AROUND])) {
            // Call 'around' listener
            $chain = $this->chain;
            $type = $this->subjectType;
            /** @var \Magento\Framework\Interception\InterceptorInterface $subject */
            $subject = $this;
            $code = $pluginInfo[DefinitionInterface::LISTENER_AROUND];
            $next = function () use ($chain, $type, $method, $subject, $code) {
                return $chain->invokeNext($type, $method, $subject, func_get_args(), $code);
            };
            $pluginInstance = $this->pluginList->getPlugin($this->subjectType, $code);
            $pluginMethod = 'around' . $capMethod;
            $result = $pluginInstance->$pluginMethod($this, $next, ...array_values($arguments));
            unset($pluginInstance, $pluginMethod);
        } else {
            // Call original method
            $result = parent::$method(...array_values($arguments));
        }
        if (isset($pluginInfo[DefinitionInterface::LISTENER_AFTER])) {
            // Call 'after' listeners
            foreach ($pluginInfo[DefinitionInterface::LISTENER_AFTER] as $code) {
                $result = $this->pluginList->getPlugin($this->subjectType, $code)
                    ->{'after' . $capMethod}($this, $result);
            }
        }
        return $result;
    }
}
