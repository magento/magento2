<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * System Configuration Ignore Mapper
 */
namespace Magento\Backend\Model\Config\Structure\Mapper;

class Ignore implements \Magento\Backend\Model\Config\Structure\MapperInterface
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
