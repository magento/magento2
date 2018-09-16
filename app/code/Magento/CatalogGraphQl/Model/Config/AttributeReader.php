<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogGraphQl\Model\Config;

use Magento\Framework\Config\ReaderInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\Entity\MapperInterface;
use Magento\Framework\Reflection\TypeProcessor;
use Magento\EavGraphQl\Model\Resolver\Query\Type;
use Magento\CatalogGraphQl\Model\Resolver\Products\Attributes\Collection;

/**
 * Adds custom/eav attribute to Catalog product types in the GraphQL config.
 */
class AttributeReader implements ReaderInterface
{
    /**
     * @var MapperInterface
     */
    private $mapper;

    /**
     * @var Type
     */
    private $typeLocator;

    /**
     * @var Collection
     */
    private $collection;

    /**
     * @param MapperInterface $mapper
     * @param Type $typeLocator
     * @param Collection $collection
     */
    public function __construct(
        MapperInterface $mapper,
        Type $typeLocator,
        Collection $collection
    ) {
        $this->mapper = $mapper;
        $this->typeLocator = $typeLocator;
        $this->collection = $collection;
    }

    /**
     * Read configuration scope
     *
     * @param string|null $scope
     * @return array
     * @throws GraphQlInputException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function read($scope = null) : array
    {
        $typeNames = $this->mapper->getMappedTypes(\Magento\Catalog\Model\Product::ENTITY);
        $config =[];
        /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
        foreach ($this->collection->getAttributes() as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            $locatedType = $this->typeLocator->getType(
                $attributeCode,
                \Magento\Catalog\Model\Product::ENTITY
            ) ?: 'String';
            $locatedType = $locatedType === TypeProcessor::NORMALIZED_ANY_TYPE ? 'String' : ucfirst($locatedType);
            foreach ($typeNames as $typeName) {
                $config[$typeName]['fields'][$attributeCode] = [
                    'name' => $attributeCode,
                    'type' => $locatedType,
                    'arguments' => []
                ];
            }
        }

        return $config;
    }
}
