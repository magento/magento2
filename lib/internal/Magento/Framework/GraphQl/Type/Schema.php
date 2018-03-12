<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Type;

/**
 * Schema object for a GraphQL endpoint describing queries and types for a client to consume.
 */
class Schema extends \GraphQL\Type\Schema
{
    /**
     * @api
     * @param array|\GraphQL\Type\SchemaConfig $config
     */
    public function __construct($config)
    {
        $config = $this->replaceScalarTypes($config);
        parent::__construct($config);
    }

    private function replaceScalarTypes($config) : array
    {
        $recur = function (&$value) use (&$recur) {
            if ($value instanceof \GraphQL\Type\Definition\ObjectType) {
                /** @var \Magento\Framework\GraphQl\Type\Definition\ObjectType $value */
                $fields = $value->getFields();
                array_walk_recursive($fields, $recur);
            } elseif ($value instanceof \Graphql\Type\Definition\FieldDefinition) {
                /** @var \Graphql\Type\Definition\FieldDefinition $value */
                if ($value->config['type'] instanceof \Magento\Framework\GraphQl\Type\Definition\StringType) {
                    $value->config['type'] = \GraphQL\Type\Definition\Type::string();
                } elseif ($value->config['type'] instanceof \Magento\Framework\GraphQl\Type\Definition\IDType) {
                    $value->config['type'] = \GraphQL\Type\Definition\Type::id();
                } elseif ($value->config['type'] instanceof \Magento\Framework\GraphQl\Type\Definition\FloatType) {
                    $value->config['type'] = \GraphQL\Type\Definition\Type::float();
                } elseif ($value->config['type'] instanceof \Magento\Framework\GraphQl\Type\Definition\IntType) {
                    $value->config['type'] = \GraphQL\Type\Definition\Type::int();
                } elseif ($value->config['type'] instanceof \Magento\Framework\GraphQl\Type\Definition\BooleanType) {
                    $value->config['type'] = \GraphQL\Type\Definition\Type::boolean();
                }
            }
        };

        array_walk_recursive($config, $recur);

        return $config;
    }
}
