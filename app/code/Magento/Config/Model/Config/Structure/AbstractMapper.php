<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

/**
 * System Configuration Converter Mapper Interface
 */
namespace Magento\Config\Model\Config\Structure;

/**
 * @phpcs:ignore Magento2.Classes.AbstractApi.AbstractApi
 * @api
 * @since 100.0.2
 */
abstract class AbstractMapper implements MapperInterface
{
    /**
     * Check value existence
     *
     * @param string $key
     * @param array $target
     * @return bool
     */
    protected function _hasValue($key, $target)
    {
        if (false == is_array($target)) {
            return false;
        }

        $paths = explode('/', $key);
        foreach ($paths as $path) {
            if (is_array($target) && array_key_exists($path, $target)) {
                $target = $target[$path];
            } else {
                return false;
            }
        }
        return true;
    }
}
