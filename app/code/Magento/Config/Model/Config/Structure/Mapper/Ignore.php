<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * System Configuration Ignore Mapper
 */
namespace Magento\Config\Model\Config\Structure\Mapper;

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
