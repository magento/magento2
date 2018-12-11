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
     * @inheritdoc
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
     * @inheritdoc
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
     * @inheritdoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function getConfigData()
    {
        return $this->data['config'] ?? [];
    }

    /**
     * @inheritdoc
     */
    public function setConfigData($config)
    {
        $this->data['config'] = $config;

        return true;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getFieldMetaInfo($fieldSetName, $fieldName)
    {
        return [];
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getFieldSetMetaInfo($fieldSetName)
    {
        return [];
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getFieldsMetaInfo($fieldSetName)
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getPrimaryFieldName()
    {
        return 'release_notification';
    }

    /**
     * @inheritdoc
     */
    public function getRequestFieldName()
    {
        return 'release_notification';
    }

    /**
     * @inheritdoc
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
    }

    /**
     * @inheritdoc
     */
    public function addOrder($field, $direction)
    {
    }

    /**
     * @inheritdoc
     */
    public function setLimit($offset, $size)
    {
    }

    /**
     * @inheritdoc
     */
    public function getSearchCriteria()
    {
        return $this->searchCriteria;
    }

    /**
     * @inheritdoc
     */
    public function getSearchResult()
    {
        return $this->searchResult;
    }
}
