<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Variable\Ui\Component;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\Reporting;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * Class VariablesDataProvider
 * @package Magento\Variable\Ui\Component
 */
class VariablesDataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    /**
     * @var \Magento\Variable\Model\VariableFactory
     */
    private $collectionFactory;
    /**
     * @var \Magento\Email\Model\Source\Variables
     */
    private $storesVariables;

    /**
     * VariablesDataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param ReportingInterface $reporting
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RequestInterface $request
     * @param FilterBuilder $filterBuilder
     * @param \Magento\Variable\Model\ResourceModel\Variable\CollectionFactory $collectionFactory
     * @param \Magento\Email\Model\Source\Variables $storesVariables
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        \Magento\Variable\Model\ResourceModel\Variable\CollectionFactory $collectionFactory,
        \Magento\Email\Model\Source\Variables $storesVariables,
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
        $this->storesVariables = $storesVariables;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Prepare default variables
     *
     * @return array
     */
    private function getDefaultVariables()
    {
        $variables = [];
        foreach ($this->storesVariables->getData() as $variable) {
            $variables[] = [
                'code' => $variable['value'],
                'variable_name' => $variable['label'],
                'variable_type' => \Magento\Email\Model\Source\Variables::DEFAULT_VARIABLE_TYPE
            ];
        }

        return $variables;
    }

    /**
     * Prepare custom variables
     *
     * @return array
     */
    private function getCustomVariables()
    {
        $customVariables = $this->collectionFactory->create();

        $variables = [];
        foreach ($customVariables->getData() as $variable) {
            $variables[] = [
                'code' => $variable['code'],
                'variable_name' => $variable['name'],
                'variable_type' => 'custom'
            ];
        }

        return $variables;
    }

    /**
     * Merge variables from different sources:
     * custom variables and default (stores configuration variables)
     *
     * @return array
     */
    public function getData()
    {
        $searchCriteria = $this->getSearchCriteria();

        // sort items by variable_type
        $sortOrder = $searchCriteria->getSortOrders();
        if (!empty($sortOrder) && $sortOrder[0]->getDirection() == 'DESC') {
            $items = array_merge(
                $this->getCustomVariables(),
                $this->getDefaultVariables()
            );
        } else {
            $items = array_merge(
                $this->getDefaultVariables(),
                $this->getCustomVariables()
            );
        }

        // filter array by variable_name and search value
        $filterGroups = $searchCriteria->getFilterGroups();
        if(!empty($filterGroups)) {
            $filters = $filterGroups[0]->getFilters();
            if(!empty($filters)) {
                $value = str_replace('%', '', $filters[0]->getValue());
                $items = array_values(array_filter($items, function ($item) use($value) {
                    return strpos(strtolower($item['variable_name']), strtolower($value)) !== false;
                }));
            }
        }

        return [
            'items' => $items
        ];
    }
}
