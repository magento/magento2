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
interface MapperInterface
{
    /**
     * Apply map
     *
     * @param array $data
     * @return array
     * @since 2.0.0
     */
    public function map(array $data);
}
