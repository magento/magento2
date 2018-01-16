<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Config;

use Magento\Framework\GraphQl\Config\Data\StructureInterface;

/**
 * Access all GraphQL type information declared in the schema's configuration.
 *
 * Data includes types, interfaces they implement, their arguments, and fields.
 */
interface ConfigInterface
{
    /**
     * Access a type's, identified by its name.
     *
     * @param string $key
     * @return StructureInterface
     */
    public function get(string $key) : StructureInterface;

    /**
     * Return all type names from a GraphQL schema's configuration.
     *
     * @return string[]
     */
    public function getDeclaredTypeNames() : array;
}
