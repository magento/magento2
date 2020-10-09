<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\CategoryRepository;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Attribute\ScopeOverriddenValue;
use Magento\Catalog\Model\Category;
use Magento\Eav\Api\AttributeRepositoryInterface as AttributeRepository;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Add data to category entity and populate with default values
 */
class PopulateWithValues
{
    /**
     * @var ScopeOverriddenValue
     */
    private $scopeOverriddenValue;

    /**
     * @var AttributeRepository
     */
    private $attributeRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @param ScopeOverriddenValue $scopeOverriddenValue
     * @param AttributeRepository $attributeRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     */
    public function __construct(
        ScopeOverriddenValue $scopeOverriddenValue,
        AttributeRepository $attributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder
    ) {
        $this->scopeOverriddenValue = $scopeOverriddenValue;
        $this->attributeRepository = $attributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * Set null to entity default values
     *
     * @param CategoryInterface $category
     * @param array $existingData
     * @return void
     */
    public function execute(CategoryInterface $category, array $existingData): void
    {
        $storeId = $existingData['store_id'];
        $overriddenValues = array_filter($category->getData(), function ($key) use ($category, $storeId) {
            /** @var Category $category */
            return $this->scopeOverriddenValue->containsValue(
                CategoryInterface::class,
                $category,
                $key,
                $storeId
            );
        }, ARRAY_FILTER_USE_KEY);
        $defaultValues = array_diff_key($category->getData(), $overriddenValues);
        array_walk($defaultValues, function (&$value, $key) {
            $attributes = $this->getAttributes();
            if (isset($attributes[$key]) && !$attributes[$key]->isStatic()) {
                $value = null;
            }
        });
        $category->addData($defaultValues);
        $category->addData($existingData);
        $useDefaultAttributes = array_filter($category->getData(), function ($attributeValue) {
            return null === $attributeValue;
        });
        $category->setData('use_default', array_map(function () {
            return true;
        }, $useDefaultAttributes));
    }

    /**
     * Returns entity attributes.
     *
     * @return AttributeInterface[]
     */
    private function getAttributes()
    {
        $searchResult = $this->attributeRepository->getList(
            $this->searchCriteriaBuilder->addFilters(
                [
                    $this->filterBuilder
                        ->setField('is_global')
                        ->setConditionType('in')
                        ->setValue([ScopedAttributeInterface::SCOPE_STORE, ScopedAttributeInterface::SCOPE_WEBSITE])
                        ->create()
                ]
            )->create()
        );
        $result = [];
        foreach ($searchResult->getItems() as $attribute) {
            $result[$attribute->getAttributeCode()] = $attribute;
        }

        return $result;
    }
}
