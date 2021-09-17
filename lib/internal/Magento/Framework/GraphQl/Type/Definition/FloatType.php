<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Type\Definition;

use Exception;
use GraphQL\Error\Error as GraphQLError;
use GraphQL\Language\AST\Node;

/**
 * Replacement for the FloatType definition that can typecast non-numeric values for backwards compatibility
 */
class FloatType extends \GraphQL\Type\Definition\FloatType
{
    /**
     * Try to typecast valid values before running the native validations
     *
     * @param mixed $value
     * @return float
     * @throws GraphQLError
     */
    public function parseValue($value): float
    {
        if ($value !== '' && (is_numeric($value) || is_bool($value))) {
            $value = (float)$value;
        }
        return parent::parseValue($value);
    }

    /**
     * Try to parse the literal value the same way as a variable before calling the native literal parsing
     *
     * @param Node $valueNode
     * @param array|null $variables
     * @return float
     * @throws Exception
     */
    public function parseLiteral(Node $valueNode, ?array $variables = null): float
    {
        try {
            return $this->parseValue($valueNode->value);
        } catch (Exception $e) {
        }
        return parent::parseLiteral($valueNode);
    }
}
