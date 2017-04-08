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
 */
interface DataProviderInterface
{
    /**
     * Get Data Provider name
     *
     * @return string
     */
    public function getName();

    /**
     * Get config data
     *
     * @return mixed
     */
    public function getConfigData();

    /**
     * Set config data
     *
     * @param mixed $config
     * @return void
     */
    public function setConfigData($config);

    /**
     * @return array
     */
    public function getMeta();

    /**
     * @param string $fieldSetName
     * @param string $fieldName
     * @return array
     */
    public function getFieldMetaInfo($fieldSetName, $fieldName);

    /**
     * Get field Set meta info
     *
     * @param string $fieldSetName
     * @return array
     */
    public function getFieldSetMetaInfo($fieldSetName);

    /**
     * @param string $fieldSetName
     * @return array
     */
    public function getFieldsMetaInfo($fieldSetName);

    /**
     * Get primary field name
     *
     * @return string
     */
    public function getPrimaryFieldName();

    /**
     * Get field name in request
     *
     * @return string
     */
    public function getRequestFieldName();

    /**
     * Get data
     *
     * @return mixed
     */
    public function getData();

    /**
     * Add field filter to collection
     *
     * @param \Magento\Framework\Api\Filter $filter
     * @return mixed
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter);

    /**
     * Add ORDER BY to the end or to the beginning
     *
     * @param string $field
     * @param string $direction
     * @return void
     */
    public function addOrder($field, $direction);

    /**
     * Set Query limit
     *
     * @param int $offset
     * @param int $size
     * @return void
     */
    public function setLimit($offset, $size);

    /**
     * Returns search criteria
     *
     * @return \Magento\Framework\Api\Search\SearchCriteriaInterface
     */
    public function getSearchCriteria();

    /**
     * @return \Magento\Framework\Api\Search\SearchResultInterface
     */
    public function getSearchResult();
}
