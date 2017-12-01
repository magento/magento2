<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Type\Definition;

/**
 * Factory class for the creation scalar types
 */
class ScalarTypeFactory
{
    /**
     * Create a scalar type
     *
     * @param string $typeName
     * @return \GraphQL\Type\Definition\Type
     * @throws \LogicException
     */
    public function create(string $typeName)
    {
        $types = \GraphQL\Type\Definition\Type::getInternalTypes();
        if (isset($types[$typeName])) {
            return $types[$typeName];
        } else {
            throw new \LogicException(sprintf('Scalar type %s not found', $typeName));
        }
    }

    /**
     * Checks if a scalar type exists
     *
     * @param string $typeName
     * @return bool
     */
    public function typeExists(string $typeName)
    {
        $types = \GraphQL\Type\Definition\Type::getInternalTypes();
        if (isset($types[$typeName])) {
            return true;
        } else {
            return false;
        }
    }
}
