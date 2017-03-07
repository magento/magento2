<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Ui\DataProvider;

use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Data\Collection;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;

/**
 * Class which serves as stub for degenerated UI component.
 */
class DummyDataProvider implements DataProviderInterface
{
    /**
     * Search result object.
     *
     * @var SearchResultInterface
     */
    private $searchResult;

    /**
     * Search criteria object.
     *
     * @var SearchCriteriaInterface
     */
    private $searchCriteria;

    /**
     * Data collection.
     *
     * @var Collection
     */
    private $collection;

    /**
     * Own name of this provider.
     *
     * @var string
     */
    private $name;

    /**
     * Provider configuration data.
     *
     * @var array
     */
    private $data;

    /**
     * @param string $name
     * @param SearchResultInterface $searchResult
     * @param SearchCriteriaInterface $searchCriteria
     * @param Collection $collection
     * @param array $data
     */
    public function __construct(
        $name,
        SearchResultInterface $searchResult,
        SearchCriteriaInterface $searchCriteria,
        Collection $collection,
        array $data = []
    ) {
        $this->name = $name;
        $this->searchResult = $searchResult;
        $this->searchCriteria = $searchCriteria;
        $this->collection = $collection;
        $this->data = $data;
    }

    /**
     * Get Data Provider name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get config data
     *
     * @return mixed
     */
    public function getConfigData()
    {
        return isset($this->data['config']) ? $this->data['config'] : [];
    }

    /**
     * Set config data
     *
     * @param mixed $config
     *
     * @return bool
     */
    public function setConfigData($config)
    {
        $this->data['config'] = $config;

        return true;
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        return [];
    }

    /**
     * @param string $fieldSetName
     * @param string $fieldName
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getFieldMetaInfo($fieldSetName, $fieldName)
    {
        return [];
    }

    /**
     * Get field set meta info
     *
     * @param string $fieldSetName
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getFieldSetMetaInfo($fieldSetName)
    {
        return [];
    }

    /**
     * @param string $fieldSetName
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getFieldsMetaInfo($fieldSetName)
    {
        return [];
    }

    /**
     * Get primary field name
     *
     * @return string
     */
    public function getPrimaryFieldName()
    {
        return '';
    }

    /**
     * Get field name in request
     *
     * @return string
     */
    public function getRequestFieldName()
    {
        return '';
    }

    /**
     * Get data
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->collection->toArray();
    }

    /**
     * Add field filter to collection
     *
     * @param \Magento\Framework\Api\Filter $filter
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
    }

    /**
     * Add ORDER BY to the end or to the beginning
     *
     * @param string $field
     * @param string $direction
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addOrder($field, $direction)
    {
    }

    /**
     * Set Query limit
     *
     * @param int $offset
     * @param int $size
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setLimit($offset, $size)
    {
    }

    /**
     * Returns search criteria
     *
     * @return SearchCriteriaInterface
     */
    public function getSearchCriteria()
    {
        return $this->searchCriteria;
    }

    /**
     * @return SearchResultInterface
     */
    public function getSearchResult()
    {
        return $this->searchResult;
    }
}
