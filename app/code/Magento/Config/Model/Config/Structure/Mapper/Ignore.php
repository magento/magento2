<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

/**
 * System Configuration Ignore Mapper
 */
namespace Magento\Config\Model\Config\Structure\Mapper;

/**
 * @api
 * @since 100.0.2
 */
class Ignore implements \Magento\Config\Model\Config\Structure\MapperInterface
{
    /**
     * Apply map
     *
     * @param array $data
     * @return array
     */
    public function map(array $data)
    {
        return $data;
    }
}
