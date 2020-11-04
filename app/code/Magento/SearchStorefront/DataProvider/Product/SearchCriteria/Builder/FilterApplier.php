<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SearchStorefront\DataProvider\Product\SearchCriteria\Builder;

use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\SearchStorefrontApi\Api\Data\ProductSearchRequestInterface;

/**
 * Class FilterApplier
 */
class FilterApplier implements ApplierInterface
{
    const EQ_CONDITION_TYPE = 'eq';
    const IN_CONDITION_TYPE = 'in';

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var \Magento\Framework\Api\Search\FilterGroupBuilder
     */
    private $filterGroupBuilder;

    /**
     * FilterApplier constructor.
     *
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Framework\Api\Search\FilterGroupBuilder $filterGroupBuilder
     */
    public function __construct(
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\Api\Search\FilterGroupBuilder $filterGroupBuilder
    ) {
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
    }

    /**
     * Convert search request filters to search criteria filters.
     *
     * @param $request
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchCriteriaInterface
     * @throws LocalizedException
     */
    public function apply(
        ProductSearchRequestInterface $request,
        SearchCriteriaInterface $searchCriteria
    ) : SearchCriteriaInterface {
        $filters = $request->getFilters();

        if (empty($filters)) {
            return $searchCriteria;
        }

        foreach ($filters as $filter) {
            if (!empty($filter->getAttribute())) {
                $range = $filter->getRange();
                $attribute = $filter->getAttribute();
                $conditionType = null;

                if (!empty($filter->getEq())) {
                    $value = $filter->getEq();
                } elseif (!empty($filter->getIn())) {
                    $value = $filter->getIn();
                    $conditionType = 'in';
                } elseif (!empty($range) && ($range->getFrom() || $range->getTo())) {
                    $value = ['from' => $range->getFrom(), 'to' => $range->getTo()];
                } else {
                    throw new LocalizedException(__('Unsupported filter for attribute %1', $attribute));
                }

                $searchCriteria = $this->addFilter($searchCriteria, $attribute, $value, $conditionType);
            }
        }

        return $searchCriteria;
    }

    /**
     * Create and add filter group to search criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param string $field
     * @param $value
     * @param string|null $condition
     * @return SearchCriteriaInterface
     */
    public function addFilter(
        SearchCriteriaInterface $searchCriteria,
        string $field,
        $value,
        ?string $condition = null
    ) {
        if (!empty($attributeValue['from']) || !empty($attributeValue['to'])) {
            $this->addRangeAttributeToSearch($field, $value);
        } else {
            $filter = $this->filterBuilder
                ->setField($field)
                ->setValue($value)
                ->setConditionType($condition)
                ->create();
            $this->filterGroupBuilder->addFilter($filter);
        }

        $filterGroups = $searchCriteria->getFilterGroups();
        $filterGroups[] = $this->filterGroupBuilder->create();
        $searchCriteria->setFilterGroups($filterGroups);

        return $searchCriteria;
    }

    /**
     * Apply range filter to search criteria.
     *
     * @param $attributeCode
     * @param $attributeValue
     */
    private function addRangeAttributeToSearch($attributeCode, $attributeValue): void
    {
        if (isset($attributeValue['from']) && '' !== $attributeValue['from']) {
            $filterFrom = $this->filterBuilder->setField("{$attributeCode}.from")->setValue($attributeValue['from']);
            $this->filterGroupBuilder->addFilter($filterFrom->create());
        }

        if (isset($attributeValue['to']) && '' !== $attributeValue['to']) {
            $filterTo = $this->filterBuilder->setField("{$attributeCode}.to")->setValue($attributeValue['to']);
            $this->filterGroupBuilder->addFilter($filterTo->create());
        }
    }
}
