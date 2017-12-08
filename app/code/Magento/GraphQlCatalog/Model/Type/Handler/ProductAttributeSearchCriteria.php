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
use Magento\Eav\Setup\EavSetupFactory;

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
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @param Pool $pool
     * @param AttributeManagementInterface $management
     * @param TypeFactory $typeFactory
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        Pool $pool,
        AttributeManagementInterface $management,
        TypeFactory $typeFactory,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->pool = $pool;
        $this->management = $management;
        $this->typeFactory = $typeFactory;
        $this->eavSetupFactory = $eavSetupFactory;
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
        $eavSetup = $this->eavSetupFactory->create();
        $productEntityCode = \Magento\Catalog\Model\Product::ENTITY;
        $schema = [];
        $defaultAttributeSetId = $eavSetup->getDefaultAttributeSetId($productEntityCode);
        $attributes = $this->management->getAttributes($productEntityCode, $defaultAttributeSetId);
        foreach ($attributes as $attribute) {
            $schema[$attribute->getAttributeCode()] = $this->pool->getType('SearchCriteriaExpression');
        }

        $fields = function () use ($schema, $className) {
            $schema = array_merge($schema, ['or' => $this->pool->getType($className)]);

            return $schema;
        };

        return $fields;
    }
}
