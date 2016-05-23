<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data;

/**
 * Class SearchResultProcessor
 */
class SearchResultProcessor extends AbstractDataObject implements SearchResultProcessorInterface
{
    /**
     * Data Interface name
     *
     * @var string
     */
    protected $dataInterface = 'Magento\Framework\DataObject';

    /**
     * @var AbstractSearchResult
     */
    protected $searchResult;

    /**
     * @param AbstractSearchResult $searchResult
     */
    public function __construct(AbstractSearchResult $searchResult)
    {
        $this->searchResult = $searchResult;
    }

    /**
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->searchResult->getSearchCriteria()->getLimit()[0];
    }

    /**
     * @return int
     */
    public function getPageSize()
    {
        return $this->searchResult->getSearchCriteria()->getLimit()[1];
    }

    /**
     * @return \Magento\Framework\DataObject|mixed
     */
    public function getFirstItem()
    {
        return current($this->searchResult->getItems());
    }

    /**
     * @return \Magento\Framework\DataObject|mixed
     */
    public function getLastItem()
    {
        $items = $this->searchResult->getItems();
        return end($items);
    }

    /**
     * @return array
     */
    public function getAllIds()
    {
        $ids = [];
        foreach ($this->searchResult->getItems() as $item) {
            $ids[] = $this->searchResult->getItemId($item);
        }
        return $ids;
    }

    /**
     * @param int $id
     * @return \Magento\Framework\DataObject|null
     */
    public function getItemById($id)
    {
        $items = $this->searchResult->getItems();
        if (isset($items[$id])) {
            return $items[$id];
        }
        return null;
    }

    /**
     * @param string $colName
     * @return array
     */
    public function getColumnValues($colName)
    {
        $col = [];
        foreach ($this->searchResult->getItems() as $item) {
            $col[] = $item->getData($colName);
        }
        return $col;
    }

    /**
     * @param string $column
     * @param mixed $value
     * @return array
     */
    public function getItemsByColumnValue($column, $value)
    {
        $res = [];
        foreach ($this->searchResult->getItems() as $item) {
            if ($item->getData($column) == $value) {
                $res[] = $item;
            }
        }
        return $res;
    }

    /**
     * @param string $column
     * @param mixed $value
     * @return \Magento\Framework\DataObject|null
     */
    public function getItemByColumnValue($column, $value)
    {
        foreach ($this->searchResult->getItems() as $item) {
            if ($item->getData($column) == $value) {
                return $item;
            }
        }
        return null;
    }

    /**
     * @param string $callback
     * @param array $args
     * @return array
     */
    public function walk($callback, array $args = [])
    {
        $results = [];
        $useItemCallback = is_string($callback) && strpos($callback, '::') === false;
        foreach ($this->searchResult->getItems() as $id => $item) {
            if ($useItemCallback) {
                $cb = [$item, $callback];
            } else {
                $cb = $callback;
                array_unshift($args, $item);
            }
            $results[$id] = call_user_func_array($cb, $args);
        }
        return $results;
    }

    /**
     * @return string
     */
    public function toXml()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <collection>
           <totalRecords>' .
            $this->searchResult->getSize() .
            '</totalRecords>
           <items>';
        foreach ($this->searchResult->getItems() as $item) {
            $xml .= $item->toXml();
        }
        $xml .= '</items>
        </collection>';
        return $xml;
    }

    /**
     * @param array $arrRequiredFields
     * @return array
     */
    public function toArray($arrRequiredFields = [])
    {
        $array = [];
        $array['search_criteria'] = $this->searchResult->getSearchCriteria();
        $array['total_count'] = $this->searchResult->getTotalCount();
        foreach ($this->searchResult->getItems() as $item) {
            $array['items'][] = $item->toArray($arrRequiredFields);
        }
        return $array;
    }

    /**
     * @param null $valueField
     * @param null $labelField
     * @param array $additional
     * @return array
     */
    public function toOptionArray($valueField = null, $labelField = null, $additional = [])
    {
        if ($valueField === null) {
            $valueField = $this->searchResult->getIdFieldName();
        }
        if ($labelField === null) {
            $labelField = 'name';
        }
        $result = [];
        $additional['value'] = $valueField;
        $additional['label'] = $labelField;
        foreach ($this->searchResult->getItems() as $item) {
            $data = [];
            foreach ($additional as $code => $field) {
                $data[$code] = $item->getData($field);
            }
            $result[] = $data;
        }
        return $result;
    }

    /**
     * @param string $valueField
     * @param string $labelField
     * @return array
     */
    public function toOptionHash($valueField, $labelField)
    {
        $res = [];
        foreach ($this->searchResult->getItems() as $item) {
            $res[$item->getData($valueField)] = $item->getData($labelField);
        }
        return $res;
    }

    /**
     * @return string
     */
    protected function getDataInterfaceName()
    {
        return $this->dataInterface;
    }
}
