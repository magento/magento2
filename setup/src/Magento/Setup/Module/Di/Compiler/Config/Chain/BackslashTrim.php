<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Module\Di\Compiler\Config\Chain;

use Magento\Setup\Module\Di\Compiler\Config\ModificationInterface;

/**
 * Class BackslashTrim
 *
 * @package Magento\Setup\Module\Di\Compiler\Config\Chain
 */
class BackslashTrim implements ModificationInterface
{
    /**
     * Argument keys which require recursive resolving
     */
    private const RECURSIVE_ARGUMENT_KEYS = [
        '_i_' => true, // shared instance of a class or interface
        '_ins_' => true // non-shared instance of a class or interface
    ];

    /**
     * Modifies input config
     *
     * @param array $config
     * @return array
     */
    public function modify(array $config)
    {
        if (!isset($config['arguments'])) {
            return $config;
        }

        $config['arguments'] = $this->resolveInstancesNames($config['arguments']);
        $this->resolveArguments($config['arguments']);

        return $config;
    }

    /**
     * Resolves instances names
     *
     * @param array $arguments
     * @return array
     */
    private function resolveInstancesNames(array $arguments)
    {
        $resolvedInstances = [];
        foreach ($arguments as $instance => $constructor) {
            $resolvedInstances[ltrim($instance, '\\')] = $constructor;
        }

        return $resolvedInstances;
    }

    /**
     * Resolves instances arguments
     *
     * @param array $argument
     */
    private function resolveArguments(&$argument)
    {
        if (!is_array($argument)) {
            return;
        }

        foreach ($argument as $key => &$value) {
            if (isset(self::RECURSIVE_ARGUMENT_KEYS[$key])) {
                $value = ltrim($value, '\\');
                continue;
            }

            $this->resolveArguments($value);
        }
    }
}
