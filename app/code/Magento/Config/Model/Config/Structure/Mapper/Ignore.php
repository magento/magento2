<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * System Configuration Ignore Mapper
 */
namespace Magento\Config\Model\Config\Structure\Mapper;

/**
 * @api
 * @since 2.0.0
 */
class Ignore implements \Magento\Config\Model\Config\Structure\MapperInterface
{
    /**
     * Apply map
     *
     * @param array $data
     * @return array
     * @since 2.0.0
     */
    public function map(array $data)
    {
        return $data;
    }
}
