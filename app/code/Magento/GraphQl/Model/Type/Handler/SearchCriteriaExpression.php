<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model\Type\Handler;

use Magento\Framework\GraphQl\Type\Definition\InputObjectType;
use Magento\Framework\GraphQl\Type\Definition\Type;
use Magento\GraphQl\Model\Type\Helper\ServiceContract\TypeGenerator;
use Magento\GraphQl\Model\Type\HandlerInterface;
use Magento\Framework\GraphQl\Type\TypeFactory;

/**
 * Define SearchCriteriaExpression GraphQL type
 */
class SearchCriteriaExpression implements HandlerInterface
{
    /**
     * @var TypeGenerator
     */
    private $typeGenerator;

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @param TypeGenerator $typeGenerator
     * @param TypeFactory $typeFactory
     */
    public function __construct(TypeGenerator $typeGenerator, TypeFactory $typeFactory)
    {
        $this->typeGenerator = $typeGenerator;
        $this->typeFactory = $typeFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        $reflector = new \ReflectionClass($this);
        return $this->typeFactory->createInputObject(
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
