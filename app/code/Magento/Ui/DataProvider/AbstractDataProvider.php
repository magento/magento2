<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\DataProvider;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;

/**
 * @api
 * @since 2.0.0
 */
abstract class AbstractDataProvider implements DataProviderInterface
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
     * @var AbstractCollection
     * @since 2.0.0
     */
    protected $collection;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->name = $name;
        $this->primaryFieldName = $primaryFieldName;
        $this->requestFieldName = $requestFieldName;
        $this->meta = $meta;
        $this->data = $data;
    }

    /**
     * @return AbstractCollection
     * @since 2.0.0
     */
    public function getCollection()
    {
        return $this->collection;
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
        $this->getCollection()->addFieldToFilter(
            $filter->getField(),
            [$filter->getConditionType() => $filter->getValue()]
        );
    }

    /**
     * Returns search criteria
     *
     * @return null
     * @since 2.0.0
     */
    public function getSearchCriteria()
    {
        //TODO: Technical dept, should be implemented as part of SearchAPI support for Catalog Grids
        return null;
    }

    /**
     * Returns SearchResult
     *
     * @return \Magento\Framework\Api\Search\SearchResultInterface
     * @since 2.0.0
     */
    public function getSearchResult()
    {
        //TODO: Technical dept, should be implemented as part of SearchAPI support for Catalog Grids
        return $this->getCollection();
    }

    /**
     * Add field to select
     *
     * @param string|array $field
     * @param string|null $alias
     * @return void
     * @since 2.0.0
     */
    public function addField($field, $alias = null)
    {
        $this->getCollection()->addFieldToSelect($field, $alias);
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
        $this->getCollection()->addOrder($field, $direction);
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
        $this->getCollection()->setPageSize($size);
        $this->getCollection()->setCurPage($offset);
    }

    /**
     * Removes field from select
     *
     * @param string|null $field
     * @param bool $isAlias Alias identifier
     * @return void
     * @since 2.0.0
     */
    public function removeField($field, $isAlias = false)
    {
        $this->getCollection()->removeFieldFromSelect($field, $isAlias);
    }

    /**
     * Removes all fields from select
     *
     * @return void
     * @since 2.0.0
     */
    public function removeAllFields()
    {
        $this->getCollection()->removeAllFieldsFromSelect();
    }

    /**
     * Get data
     *
     * @return array
     * @since 2.0.0
     */
    public function getData()
    {
        return $this->getCollection()->toArray();
    }

    /**
     * Retrieve count of loaded items
     *
     * @return int
     * @since 2.0.0
     */
    public function count()
    {
        return $this->getCollection()->count();
    }

    /**
     * Get config data
     *
     * @return mixed
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
     * Retrieve all ids from collection
     *
     * @return int[]
     * @since 2.2.0
     */
    public function getAllIds()
    {
        return  $this->collection->getAllIds();
    }
}
