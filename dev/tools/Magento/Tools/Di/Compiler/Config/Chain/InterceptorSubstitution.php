<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Di\Compiler\Config\Chain;

use Magento\Tools\Di\Compiler\Config\ModificationInterface;

class InterceptorSubstitution implements ModificationInterface
{
    /**
     * Modifies input config
     *
     * @param array $config
     * @return array
     */
    public function modify(array $config)
    {
        $configKeys = [
            'arguments',
            'preferences',
            'instanceTypes'
        ];
        if ($configKeys != array_keys($config)) {
            return $config;
        }

        $interceptors = $this->getInterceptorsList($config['arguments']);
        $config['arguments'] = $this->resolveInstancesNames($config['arguments'], $interceptors);

        $this->resolveArguments($config['arguments'], $interceptors);
        $config['preferences'] = $this->resolvePreferences($config['preferences'], $interceptors);
        $config['instanceTypes'] = $this->resolvePreferences($config['instanceTypes'], $interceptors);
        $config['interceptors'] = $interceptors;
        return $config;
    }

    /**
     * Returns list of intercepted types and their interceptors
     *
     * @param array $arguments
     * @return array
     */
    private function getInterceptorsList(array $arguments)
    {
        $interceptors = [];

        foreach (array_keys($arguments) as $instanceName) {
            if (substr($instanceName, -12) === '\Interceptor') {
                $originalName = substr($instanceName, 0, strlen($instanceName) - 12);
                $interceptors[$originalName] = $instanceName;
            }
        }

        return $interceptors;
    }

    /**
     * Resolves instances names
     *
     * @param array $arguments
     * @return array
     */
    private function resolveInstancesNames(array $arguments, array $interceptors)
    {
        $resolvedInstances = [];
        foreach ($arguments as $instance => &$constructor) {
            if (isset($interceptors[$instance])) {
                unset($arguments[$interceptors[$instance]]);
                $instance = $interceptors[$instance];
            }
            $resolvedInstances[$instance] = $constructor;
        }

        return $resolvedInstances;
    }

    /**
     * Resolves instances arguments
     *
     * @param array $argument
     * @return array
     */
    private function resolveArguments(array &$argument, array $interceptors)
    {
        if (!is_array($argument)) {
            return;
        }

        foreach ($argument as $key => &$value) {
            if (in_array($key, ['_i_', '_ins_'])) {
                if (isset($interceptors[$value])) {
                    $value = $interceptors[$value];
                    continue;
                }
            }
            if (is_array($value)) {
                $this->resolveArguments($value, $interceptors);
            }
        }
        return;
    }

    /**
     * Resolves config preferences
     *
     * @param array $preferences
     * @param array $interceptors
     * @return array
     */
    private function resolvePreferences(array $preferences, array $interceptors)
    {
        foreach ($preferences as $type => &$preference) {
            if (isset($interceptors[$preference])) {
                $preference = $interceptors[$preference];
            }
        }
        return $preferences;
    }
}
