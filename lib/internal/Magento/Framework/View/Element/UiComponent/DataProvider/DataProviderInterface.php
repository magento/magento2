<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent\DataProvider;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Interface DataProviderInterface
 *
 * @api
 * @since 2.0.0
 */
interface DataProviderInterface
{
    /**
     * Get Data Provider name
     *
     * @return string
     * @since 2.0.0
     */
    public function getName();

    /**
     * Get config data
     *
     * @return mixed
     * @since 2.0.0
     */
    public function getConfigData();

    /**
     * Set config data
     *
     * @param mixed $config
     * @return void
     * @since 2.0.0
     */
    public function setConfigData($config);

    /**
     * @return array
     * @since 2.0.0
     */
    public function getMeta();

    /**
     * @param string $fieldSetName
     * @param string $fieldName
     * @return array
     * @since 2.0.0
     */
    public function getFieldMetaInfo($fieldSetName, $fieldName);

    /**
     * Get field Set meta info
     *
     * @param string $fieldSetName
     * @return array
     * @since 2.0.0
     */
    public function getFieldSetMetaInfo($fieldSetName);

    /**
     * @param string $fieldSetName
     * @return array
     * @since 2.0.0
     */
    public function getFieldsMetaInfo($fieldSetName);

    /**
     * Get primary field name
     *
     * @return string
     * @since 2.0.0
     */
    public function getPrimaryFieldName();

    /**
     * Get field name in request
     *
     * @return string
     * @since 2.0.0
     */
    public function getRequestFieldName();

    /**
     * Get data
     *
     * @return mixed
     * @since 2.0.0
     */
    public function getData();

    /**
     * Add field filter to collection
     *
     * @param \Magento\Framework\Api\Filter $filter
     * @return mixed
     * @since 2.0.0
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter);

    /**
     * Add ORDER BY to the end or to the beginning
     *
     * @param string $field
     * @param string $direction
     * @return void
     * @since 2.0.0
     */
    public function addOrder($field, $direction);

    /**
     * Set Query limit
     *
     * @param int $offset
     * @param int $size
     * @return void
     * @since 2.0.0
     */
    public function setLimit($offset, $size);

    /**
     * Returns search criteria
     *
     * @return \Magento\Framework\Api\Search\SearchCriteriaInterface
     * @since 2.0.0
     */
    public function getSearchCriteria();

    /**
     * @return \Magento\Framework\Api\Search\SearchResultInterface
     * @since 2.0.0
     */
    public function getSearchResult();
}
