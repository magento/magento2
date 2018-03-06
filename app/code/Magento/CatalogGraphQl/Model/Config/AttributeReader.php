<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogGraphQl\Model\Config;

use Magento\Framework\Config\ReaderInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Type\Entity\MapperInterface;
use Magento\Framework\Reflection\TypeProcessor;
use Magento\EavGraphQl\Model\Resolver\Query\Type;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;

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
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param MapperInterface $mapper
     * @param Type $typeLocator
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        MapperInterface $mapper,
        Type $typeLocator,
        CollectionFactory $collectionFactory
    ) {
        $this->mapper = $mapper;
        $this->typeLocator = $typeLocator;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Read configuration scope
     *
     * @param string|null $scope
     * @return array
     * @throws GraphQlInputException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function read($scope = null)
    {
        $targetStructures = $this->mapper->getMappedTypes(\Magento\Catalog\Model\Product::ENTITY);
        $config =[];
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('is_user_defined', '1');
        $collection->addFieldToFilter('attribute_code', ['neq' => 'cost']);
        /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
        foreach ($collection as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            $locatedType = $this->typeLocator->getType(
                $attributeCode,
                \Magento\Catalog\Model\Product::ENTITY
            ) ?: 'String';
            $locatedType = $locatedType === TypeProcessor::NORMALIZED_ANY_TYPE ? 'String' : ucfirst($locatedType);
            foreach ($targetStructures as $structure) {
                $config[$structure]['fields'][$attributeCode] = [
                    'name' => $attributeCode,
                    'type' => $locatedType,
                    'arguments' => []
                ];
            }
        }

        return $config;
    }
}
