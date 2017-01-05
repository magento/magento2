<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Module\Di\Compiler\Config\Chain;

use Magento\Setup\Module\Di\Compiler\Config\ModificationInterface;

class BackslashTrim implements ModificationInterface
{
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
     * @return array
     */
    private function resolveArguments(&$argument)
    {
        if (!is_array($argument)) {
            return;
        }

        foreach ($argument as $key => &$value) {
            if (in_array($key, ['_i_', '_ins_'], true)) {
                $value = ltrim($value, '\\');
                continue;
            }

            if (is_array($value)) {
                $this->resolveArguments($value);
            }
        }
        return;
    }
}
