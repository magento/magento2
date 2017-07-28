<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Di\Compiler\Config\Chain;

use Magento\Setup\Module\Di\Compiler\Config\ModificationInterface;

/**
 * Class \Magento\Setup\Module\Di\Compiler\Config\Chain\InterceptorSubstitution
 *
 * @since 2.0.0
 */
class InterceptorSubstitution implements ModificationInterface
{
    /**
     * Modifies input config
     *
     * @param array $config
     * @return array
     * @since 2.0.0
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

        $config['arguments'] = array_diff_key($config['arguments'], array_flip($interceptors));

        foreach ($interceptors as $originalName => $interceptor) {
            if (isset($config['arguments'][$originalName])) {
                $config['arguments'][$interceptor] = $config['arguments'][$originalName];
                unset($config['arguments'][$originalName]);
            }
        }

        $config['preferences'] = $this->resolvePreferences($config['preferences'], $interceptors);
        $config['preferences'] = array_merge($interceptors, $config['preferences']);
        $config['instanceTypes'] = $this->resolvePreferences($config['instanceTypes'], $interceptors);

        return $config;
    }

    /**
     * Returns list of intercepted types and their interceptors
     *
     * @param array $arguments
     * @return array
     * @since 2.0.0
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
     * Resolves config preferences
     *
     * @param array $preferences
     * @param array $interceptors
     * @return array
     * @since 2.0.0
     */
    private function resolvePreferences(array $preferences, array $interceptors)
    {
        foreach ($preferences as &$preference) {
            if (isset($interceptors[$preference])) {
                $preference = $interceptors[$preference];
            }
        }
        return $preferences;
    }
}
