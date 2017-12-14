<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model\Type\Handler;

use Magento\GraphQl\Model\Type\Helper\ServiceContract\TypeGenerator;
use Magento\GraphQl\Model\Type\HandlerInterface;
use Magento\Framework\GraphQl\Type\TypeFactory;

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
     * Retrieve Product base fields
     *
     * @return array
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
