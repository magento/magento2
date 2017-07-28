<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * System Configuration Converter Mapper Interface
 */
namespace Magento\Config\Model\Config\Structure;

/**
 * @api
 * @since 2.0.0
 */
abstract class AbstractMapper implements MapperInterface
{
    /**
     * Check value existence
     *
     * @param string $key
     * @param array $target
     * @return bool
     * @since 2.0.0
     */
    protected function _hasValue($key, $target)
    {
        if (false == is_array($target)) {
            return false;
        }

        $paths = explode('/', $key);
        foreach ($paths as $path) {
            if (array_key_exists($path, $target)) {
                $target = $target[$path];
            } else {
                return false;
            }
        }
        return true;
    }
}
