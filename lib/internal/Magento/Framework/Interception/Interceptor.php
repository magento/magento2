<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * List of plugins
     *
     * @var PluginListInterface
     */
    private $pluginList;

    /**
     * Subject type name
     *
     * @var string
     */
    private $subjectType;

    /**
     * Initialize the Interceptor
     *
     * @return void
     */
    public function ___init()
    {
        $this->pluginList = ObjectManager::getInstance()->get(PluginListInterface::class);
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
        $properties = array_diff($properties, ['pluginList', 'subjectType']);
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
        $subject = $this;
        $type = $this->subjectType;
        $pluginList = $this->pluginList;

        $next = function (...$arguments) use (
            $method,
            &$pluginInfo,
            $subject,
            $type,
            $pluginList,
            &$next
        ) {
            $capMethod = ucfirst($method);
            $currentPluginInfo = $pluginInfo;
            $result = null;

            if (isset($currentPluginInfo[DefinitionInterface::LISTENER_BEFORE])) {
                // Call 'before' listeners
                foreach ($currentPluginInfo[DefinitionInterface::LISTENER_BEFORE] as $code) {
                    $pluginInstance = $pluginList->getPlugin($type, $code);
                    $pluginMethod = 'before' . $capMethod;
                    $beforeResult = $pluginInstance->$pluginMethod($this, ...array_values($arguments));

                    if ($beforeResult !== null) {
                        $arguments = (array)$beforeResult;
                    }
                }
            }

            if (isset($currentPluginInfo[DefinitionInterface::LISTENER_AROUND])) {
                // Call 'around' listener
                $code = $currentPluginInfo[DefinitionInterface::LISTENER_AROUND];
                $pluginInfo = $pluginList->getNext($type, $method, $code);
                $pluginInstance = $pluginList->getPlugin($type, $code);
                $pluginMethod = 'around' . $capMethod;
                $result = $pluginInstance->$pluginMethod($subject, $next, ...array_values($arguments));
            } else {
                // Call original method
                $result = $subject->___callParent($method, $arguments);
            }

            if (isset($currentPluginInfo[DefinitionInterface::LISTENER_AFTER])) {
                // Call 'after' listeners
                foreach ($currentPluginInfo[DefinitionInterface::LISTENER_AFTER] as $code) {
                    $pluginInstance = $pluginList->getPlugin($type, $code);
                    $pluginMethod = 'after' . $capMethod;
                    $result = $pluginInstance->$pluginMethod($subject, $result, ...array_values($arguments));
                }
            }

            return $result;
        };

        $result = $next(...array_values($arguments));
        $next = null;

        return $result;
    }
}
