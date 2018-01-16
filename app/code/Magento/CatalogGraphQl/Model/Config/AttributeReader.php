<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogGraphQl\Model\Config;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Eav\Api\AttributeManagementInterface;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Config\ReaderInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Type\Entity\MapperInterface;
use Magento\Framework\Reflection\TypeProcessor;
use Magento\EavGraphQl\Model\Resolver\Query\Type;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;

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
     * @var ProductAttributeRepositoryInterface
     */
    private $productAttributeRepository;

    /**
     * @var Type
     */
    private $typeLocator;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var AttributeManagementInterface
     */
    private $attributeManagement;

    /**
     * @var AttributeSetRepositoryInterface
     */
    private $attributeSetRepository;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param MapperInterface $mapper
     * @param ProductAttributeRepositoryInterface $productAttributeRepository
     * @param Type $typeLocator
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param AttributeManagementInterface $attributeManagement
     * @param AttributeSetRepositoryInterface $attributeSetRepository
     */
    public function __construct(
        MapperInterface $mapper,
        ProductAttributeRepositoryInterface $productAttributeRepository,
        Type $typeLocator,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AttributeManagementInterface $attributeManagement,
        AttributeSetRepositoryInterface $attributeSetRepository,
        CollectionFactory $collectionFactory
    ) {
        $this->mapper = $mapper;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->typeLocator = $typeLocator;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributeManagement = $attributeManagement;
        $this->attributeSetRepository = $attributeSetRepository;
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
