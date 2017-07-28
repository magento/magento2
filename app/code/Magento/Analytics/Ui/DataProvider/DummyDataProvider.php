<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Ui\DataProvider;

use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Data\Collection;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;

/**
 * Class which serves as stub for degenerated UI component.
 * @since 2.2.0
 */
class DummyDataProvider implements DataProviderInterface
{
    /**
     * Search result object.
     *
     * @var SearchResultInterface
     * @since 2.2.0
     */
    private $searchResult;

    /**
     * Search criteria object.
     *
     * @var SearchCriteriaInterface
     * @since 2.2.0
     */
    private $searchCriteria;

    /**
     * Data collection.
     *
     * @var Collection
     * @since 2.2.0
     */
    private $collection;

    /**
     * Own name of this provider.
     *
     * @var string
     * @since 2.2.0
     */
    private $name;

    /**
     * Provider configuration data.
     *
     * @var array
     * @since 2.2.0
     */
    private $data;

    /**
     * @param string $name
     * @param SearchResultInterface $searchResult
     * @param SearchCriteriaInterface $searchCriteria
     * @param Collection $collection
     * @param array $data
     * @since 2.2.0
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
     * @since 2.2.0
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get config data
     *
     * @return mixed
     * @since 2.2.0
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
     * @since 2.2.0
     */
    public function setConfigData($config)
    {
        $this->data['config'] = $config;

        return true;
    }

    /**
     * @return array
     * @since 2.2.0
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
     * @since 2.2.0
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
     * @since 2.2.0
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
     * @since 2.2.0
     */
    public function getFieldsMetaInfo($fieldSetName)
    {
        return [];
    }

    /**
     * Get primary field name
     *
     * @return string
     * @since 2.2.0
     */
    public function getPrimaryFieldName()
    {
        return '';
    }

    /**
     * Get field name in request
     *
     * @return string
     * @since 2.2.0
     */
    public function getRequestFieldName()
    {
        return '';
    }

    /**
     * Get data
     *
     * @return mixed
     * @since 2.2.0
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
     * @since 2.2.0
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
     * @since 2.2.0
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
     * @since 2.2.0
     */
    public function setLimit($offset, $size)
    {
    }

    /**
     * Returns search criteria
     *
     * @return SearchCriteriaInterface
     * @since 2.2.0
     */
    public function getSearchCriteria()
    {
        return $this->searchCriteria;
    }

    /**
     * @return SearchResultInterface
     * @since 2.2.0
     */
    public function getSearchResult()
    {
        return $this->searchResult;
    }
}
