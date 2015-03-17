<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model\Page;

use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;

use \Magento\Cms\Model\Resource\Page\Grid\Collection;
use Magento\Cms\Model\Resource\Page\Grid\CollectionFactory;

/**
 * Class DataProvider
 */
class DataProvider implements DataProviderInterface
{
    /**
     * @var string
     */
    protected $primaryFieldName;

    /**
     * @var string
     */
    protected $requestFieldName;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var array
     */
    protected $meta = [];

    /**
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param array $meta
     */
    public function __construct(
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = []
    ) {
        $this->primaryFieldName = $primaryFieldName;
        $this->requestFieldName = $requestFieldName;

        $this->collection = $collectionFactory->create();
        $this->collection->setFirstStoreFlag(true);
        $this->meta = $meta;
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        return $this->collection->toArray();
    }

    /**
     * @param string $fieldSetName
     * @param string $fieldName
     * @return array
     */
    public function getFieldMetaInfo($fieldSetName, $fieldName)
    {
        return isset($this->meta[$fieldSetName][$fieldName]) ? $this->meta[$fieldSetName][$fieldName] : [];
    }

    /**
     * Get field name in request
     *
     * @return string
     */
    public function getRequestFieldName()
    {
        return $this->requestFieldName;
    }

    /**
     * Get primary field name
     *
     * @return string
     */
    public function getPrimaryFieldName()
    {
        return $this->primaryFieldName;
    }

    /**
     * @inheritdoc
     */
    public function addFilter($field, $condition = null)
    {
        $this->collection->addFieldToFilter($field, $condition);
    }

    /**
     * Add field to select
     *
     * @param string|array $field
     * @param string|null $alias
     * @return void
     */
    public function addField($field, $alias = null)
    {
        $this->collection->addFieldToSelect($field, $alias);
    }

    /**
     * self::setOrder() alias
     *
     * @param string $field
     * @param string $direction
     * @return void
     */
    public function addOrder($field, $direction)
    {
        $this->collection->addOrder($field, $direction);
    }

    /**
     * Set Query limit
     *
     * @param int $offset
     * @param int $size
     * @return void
     */
    public function setLimit($offset, $size)
    {
        $this->collection->setPageSize($size);
        $this->collection->setCurPage($offset);
    }

    /**
     * Removes field from select
     *
     * @param string|null $field
     * @param bool $isAlias Alias identifier
     * @return void
     */
    public function removeField($field, $isAlias = false)
    {
        $this->collection->removeFieldFromSelect($field, $isAlias);
    }

    /**
     * Removes all fields from select
     *
     * @return void
     */
    public function removeAllFields()
    {
        $this->collection->removeAllFieldsFromSelect();
    }
}
