<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent\DataProvider;

/**
 * Interface DataProviderInterface
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
     * Add field to select
     *
     * @param string|array $field
     * @param string|null $alias
     * @return void
     */
    public function addField($field, $alias = null);

    /**
     * Add field filter to collection
     *
     * @param string|int|array|null $condition
     * @param null|string|array $field
     * @param string $type
     * @return mixed
     */
    public function addFilter($condition, $field = null, $type = 'regular');

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
     * Removes field from select
     *
     * @param string|null $field
     * @param bool $isAlias Alias identifier
     * @return void
     */
    public function removeField($field, $isAlias = false);

    /**
     * Removes all fields from select
     *
     * @return void
     */
    public function removeAllFields();

    /**
     * Retrieve count of loaded items
     *
     * @return int
     */
    public function count();
}
