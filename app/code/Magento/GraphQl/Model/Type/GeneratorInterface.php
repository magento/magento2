<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model\Type;

/**
 * Generate configured GraphQL type schema and top-level fields of base query object
 */
interface GeneratorInterface
{
    /**
     * Generate type definitions for all fields of given GraphQl query or mutation name
     *
     * @param string $typeName
     * @return array Represented as ['fields' => ['fieldName' => Type, {...}], 'types' => Types[]]
     */
    public function generateTypes(string $typeName);
}
