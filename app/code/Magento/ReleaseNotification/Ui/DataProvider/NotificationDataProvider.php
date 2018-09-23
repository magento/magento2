<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ReleaseNotification\Ui\DataProvider;

use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

/**
 * Data Provider for the Release Notifications UI component.
 */
class NotificationDataProvider implements DataProviderInterface
{
    /**
     * @var PoolInterface
     */
    private $pool;

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
     * Provider configuration meta.
     *
     * @var array
     */
    private $meta;

    /**
     * @param string $name
     * @param SearchResultInterface $searchResult
     * @param SearchCriteriaInterface $searchCriteria
     * @param PoolInterface $pool
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        SearchResultInterface $searchResult,
        SearchCriteriaInterface $searchCriteria,
        PoolInterface $pool,
        array $meta = [],
        array $data = []
    ) {
        $this->name = $name;
        $this->searchResult = $searchResult;
        $this->searchCriteria = $searchCriteria;
        $this->pool = $pool;
        $this->meta = $meta;
        $this->data = $data;
    }

    /**
     * Get data
     *
     * @return mixed
     */
    public function getData()
    {
        /** @var ModifierInterface $modifier */
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $this->data = $modifier->modifyData($this->data);
        }

        return $this->data;
    }

    /**
     * Get Meta
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getMeta()
    {
        /** @var ModifierInterface $modifier */
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $this->meta = $modifier->modifyMeta($this->meta);
        }
        return $this->meta;
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
        return $this->data['config'] ?? [];
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
     * Get Field Meta Info
     *
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
     * Get Field Set Meta Info
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
     * Get Fields Meta Info
     *
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
     * Get Primary Field Name
     *
     * @return string
     */
    public function getPrimaryFieldName()
    {
        return 'release_notification';
    }

    /**
     * Get Request Field Name
     *
     * @return string
     */
    public function getRequestFieldName()
    {
        return 'release_notification';
    }

    /**
     * Add Filter
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
     * Add Order
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
     * Set Limit
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
     * Get Search Criteria
     *
     * @return SearchCriteriaInterface
     */
    public function getSearchCriteria()
    {
        return $this->searchCriteria;
    }

    /**
     * Get Search Result
     *
     * @return SearchResultInterface
     */
    public function getSearchResult()
    {
        return $this->searchResult;
    }
}
