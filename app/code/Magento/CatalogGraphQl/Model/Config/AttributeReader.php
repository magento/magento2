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
use Magento\GraphQl\Model\EntityAttributeList;
use Magento\EavGraphQl\Model\Resolver\Query\Type;

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
     * @param MapperInterface $mapper
     * @param ProductAttributeRepositoryInterface $productAttributeRepository
     * @param Type $typeLocator
     */
    public function __construct(
        MapperInterface $mapper,
        ProductAttributeRepositoryInterface $productAttributeRepository,
        Type $typeLocator,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AttributeManagementInterface $attributeManagement,
        AttributeSetRepositoryInterface $attributeSetRepository
    ) {
        $this->mapper = $mapper;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->typeLocator = $typeLocator;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributeManagement = $attributeManagement;
        $this->attributeSetRepository = $attributeSetRepository;
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
        $this->searchCriteriaBuilder->addFilters(
            [
                $this->filterBuilder
                    ->setField('entity_type_code')
                    ->setValue(\Magento\Catalog\Model\Product::ENTITY)
                    ->setConditionType('eq')
                    ->create(),
            ]
        );
        $attributeSetList = $this->attributeSetRepository->getList($this->searchCriteriaBuilder->create())->getItems();
        $attributes = [];
        $eavStaticAttributeCodes = [];
        foreach ($attributeSetList as $attributeSet) {
            try {
                $attributes = array_merge(
                    $attributes,
                    $this->attributeManagement->getAttributes(
                        \Magento\Catalog\Model\Product::ENTITY,
                        $attributeSet->getAttributeSetId()
                    )
                );
            } catch (NoSuchEntityException $exception) {
                throw new GraphQlInputException(
                    __('Entity code %1 does not exist.', [\Magento\Catalog\Model\Product::ENTITY])
                );
            }
        }
        /** @var AttributeInterface $attribute */
        foreach ($attributes as $attribute) {
            $eavStaticAttributeCodes[] = $attribute->getAttributeCode();
        }
        $targetStructures = $this->mapper->getMappedTypes(\Magento\Catalog\Model\Product::ENTITY);
        $config =[];
        $attributeList = $this->productAttributeRepository->getCustomAttributesMetadata();
        $attributeCodes = [];
        foreach ($attributeList as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            $attributeCodes[] = $attributeCode;
            if (in_array($attributeCode, $eavStaticAttributeCodes)) {
                continue;
            }
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
