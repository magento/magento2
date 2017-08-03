<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent\DataProvider;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteria;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\RequestInterface;

/**
 * Class DataProvider
 * @since 2.0.0
 */
class DataProvider implements DataProviderInterface
{
    /**
     * Data Provider name
     *
     * @var string
     * @since 2.0.0
     */
    protected $name;

    /**
     * Data Provider Primary Identifier name
     *
     * @var string
     * @since 2.0.0
     */
    protected $primaryFieldName;

    /**
     * Data Provider Request Parameter Identifier name
     *
     * @var string
     * @since 2.0.0
     */
    protected $requestFieldName;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $meta = [];

    /**
     * Provider configuration data
     *
     * @var array
     * @since 2.0.0
     */
    protected $data = [];

    /**
     * @var ReportingInterface
     * @since 2.0.0
     */
    protected $reporting;

    /**
     * @var FilterBuilder
     * @since 2.0.0
     */
    protected $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     * @since 2.0.0
     */
    protected $searchCriteriaBuilder;

    /**
     * @var RequestInterface
     * @since 2.0.0
     */
    protected $request;

    /**
     * @var SearchCriteria
     * @since 2.0.0
     */
    protected $searchCriteria;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param ReportingInterface $reporting
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RequestInterface $request
     * @param FilterBuilder $filterBuilder
     * @param array $meta
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        array $meta = [],
        array $data = []
    ) {
        $this->request = $request;
        $this->filterBuilder = $filterBuilder;
        $this->name = $name;
        $this->primaryFieldName = $primaryFieldName;
        $this->requestFieldName = $requestFieldName;
        $this->reporting = $reporting;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->meta = $meta;
        $this->data = $data;
        $this->prepareUpdateUrl();
    }

    /**
     * @return void
     * @since 2.0.0
     */
    protected function prepareUpdateUrl()
    {
        if (!isset($this->data['config']['filter_url_params'])) {
            return;
        }
        foreach ($this->data['config']['filter_url_params'] as $paramName => $paramValue) {
            if ('*' == $paramValue) {
                $paramValue = $this->request->getParam($paramName);
            }
            if ($paramValue) {
                $this->data['config']['update_url'] = sprintf(
                    '%s%s/%s/',
                    $this->data['config']['update_url'],
                    $paramName,
                    $paramValue
                );
                $this->addFilter(
                    $this->filterBuilder->setField($paramName)->setValue($paramValue)->setConditionType('eq')->create()
                );
            }
        }
    }

    /**
     * Get Data Provider name
     *
     * @return string
     * @since 2.0.0
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get primary field name
     *
     * @return string
     * @since 2.0.0
     */
    public function getPrimaryFieldName()
    {
        return $this->primaryFieldName;
    }

    /**
     * Get field name in request
     *
     * @return string
     * @since 2.0.0
     */
    public function getRequestFieldName()
    {
        return $this->requestFieldName;
    }

    /**
     * @return array
     * @since 2.0.0
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * Get field Set meta info
     *
     * @param string $fieldSetName
     * @return array
     * @since 2.0.0
     */
    public function getFieldSetMetaInfo($fieldSetName)
    {
        return isset($this->meta[$fieldSetName]) ? $this->meta[$fieldSetName] : [];
    }

    /**
     * @param string $fieldSetName
     * @return array
     * @since 2.0.0
     */
    public function getFieldsMetaInfo($fieldSetName)
    {
        return isset($this->meta[$fieldSetName]['children']) ? $this->meta[$fieldSetName]['children'] : [];
    }

    /**
     * @param string $fieldSetName
     * @param string $fieldName
     * @return array
     * @since 2.0.0
     */
    public function getFieldMetaInfo($fieldSetName, $fieldName)
    {
        return isset($this->meta[$fieldSetName]['children'][$fieldName])
            ? $this->meta[$fieldSetName]['children'][$fieldName]
            : [];
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        $this->searchCriteriaBuilder->addFilter($filter);
    }

    /**
     * self::setOrder() alias
     *
     * @param string $field
     * @param string $direction
     * @return void
     * @since 2.0.0
     */
    public function addOrder($field, $direction)
    {
        $this->searchCriteriaBuilder->addSortOrder($field, $direction);
    }

    /**
     * Set Query limit
     *
     * @param int $offset
     * @param int $size
     * @return void
     * @since 2.0.0
     */
    public function setLimit($offset, $size)
    {
        $this->searchCriteriaBuilder->setPageSize($size);
        $this->searchCriteriaBuilder->setCurrentPage($offset);
    }

    /**
     * @param SearchResultInterface $searchResult
     * @return array
     * @since 2.0.0
     */
    protected function searchResultToOutput(SearchResultInterface $searchResult)
    {
        $arrItems = [];

        $arrItems['items'] = [];
        foreach ($searchResult->getItems() as $item) {
            $itemData = [];
            foreach ($item->getCustomAttributes() as $attribute) {
                $itemData[$attribute->getAttributeCode()] = $attribute->getValue();
            }
            $arrItems['items'][] = $itemData;
        }

        $arrItems['totalRecords'] = $searchResult->getTotalCount();

        return $arrItems;
    }

    /**
     * Returns search criteria
     *
     * @return \Magento\Framework\Api\Search\SearchCriteria
     * @since 2.0.0
     */
    public function getSearchCriteria()
    {
        if (!$this->searchCriteria) {
            $this->searchCriteria = $this->searchCriteriaBuilder->create();
            $this->searchCriteria->setRequestName($this->name);
        }
        return $this->searchCriteria;
    }

    /**
     * Get data
     *
     * @return array
     * @since 2.0.0
     */
    public function getData()
    {
        return $this->searchResultToOutput($this->getSearchResult());
    }

    /**
     * Get config data
     *
     * @return array
     * @since 2.0.0
     */
    public function getConfigData()
    {
        return isset($this->data['config']) ? $this->data['config'] : [];
    }

    /**
     * Set data
     *
     * @param mixed $config
     * @return void
     * @since 2.0.0
     */
    public function setConfigData($config)
    {
        $this->data['config'] = $config;
    }

    /**
     * Returns Search result
     *
     * @return SearchResultInterface
     * @since 2.0.0
     */
    public function getSearchResult()
    {
        return $this->reporting->search($this->getSearchCriteria());
    }
}
