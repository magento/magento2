<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogGraphQl\Model\Config;

use Magento\Catalog\Model\ResourceModel\Category\Attribute\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Category\Attribute\Collection;
use Magento\Framework\Config\ReaderInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Type\Entity\MapperInterface;
use Magento\Framework\Reflection\TypeProcessor;
use Magento\EavGraphQl\Model\Resolver\Query\Type;

/**
 * Adds custom/eav attribute to Catalog category types in the GraphQL config.
 */
class CategoryAttributeReader implements ReaderInterface
{
    /**
     * @var MapperInterface
     */
    private $mapper;

    /**
     * In database data type is differ to graphql type, but eventually type for this attributes
     * will be casted without problems
     *
     * @var array
     */
    private static $bannedSystemAttributes = [
        'position',
        'is_active',
        'children',
        'level'
    ];

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
        $config =[];
        $data = [];
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
        foreach ($collection as $attribute) {
            $attributeCode = $attribute->getAttributeCode();

            if (in_array($attributeCode, self::$bannedSystemAttributes)) {
                continue;
            }

            $locatedType = $this->typeLocator->getType(
                $attributeCode,
                'catalog_category'
            ) ?: 'String';
            $locatedType = $locatedType === TypeProcessor::NORMALIZED_ANY_TYPE ? 'String' : ucfirst($locatedType);
            $data['fields'][$attributeCode]['name'] = $attributeCode;
            $data['fields'][$attributeCode]['type'] = $locatedType;
            $data['fields'][$attributeCode]['arguments'] = [];
        }

        $config['CategoryInterface'] = $data;
        $config['CategoryTree'] = $data;

        return $config;
    }
}
