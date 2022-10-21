<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Variable\Ui\Component;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\App\RequestInterface;

/**
 * Data provider for variables_modal listing
 */
class VariablesDataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    /**
     * @var \Magento\Variable\Model\Variable\Data
     */
    private $variableDataProvider;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param ReportingInterface $reporting
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RequestInterface $request
     * @param FilterBuilder $filterBuilder
     * @param \Magento\Variable\Model\Variable\Data $variableDataProvider
     * @param array $meta
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        \Magento\Variable\Model\Variable\Data $variableDataProvider,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $meta,
            $data
        );
        $this->variableDataProvider = $variableDataProvider;
    }

    /**
     * Sort variables array by field.
     *
     * @param array $items
     * @param string $field
     * @param string $direction
     * @return array
     */
    private function sortBy($items, $field, $direction)
    {
        usort(
            $items,
            function ($item1, $item2) use ($field, $direction) {
                return $this->variablesCompare($item1, $item2, $field, $direction);
            }
        );
        return $items;
    }

    /**
     * Compare variables array's elements on index.
     *
     * @param array $variable1
     * @param array $variable2
     * @param string $partIndex
     * @param string $direction
     *
     * @return int
     */
    private function variablesCompare($variable1, $variable2, $partIndex, $direction)
    {
        $values = [$variable1[$partIndex], $variable2[$partIndex]];
        sort($values, SORT_STRING);
        return $variable1[$partIndex] === $values[$direction == SortOrder::SORT_ASC ? 0 : 1] ? -1 : 1;
    }

    /**
     * Merge variables from different sources:
     *
     * Custom variables and default (stores configuration variables)
     *
     * @return array
     */
    public function getData()
    {
        $searchCriteria = $this->getSearchCriteria();
        $sortOrders = $searchCriteria->getSortOrders();

        $items = array_merge(
            $this->variableDataProvider->getDefaultVariables(),
            $this->variableDataProvider->getCustomVariables()
        );

        /** @var \Magento\Framework\Api\SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            if ($sortOrder->getField() && $sortOrder->getDirection()) {
                $items = $this->sortBy($items, $sortOrder->getField(), $sortOrder->getDirection());
            }
        }

        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $value = str_replace('%', '', $filter->getValue() ?? '');
                $filterField = $filter->getField();
                $items = array_values(
                    array_filter(
                        $items,
                        function ($item) use ($value, $filterField) {
                            return strpos(strtolower($item[$filterField] ?? ''), strtolower((string)$value)) !== false;
                        }
                    )
                );
            }
        }

        return [
            'items' => $items
        ];
    }
}
