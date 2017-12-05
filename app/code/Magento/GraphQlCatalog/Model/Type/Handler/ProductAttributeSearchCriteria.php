<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQlCatalog\Model\Type\Handler;

use Magento\Eav\Api\AttributeManagementInterface;
use Magento\GraphQl\Model\Type\ServiceContract\TypeGenerator;
use Magento\GraphQl\Model\Type\HandlerInterface;
use Magento\Framework\GraphQl\Type\TypeFactory;
use Magento\GraphQl\Model\Type\Handler\Pool;

/**
 * Define ProductAttributeSearchCriteria's GraphQL type
 */
class ProductAttributeSearchCriteria implements HandlerInterface
{
    /**
     * @var TypeGenerator
     */
    private $pool;

    /**
     * @var AttributeManagementInterface
     */
    private $management;

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @param Pool $pool
     * @param AttributeManagementInterface $management
     * @param TypeFactory $typeFactory
     */
    public function __construct(
        Pool $pool,
        AttributeManagementInterface $management,
        TypeFactory $typeFactory
    ) {
        $this->pool = $pool;
        $this->management = $management;
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
     * @return \Closure|array
     */
    private function getFields()
    {
        $reflector = new \ReflectionClass($this);
        $className = $reflector->getShortName();
        $schema = [];
        $attributes = $this->management->getAttributes('catalog_product', 4);
        foreach ($attributes as $attribute) {
            $schema[$attribute->getAttributeCode()] = $this->pool->getType('SearchCriteriaExpression');
        }

        $fields = function () use ($schema, $className) {
            array_merge($schema, ['or' => $this->pool->getType($className)]);

            return $schema;
        };

        return $fields;
    }
}
