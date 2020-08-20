<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider;

use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeProvider;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldIndex\ConverterInterface
    as IndexTypeConverterInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\ResolverInterface
    as FieldNameResolver;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\ConverterInterface
    as FieldTypeConverterInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProviderInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Model\ResourceModel\Category\Collection;

/**
 * Provide dynamic fields for product.
 */
class DynamicField implements FieldProviderInterface
{
    /**
     * Category collection.
     *
     * @var Collection
     */
    private $categoryCollection;

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
     * @param FieldNameResolver $fieldNameResolver
     * @param AttributeProvider $attributeAdapterProvider
     * @param Collection $categoryCollection
     */
    public function __construct(
        FieldTypeConverterInterface $fieldTypeConverter,
        IndexTypeConverterInterface $indexTypeConverter,
        GroupRepositoryInterface $groupRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FieldNameResolver $fieldNameResolver,
        AttributeProvider $attributeAdapterProvider,
        Collection $categoryCollection
    ) {
        $this->groupRepository = $groupRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->fieldTypeConverter = $fieldTypeConverter;
        $this->indexTypeConverter = $indexTypeConverter;
        $this->fieldNameResolver = $fieldNameResolver;
        $this->attributeAdapterProvider = $attributeAdapterProvider;
        $this->categoryCollection = $categoryCollection;
    }

    /**
     * @inheritdoc
     */
    public function getFields(array $context = []): array
    {
        $allAttributes = [];
        $categoryIds = $this->categoryCollection->getAllIds();
        $positionAttribute = $this->attributeAdapterProvider->getByAttributeCode('position');
        $categoryNameAttribute = $this->attributeAdapterProvider->getByAttributeCode('category_name');
        foreach ($categoryIds as $categoryId) {
            $categoryPositionKey = $this->fieldNameResolver->getFieldName(
                $positionAttribute,
                ['categoryId' => $categoryId]
            );
            $categoryNameKey = $this->fieldNameResolver->getFieldName(
                $categoryNameAttribute,
                ['categoryId' => $categoryId]
            );
            $allAttributes[$categoryPositionKey] = [
                'type' => $this->fieldTypeConverter->convert(FieldTypeConverterInterface::INTERNAL_DATA_TYPE_INT),
                'index' => $this->indexTypeConverter->convert(IndexTypeConverterInterface::INTERNAL_NO_INDEX_VALUE)
            ];
            $allAttributes[$categoryNameKey] = [
                'type' => $this->fieldTypeConverter->convert(FieldTypeConverterInterface::INTERNAL_DATA_TYPE_STRING),
                'index' => $this->indexTypeConverter->convert(IndexTypeConverterInterface::INTERNAL_NO_INDEX_VALUE)
            ];
        }

        $searchCriteria = $this->searchCriteriaBuilder->create();
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
