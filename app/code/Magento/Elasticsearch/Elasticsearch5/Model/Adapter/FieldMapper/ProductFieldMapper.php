<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Elasticsearch5\Model\Adapter\FieldMapper;

use Magento\Catalog\Api\CategoryListInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Eav\Model\Config;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldName\ResolverInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;
use Magento\Elasticsearch\Elasticsearch5\Model\Adapter\FieldType;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Class ProductFieldMapper
 */
class ProductFieldMapper implements FieldMapperInterface
{
    /**
     * @var Config
     */
    protected $eavConfig;

    /**
     * @var FieldType
     */
    protected $fieldType;

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
     * @var ResolverInterface
     */
    private $fieldNameResolver;

    /**
     * @param Config $eavConfig
     * @param FieldType $fieldType
     * @param GroupRepositoryInterface $groupRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ResolverInterface $fieldNameResolver
     */
    public function __construct(
        Config $eavConfig,
        FieldType $fieldType,
        GroupRepositoryInterface $groupRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ResolverInterface $fieldNameResolver
    ) {
        $this->eavConfig = $eavConfig;
        $this->fieldType = $fieldType;
        $this->groupRepository = $groupRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->fieldNameResolver = $fieldNameResolver;
    }

    /**
     * Get field name.
     *
     * @param string $attributeCode
     * @param array $context
     * @return string
     */
    public function getFieldName($attributeCode, $context = [])
    {
        return $this->fieldNameResolver->getFieldName($attributeCode, $context);
    }

    /**
     * Get all attributes types.
     *
     * @param array $context
     * @return array
     */
    public function getAllAttributesTypes($context = [])
    {
        return array_merge(
            $this->getAllStaticAttributesTypes(),
            $this->getAllDynamicAttributesTypes()
        );
    }

    /**
     * @param Object $attribute
     * @return bool
     */
    protected function isAttributeUsedInAdvancedSearch($attribute)
    {
        return $attribute->getIsVisibleInAdvancedSearch()
        || $attribute->getIsFilterable()
        || $attribute->getIsFilterableInSearch();
    }

    /**
     * Prepare mapping data for static attributes.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return array
     */
    private function getAllStaticAttributesTypes()
    {
        $attributeCodes = $this->eavConfig->getEntityAttributeCodes(ProductAttributeInterface::ENTITY_TYPE_CODE);
        $allAttributes = [];
        // List of attributes which are required to be indexable
        $alwaysIndexableAttributes = [
            'category_ids',
            'visibility',
        ];

        foreach ($attributeCodes as $attributeCode) {
            $attribute = $this->eavConfig->getAttribute(ProductAttributeInterface::ENTITY_TYPE_CODE, $attributeCode);

            $allAttributes[$attributeCode] = [
                'type' => $this->fieldType->getFieldType($attribute),
            ];

            if (!$attribute->getIsSearchable() && !$this->isAttributeUsedInAdvancedSearch($attribute)
                && !in_array($attributeCode, $alwaysIndexableAttributes, true)
            ) {
                if ($attribute->getIsFilterable() || $attribute->getIsFilterableInSearch()) {
                    $allAttributes[$attributeCode]['type'] = FieldType::ES_DATA_TYPE_KEYWORD;
                } else if ($allAttributes[$attributeCode]['type'] === FieldType::ES_DATA_TYPE_TEXT) {
                    $allAttributes[$attributeCode]['index'] = false;
                }
            } else if ($attributeCode == "category_ids") {
                $allAttributes[$attributeCode] = [
                    'type' => FieldType::ES_DATA_TYPE_INT,
                ];
            }

            if ($attribute->usesSource()
                || $attribute->getFrontendInput() === 'select'
                || $attribute->getFrontendInput() === 'multiselect'
            ) {
                $allAttributes[$attributeCode]['type'] = FieldType::ES_DATA_TYPE_KEYWORD;

                $allAttributes[$attributeCode . '_value'] = [
                    'type' => FieldType::ES_DATA_TYPE_TEXT,
                ];
            }
        }

        return $allAttributes;
    }

    /**
     * Prepare mapping data for dynamic attributes.
     *
     * @return array
     */
    private function getAllDynamicAttributesTypes()
    {
        $allAttributes = [];
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $categories = $this->categoryList->getList($searchCriteria)->getItems();
        foreach ($categories as $category) {
            $categoryPositionKey = $this->getFieldName('position', ['categoryId' => $category->getId()]);
            $categoryNameKey = $this->getFieldName('category_name', ['categoryId' => $category->getId()]);
            $allAttributes[$categoryPositionKey] = [
                'type' => FieldType::ES_DATA_TYPE_TEXT,
                'index' => false
            ];
            $allAttributes[$categoryNameKey] = [
                'type' => FieldType::ES_DATA_TYPE_TEXT,
                'index' => false
            ];
        }

        $groups = $this->groupRepository->getList($searchCriteria)->getItems();
        foreach ($groups as $group) {
            $groupPriceKey = $this->getFieldName('price', ['customerGroupId' => $group->getId()]);
            $allAttributes[$groupPriceKey] = [
                'type' => FieldType::ES_DATA_TYPE_FLOAT,
                'store' => true
            ];
        }

        return $allAttributes;
    }
}
