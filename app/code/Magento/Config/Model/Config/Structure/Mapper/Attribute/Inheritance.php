<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * System Configuration Attribute Inheritance Mapper
 */
namespace Magento\Config\Model\Config\Structure\Mapper\Attribute;

/**
 * @api
 */
class Inheritance implements \Magento\Config\Model\Config\Structure\MapperInterface
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
