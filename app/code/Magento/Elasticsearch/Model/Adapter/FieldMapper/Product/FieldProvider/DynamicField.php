<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider;

use Magento\Catalog\Api\CategoryListInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeProvider;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProviderInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\ConverterInterface
    as FieldTypeConverterInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldIndex\ConverterInterface
    as IndexTypeConverterInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\ResolverInterface
    as FieldNameResolver;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Provide dynamic fields for product.
 */
class DynamicField implements FieldProviderInterface
{
    /**
     * Category list.
     *
     * @var CategoryListInterface
     */
    private $categoryList;

    /**
     * Customer group repository.
     *
     * @var GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * Search criteria builder.
     *
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var FieldTypeConverterInterface
     */
    private $fieldTypeConverter;

    /**
     * @var IndexTypeConverterInterface
     */
    private $indexTypeConverter;

    /**
     * @var AttributeProvider
     */
    private $attributeAdapterProvider;

    /**
     * @var FieldNameResolver
     */
    private $fieldNameResolver;

    /**
     * @param FieldTypeConverterInterface $fieldTypeConverter
     * @param IndexTypeConverterInterface $indexTypeConverter
     * @param GroupRepositoryInterface $groupRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CategoryListInterface $categoryList
     * @param FieldNameResolver $fieldNameResolver
     * @param AttributeProvider $attributeAdapterProvider
     */
    public function __construct(
        FieldTypeConverterInterface $fieldTypeConverter,
        IndexTypeConverterInterface $indexTypeConverter,
        GroupRepositoryInterface $groupRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CategoryListInterface $categoryList,
        FieldNameResolver $fieldNameResolver,
        AttributeProvider $attributeAdapterProvider
    ) {
        $this->groupRepository = $groupRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->fieldTypeConverter = $fieldTypeConverter;
        $this->indexTypeConverter = $indexTypeConverter;
        $this->categoryList = $categoryList;
        $this->fieldNameResolver = $fieldNameResolver;
        $this->attributeAdapterProvider = $attributeAdapterProvider;
    }

    /**
     * @inheritdoc
     */
    public function getFields(array $context = []): array
    {
        $allAttributes = [];
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $categories = $this->categoryList->getList($searchCriteria)->getItems();
        $positionAttribute = $this->attributeAdapterProvider->getByAttributeCode('position');
        $categoryNameAttribute = $this->attributeAdapterProvider->getByAttributeCode('category_name');
        foreach ($categories as $category) {
            $categoryPositionKey = $this->fieldNameResolver->getFieldName(
                $positionAttribute,
                ['categoryId' => $category->getId()]
            );
            $categoryNameKey = $this->fieldNameResolver->getFieldName(
                $categoryNameAttribute,
                ['categoryId' => $category->getId()]
            );
            $allAttributes[$categoryPositionKey] = [
                'type' => $this->fieldTypeConverter->convert(FieldTypeConverterInterface::INTERNAL_DATA_TYPE_STRING),
                'index' => $this->indexTypeConverter->convert(IndexTypeConverterInterface::INTERNAL_NO_INDEX_VALUE)
            ];
            $allAttributes[$categoryNameKey] = [
                'type' => $this->fieldTypeConverter->convert(FieldTypeConverterInterface::INTERNAL_DATA_TYPE_STRING),
                'index' => $this->indexTypeConverter->convert(IndexTypeConverterInterface::INTERNAL_NO_INDEX_VALUE)
            ];
        }

        $groups = $this->groupRepository->getList($searchCriteria)->getItems();
        $priceAttribute = $this->attributeAdapterProvider->getByAttributeCode('price');
        $ctx = isset($context['websiteId']) ? ['websiteId' => $context['websiteId']] : [];
        foreach ($groups as $group) {
            $ctx['customerGroupId'] = $group->getId();
            $groupPriceKey = $this->fieldNameResolver->getFieldName(
                $priceAttribute,
                $ctx
            );
            $allAttributes[$groupPriceKey] = [
                'type' => $this->fieldTypeConverter->convert(FieldTypeConverterInterface::INTERNAL_DATA_TYPE_FLOAT),
                'store' => true
            ];
        }

        return $allAttributes;
    }
}
