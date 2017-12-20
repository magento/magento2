<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQlCatalog\Model\Type\Handler;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\GraphQl\Model\EntityAttributeList;
use Magento\GraphQl\Model\Type\ServiceContract\TypeGenerator;
use Magento\GraphQl\Model\Type\HandlerInterface;
use Magento\Framework\GraphQl\TypeFactory;
use Magento\GraphQl\Model\Type\Handler\Pool;
use Magento\GraphQl\Model\Type\Handler\SearchCriteriaExpression;

/**
 * Define ProductAttributeSearchCriteria's GraphQL type
 */
class ProductAttributeSearchCriteria implements HandlerInterface
{
    const PRODUCT_ATTRIBUTE_SEARCH_CRITERIA_TYPE_NAME = 'ProductAttributeSearchCriteria';

    /**
     * @var TypeGenerator
     */
    private $pool;

    /**
     * @var EntityAttributeList
     */
    private $entityAttributeList;

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $productAttributeRepository;

    /**
     * @param Pool $pool
     * @param EntityAttributeList $entityAttributeList
     * @param TypeFactory $typeFactory
     * @param ProductAttributeRepositoryInterface $productAttributeRepository
     */
    public function __construct(
        Pool $pool,
        EntityAttributeList $entityAttributeList,
        TypeFactory $typeFactory,
        ProductAttributeRepositoryInterface $productAttributeRepository
    ) {
        $this->pool = $pool;
        $this->entityAttributeList = $entityAttributeList;
        $this->typeFactory = $typeFactory;
        $this->productAttributeRepository = $productAttributeRepository;
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
        $productAttributeSearchCriteriaClassName = self::PRODUCT_ATTRIBUTE_SEARCH_CRITERIA_TYPE_NAME;
        $attributes = $this->entityAttributeList->getDefaultEntityAttributes(
            \Magento\Catalog\Model\Product::ENTITY,
            $this->productAttributeRepository
        );
        $schema = [];
        foreach (array_keys($attributes) as $attributeCode) {
            $schema[$attributeCode] = $this->pool->getType(
                SearchCriteriaExpression::SEARCH_CRITERIA_EXPRESSION_TYPE_NAME
            );
        }

        $fields = function () use ($schema, $productAttributeSearchCriteriaClassName) {
            $schema = array_merge($schema, ['or' => $this->pool->getType($productAttributeSearchCriteriaClassName)]);

            return $schema;
        };

        return $fields;
    }
}
