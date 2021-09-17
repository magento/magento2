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
 * Replacement for the StringType definition that can typecast non-string values for backwards compatibility
 */
class StringType extends \GraphQl\Type\Definition\StringType
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
            return $this->parseValue($valueNode->value);
        } catch (Exception $e) {
        }
        return parent::parseLiteral($valueNode);
    }
}
