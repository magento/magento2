<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Module\Di\Compiler\Config\Chain;

use Magento\Setup\Module\Di\Compiler\Config\ModificationInterface;

class ArgumentsSerialization implements ModificationInterface
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

        foreach ($config['arguments'] as $key => $value) {
            if ($value !== null) {
                $config['arguments'][$key] = serialize($value);
            }
        }

        return $config;
    }
}
