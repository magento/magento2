<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Type;

use GraphQL\GraphQL;
use GraphQL\Type\Definition\Type as GraphQLType;
use Magento\Framework\GraphQl\Type\Definition\FloatType;
use Magento\Framework\GraphQl\Type\Definition\IntType;
use Magento\Framework\GraphQl\Type\Definition\StringType;

/**
 * Class containing shared methods for GraphQL type management
 */
class TypeManagement
{
    /**
     * Replace the standard type definitions with ones that know how to cast input values
     */
    public function overrideStandardGraphQLTypes(): void
    {
        $standardTypes = GraphQLType::getStandardTypes();
        $overrideTypes = [];
        if (!($standardTypes[GraphQLType::INT] instanceof IntType)) {
            $overrideTypes[GraphQLType::INT] = new IntType($standardTypes[GraphQLType::INT]->config);
        }
        if (!($standardTypes[GraphQLType::FLOAT] instanceof FloatType)) {
            $overrideTypes[GraphQLType::FLOAT] = new FloatType($standardTypes[GraphQLType::FLOAT]->config);
        }
        if (!($standardTypes[GraphQLType::STRING] instanceof StringType)) {
            $overrideTypes[GraphQLType::STRING] = new StringType($standardTypes[GraphQLType::STRING]->config);
        }
        if ($overrideTypes) {
            GraphQL::overrideStandardTypes($overrideTypes);
        }
    }
}
