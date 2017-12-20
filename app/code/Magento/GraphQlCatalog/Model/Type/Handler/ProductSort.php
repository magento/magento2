<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQlCatalog\Model\Type\Handler;

use Magento\GraphQl\Model\EntityAttributeList;
use Magento\GraphQl\Model\Type\ServiceContract\TypeGenerator as Generator;
use Magento\GraphQl\Model\Type\HandlerInterface;
use Magento\Framework\GraphQl\TypeFactory;
use Magento\GraphQl\Model\Type\Handler\Pool;
use Magento\GraphQl\Model\Type\Handler\SortEnum;

/**
 * Define ProductSort GraphQL type
 */
class ProductSort implements HandlerInterface
{
    const PRODUCTS_SORT_TYPE_NAME = 'ProductSort';

    /**
     * @var Pool
     */
    private $typePool;

    /**
     * @var Generator
     */
    private $typeGenerator;

    /**
     * @var EntityAttributeList
     */
    private $entityAttributeList;

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @param Pool $typePool
     * @param Generator $typeGenerator
     * @param EntityAttributeList $entityAttributeList
     * @param TypeFactory $typeFactory
     */
    public function __construct(
        Pool $typePool,
        Generator $typeGenerator,
        EntityAttributeList $entityAttributeList,
        TypeFactory $typeFactory
    ) {
        $this->typePool = $typePool;
        $this->typeGenerator = $typeGenerator;
        $this->entityAttributeList = $entityAttributeList;
        $this->typeFactory = $typeFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        return $this->typeFactory->createInputObject(
            [
                'name' => self::PRODUCTS_SORT_TYPE_NAME,
                'fields' => $this->getFields(),
            ]
        );
    }

    /**
     * Retrieve fields
     *
     * @return array
     */
    private function getFields()
    {
        $attributes = $this->entityAttributeList->getDefaultEntityAttributes(\Magento\Catalog\Model\Product::ENTITY);
        $result = [];
        foreach ($attributes as $attributeCode => $sortable) {
            if ($sortable) {
                $result[$attributeCode] = $this->typePool->getType(SortEnum::SORT_ENUM_TYPE_NAME);
            }
        }

        $staticAttributes = $this->typeGenerator->getTypeData('CatalogDataProductInterface');
        foreach ($staticAttributes as $attributeKey => $attribute) {
            if (is_array($attribute)) {
                unset($staticAttributes[$attributeKey]);
            } else {
                $staticAttributes[$attributeKey] = $this->typePool->getType(SortEnum::SORT_ENUM_TYPE_NAME);
            }
        }

        $result = array_merge($result, $staticAttributes);

        return $result;
    }
}
