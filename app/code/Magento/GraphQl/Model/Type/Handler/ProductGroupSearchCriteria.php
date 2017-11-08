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
 * Define ProductGroupSearchCriteria's GraphQL type
 */
class ProductGroupSearchCriteria implements HandlerInterface
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
     * Retrieve Product base fields
     *
     * @return Type[]
     */
    private function getFields()
    {
        $reflector = new \ReflectionClass($this);
        $className = $reflector->getShortName();
        $result = ['and' => 'ProductAttributeSearchCriteria'];
        $resolvedTypes = $this->typeGenerator->generate($className, $result);
        $fields = $resolvedTypes->config['fields'];

        return $fields;
    }
}
