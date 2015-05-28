<?php

namespace Magento\Framework\Interception;

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


    public function ___init()
    {
        $this->pluginLocator = \Magento\Framework\App\ObjectManager::getInstance();
        $this->pluginList = $this->pluginLocator->get('Magento\Framework\Interception\PluginListInterface');
        $this->chain = $this->pluginLocator->get('Magento\Framework\Interception\ChainInterface');
        $this->subjectType = get_parent_class($this);
        if (method_exists($this->subjectType, '___init')) {
            parent::___init();
        }
    }

    public function ___callParent($method, array $arguments)
    {
        return call_user_func_array(array('parent', $method), $arguments);
    }

    public function __sleep()
    {
        if (method_exists(get_parent_class($this), '__sleep')) {
            return array_diff(parent::__sleep(), array('pluginLocator', 'pluginList', 'chain', 'subjectType'));
        } else {
            return array_keys(get_class_vars(get_parent_class($this)));
        }
    }

    public function __wakeup()
    {
        if (method_exists(get_parent_class(\$this), '__wakeup')) {
            parent::__wakeup();
        }
        $this->___init();
    }

    protected function ___callPlugins($method, array $arguments, array $pluginInfo)
    {
        $capMethod = ucfirst($method);
        $result = null;
        if (isset($pluginInfo[\Magento\Framework\Interception\DefinitionInterface::LISTENER_BEFORE])) {
            // Call 'before' listeners
            foreach ($pluginInfo[\Magento\Framework\Interception\DefinitionInterface::LISTENER_BEFORE] as $code) {
                $beforeResult = call_user_func_array(
                    array($this->pluginList->getPlugin($this->subjectType, $code), 'before'. $capMethod), array_merge(array($this), $arguments)
                );
                if ($beforeResult) {
                    $arguments = $beforeResult;
                }
            }
        }
        if (isset($pluginInfo[\Magento\Framework\Interception\DefinitionInterface::LISTENER_AROUND])) {
            // Call 'around' listener
            $chain = $this->chain;
            $type = $this->subjectType;
            $subject = $this;
            $code = $pluginInfo[\Magento\Framework\Interception\DefinitionInterface::LISTENER_AROUND];
            $next = function () use ($chain, $type, $method, $subject, $code) {
                return $chain->invokeNext($type, $method, $subject, func_get_args(), $code);
            };
            $result = call_user_func_array(
                array($this->pluginList->getPlugin($this->subjectType, $code), 'around' . $capMethod),
                array_merge(array($this, $next), $arguments)
            );
        } else {
            // Call original method
            $result = call_user_func_array(array('parent', $method), $arguments);
        }
        if (isset($pluginInfo[\Magento\Framework\Interception\DefinitionInterface::LISTENER_AFTER])) {
            // Call 'after' listeners
            foreach ($pluginInfo[\Magento\Framework\Interception\DefinitionInterface::LISTENER_AFTER] as $code) {
                $result = $this->pluginList->getPlugin($this->subjectType, $code)
                    ->{'after' . $capMethod}($this, $result);
            }
        }
        return $result;
    }
}
