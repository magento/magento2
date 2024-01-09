<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Type\Definition;

use Exception;
use GraphQL\Error\Error as GraphQLError;
use GraphQL\Language\AST\IntValueNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\ValueNode;

/**
 * Replacement for the IntType definition that can typecast non-numeric values for backwards compatibility
 */
class IntType extends \GraphQL\Type\Definition\IntType
{
    /**
     * Try to typecast valid values before running the native validations
     *
     * @param mixed $value
     * @return int
     * @throws GraphQLError
     */
    public function parseValue($value): int
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
     * @return int
     * @throws Exception
     */
    public function parseLiteral(Node $valueNode, ?array $variables = null): int
    {
        try {
            if ($valueNode instanceof ValueNode
                && !($valueNode instanceof IntValueNode)
                && isset($valueNode->value)) {
                $valueNode = new IntValueNode([
                    'value' => (string)$this->parseValue($valueNode->value),
                    'loc' => $valueNode->loc
                ]);
            }
        } catch (Exception $e) {} // @codingStandardsIgnoreLine
        return parent::parseLiteral($valueNode, $variables);
    }
}
