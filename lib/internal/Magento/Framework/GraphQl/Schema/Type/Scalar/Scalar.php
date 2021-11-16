<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Schema\Type\Scalar;

use GraphQL\Language\AST\Node;
use Magento\Framework\GraphQl\Config\Element\Scalar as ScalarElement;
use Magento\Framework\GraphQl\Schema\Type\CustomScalarType;

/**
 * Custom scalar type configuration processor
 */
class Scalar extends CustomScalarType
{
    public function __construct(ScalarElement $configElement)
    {
        $config = [
            'name' => $configElement->getName(),
            'description' => $configElement->getDescription(),
            'serialize' =>
                static function($value) use ($configElement) {return call_user_func([$configElement->getDefinition(), 'serialize'], $value);},
            'parseValue' =>
                static function($value) use ($configElement) {return call_user_func([$configElement->getDefinition(), 'parseValue'], $value);},
            'parseLiteral' =>
                static function(Node $valueNode, ?array $variables = null) use ($configElement) {return call_user_func([$configElement->getDefinition(), 'parseLiteral'], $valueNode, $variables);},
        ];
        parent::__construct($config);
    }

}
