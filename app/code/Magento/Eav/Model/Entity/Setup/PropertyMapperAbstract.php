<?php
/**
 * Abstract attribute property mapper
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity\Setup;

abstract class PropertyMapperAbstract implements PropertyMapperInterface
{
    /**
     * Retrieve value from array by key or return default value
     *
     * @param array $array
     * @param string $key
     * @param string $default
     * @return string
     */
    protected function _getValue($array, $key, $default = null)
    {
        if (isset($array[$key]) && is_bool($array[$key])) {
            $array[$key] = (int)$array[$key];
        }
        return isset($array[$key]) ? $array[$key] : $default;
    }
}
