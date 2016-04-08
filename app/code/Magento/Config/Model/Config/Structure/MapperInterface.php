<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * System Configuration Converter Mapper Interface
 */
namespace Magento\Config\Model\Config\Structure;

interface MapperInterface
{
    /**
     * Apply map
     *
     * @param array $data
     * @return array
     */
    public function map(array $data);
}
