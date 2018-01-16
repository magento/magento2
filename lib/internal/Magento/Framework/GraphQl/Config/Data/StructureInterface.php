<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Config\Data;

/**
 * Defines a contract for a structured data objects, that combined represent a configured GraphQL schema.
 */
interface StructureInterface
{
    /**
     * @return string
     */
    public function getName() : string;
}
