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
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Language\AST\ValueNode;

/**
 * Replacement for the StringType definition that can typecast non-string values for backwards compatibility
 */
class StringType extends \GraphQL\Type\Definition\StringType
{
    /**
     * Try to typecast valid values before running the native validations
     *
     * @param mixed $value
     * @return string
     * @throws GraphQLError
     */
    public function parseValue($value): string
    {
        if (!is_array($value)) {
            $value = (string)$value;
        }
        return parent::parseValue($value);
    }

    /**
     * Try to parse the literal value the same way as a variable before calling the native literal parsing
     *
     * @param Node $valueNode
     * @param array|null $variables
     * @return string
     * @throws Exception
     */
    public function parseLiteral(Node $valueNode, ?array $variables = null): string
    {
        try {
            if ($valueNode instanceof ValueNode
                && !($valueNode instanceof StringValueNode)
                && isset($valueNode->value)) {
                $valueNode = new StringValueNode([
                    'value' => $this->parseValue($valueNode->value),
                    'loc' => $valueNode->loc
                ]);
            }
        } catch (Exception $e) {} // @codingStandardsIgnoreLine
        return parent::parseLiteral($valueNode, $variables);
    }
}
