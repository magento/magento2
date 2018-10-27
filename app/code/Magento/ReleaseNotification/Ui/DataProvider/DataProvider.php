<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ReleaseNotification\Ui\DataProvider;

use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Data\Collection;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;

/**
 * Data Provider for the Release Notification UI component.
 */
class DataProvider implements DataProviderInterface
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
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigData()
    {
        return isset($this->data['config']) ? $this->data['config'] : [];
    }

    /**
     * {@inheritdoc}
     */
    public function setConfigData($config)
    {
        $this->data['config'] = $config;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getMeta()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getFieldMetaInfo($fieldSetName, $fieldName)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getFieldSetMetaInfo($fieldSetName)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getFieldsMetaInfo($fieldSetName)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getPrimaryFieldName()
    {
        return 'release_notification';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestFieldName()
    {
        return 'release_notification';
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->collection->toArray();
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addOrder($field, $direction)
    {
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setLimit($offset, $size)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchCriteria()
    {
        return $this->searchCriteria;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchResult()
    {
        return $this->searchResult;
    }
}
