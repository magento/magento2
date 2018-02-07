<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Interception\Chain;

use Magento\Framework\Interception\InterceptorInterface;
use Magento\Framework\Interception\DefinitionInterface;
use Magento\Framework\Interception\PluginListInterface;

class Chain implements \Magento\Framework\Interception\ChainInterface
{
    /**
     * @var \Magento\Framework\Interception\PluginListInterface
     */
    protected $pluginList;

    /**
     * @param PluginListInterface $pluginList
     */
    public function __construct(PluginListInterface $pluginList)
    {
        $this->pluginList = $pluginList;
    }

    /**
     * Invoke next plugin in chain
     *
     * @param string $type
     * @param string $method
     * @param string $previousPluginCode
     * @param InterceptorInterface $subject
     * @param array $arguments
     * @return mixed|void
     */
    public function invokeNext(
        $type,
        $method,
        InterceptorInterface $subject,
        array $arguments,
        $previousPluginCode = null
    ) {
        $pluginInfo = $this->pluginList->getNext($type, $method, $previousPluginCode);
        $capMethod = ucfirst($method);
        $result = null;
        if (isset($pluginInfo[DefinitionInterface::LISTENER_BEFORE])) {
            foreach ($pluginInfo[DefinitionInterface::LISTENER_BEFORE] as $code) {
                $pluginInstance = $this->pluginList->getPlugin($type, $code);
                $pluginMethod = 'before' . $capMethod;
                $beforeResult = $pluginInstance->$pluginMethod($subject, ...array_values($arguments));
                if ($beforeResult) {
                    $arguments = $beforeResult;
                }
                unset($pluginInstance, $pluginMethod);
            }
        }
        if (isset($pluginInfo[DefinitionInterface::LISTENER_AROUND])) {
            $chain = $this;
            $code = $pluginInfo[DefinitionInterface::LISTENER_AROUND];
            $next = function () use ($chain, $type, $method, $subject, $code) {
                return $chain->invokeNext($type, $method, $subject, func_get_args(), $code);
            };
            $pluginInstance = $this->pluginList->getPlugin($type, $code);
            $pluginMethod = 'around' . $capMethod;
            $result = $pluginInstance->$pluginMethod($subject, $next, ...array_values($arguments));
            unset($pluginInstance, $pluginMethod);
        } else {
            $result = $subject->___callParent($method, $arguments);
        }
        if (isset($pluginInfo[DefinitionInterface::LISTENER_AFTER])) {
            foreach ($pluginInfo[DefinitionInterface::LISTENER_AFTER] as $code) {
                $result = $this->pluginList->getPlugin($type, $code)->{'after' . $capMethod}($subject, $result);
            }
        }
        return $result;
    }
}
