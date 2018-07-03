<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Schema\Type;

/**
 * Wrapper for GraphQl ScalarType
 */
class ScalarTypes
{
    /**
     * @param string $typeName
     * @return bool
     */
    public function isScalarType(string $typeName) : bool
    {
        $internalTypes = \GraphQL\Type\Definition\Type::getInternalTypes();
        return isset($internalTypes[$typeName]) ? true : false;
    }

    /**
     * @param string $typeName
     * @return \GraphQL\Type\Definition\ScalarType|\GraphQL\Type\Definition\Type
     * @throws \LogicException
     */
    public function getScalarTypeInstance(string $typeName) : \GraphQL\Type\Definition\Type
    {
        $internalTypes = \GraphQL\Type\Definition\Type::getInternalTypes();
        if ($this->isScalarType($typeName)) {
            return $internalTypes[$typeName];
        } else {
            throw new \LogicException(sprintf('Scalar type %s doesn\'t exist', $typeName));
        }
    }

    /**
     * Create an list array type
     *
     * @param \GraphQL\Type\Definition\ScalarType|\GraphQL\Type\Definition\Type $definedType
     * @return ListOfType
     */
    public function createList(\GraphQL\Type\Definition\Type $definedType) : ListOfType
    {
        return new ListOfType($definedType);
    }

    /**
     * Create a non null type
     *
     * @param \GraphQL\Type\Definition\ScalarType|\GraphQL\Type\Definition\Type $definedType
     * @return NonNull
     */
    public function createNonNull(\GraphQL\Type\Definition\Type $definedType) : NonNull
    {
        return new NonNull($definedType);
    }
}
