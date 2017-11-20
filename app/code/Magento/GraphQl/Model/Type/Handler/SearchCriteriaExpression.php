<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model\Type\Handler;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use Magento\GraphQl\Model\Type\Helper\ServiceContract\TypeGenerator;
use Magento\GraphQl\Model\Type\HandlerInterface;

/**
 * Define SearchCriteriaExpression's GraphQL type
 */
class SearchCriteriaExpression implements HandlerInterface
{
    /**
     * @var TypeGenerator
     */
    private $typeGenerator;

    /**
     * @param TypeGenerator $typeGenerator
     */
    public function __construct(TypeGenerator $typeGenerator)
    {
        $this->typeGenerator = $typeGenerator;
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        $reflector = new \ReflectionClass($this);
        return new InputObjectType(
            [
                'name' => $reflector->getShortName(),
                'fields' => $this->getFields()
            ]
        );
    }

    /**
     * Retrieve fields
     *
     * @return Type[]
     */
    private function getFields()
    {
        $reflector = new \ReflectionClass($this);
        $className = $reflector->getShortName();

        $schema = [
            'eq' => 'String',
            'finset' => ['String'],
            'from' => 'String',
            'gt' => 'String',
            'gteq' => 'String',
            'in' => ['String'],
            'like' => 'String',
            'lt' => 'String',
            'lteq' => 'String',
            'moreq' => 'String',
            'neq' => 'String',
            'nin' => ['String'],
            'notnull' => 'String',
            'null' => 'String',
            'to' => 'String',
        ];
        $resolvedTypes = $this->typeGenerator->generate($className, $schema);
        $fields = $resolvedTypes->config['fields'];

        return $fields;
    }
}
