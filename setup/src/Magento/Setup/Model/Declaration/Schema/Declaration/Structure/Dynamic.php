<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Declaration\Structure;

/**
 * Stub for @see: DynamicStructureInterface
 */
class Dynamic implements \Magento\Setup\Model\Declaration\Schema\Declaration\DynamicStructureInterface
{
    /**
     * Retrieve empty array as we do not want to pass any dynamic data for this moment
     *
     * @return array
     */
    public function getStructure()
    {
        return [];
    }
}
