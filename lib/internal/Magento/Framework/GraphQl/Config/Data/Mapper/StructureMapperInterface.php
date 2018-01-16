<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Config\Data\Mapper;

use Magento\Framework\GraphQl\Config\Data\StructureInterface;

/**
 * Responsible for translating data produced by configuration readers to config objects
 */
interface StructureMapperInterface
{
    /**
     * Map data from passed by config readers to a data object format
     *
     * @param array $data
     * @return StructureInterface
     */
    public function map(array $data) : StructureInterface;
}
