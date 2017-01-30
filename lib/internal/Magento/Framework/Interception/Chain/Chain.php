<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
                $beforeResult = call_user_func_array(
                    [$this->pluginList->getPlugin($type, $code), 'before' . $capMethod],
                    array_merge([$subject], $arguments)
                );
                if ($beforeResult) {
                    $arguments = $beforeResult;
                }
            }
        }
        if (isset($pluginInfo[DefinitionInterface::LISTENER_AROUND])) {
            $chain = $this;
            $code = $pluginInfo[DefinitionInterface::LISTENER_AROUND];
            $next = function () use ($chain, $type, $method, $subject, $code) {
                return $chain->invokeNext($type, $method, $subject, func_get_args(), $code);
            };
            $result = call_user_func_array(
                [$this->pluginList->getPlugin($type, $code), 'around' . $capMethod],
                array_merge([$subject, $next], $arguments)
            );
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
