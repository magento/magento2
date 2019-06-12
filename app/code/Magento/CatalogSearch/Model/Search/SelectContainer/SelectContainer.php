<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Search\SelectContainer;

use Magento\Framework\DB\Select;
use Magento\Framework\Search\Request\FilterInterface;

/**
 * This class is a container for all data that is required for creating select query by search request
 *
 * @deprecated
 * @see \Magento\ElasticSearch
 */
class SelectContainer
{
    /**
     * @var array FilterInterface[]
     */
    private $nonCustomAttributesFilters;

    /**
     * @var array FilterInterface[]
     */
    private $customAttributesFilters;

    /**
     * @var FilterInterface
     */
    private $visibilityFilter;

    /**
     * @var bool
     */
    private $isFullTextSearchRequired;

    /**
     * @var bool
     */
    private $isShowOutOfStockEnabled;

    /**
     * @var Select
     */
    private $select;

    /**
     * @var string
     */
    private $usedIndex;

    /**
     * @var array
     */
    private $dimensions;

    /**
     * @param Select $select
     * @param array $nonCustomAttributesFilters
     * @param array $customAttributesFilters
     * @param array $dimensions
     * @param bool $isFullTextSearchRequired
     * @param bool $isShowOutOfStockEnabled
     * @param string $usedIndex
     * @param FilterInterface|null $visibilityFilter
     */
    public function __construct(
        Select $select,
        array $nonCustomAttributesFilters,
        array $customAttributesFilters,
        array $dimensions,
        bool $isFullTextSearchRequired,
        bool $isShowOutOfStockEnabled,
        $usedIndex,
        FilterInterface $visibilityFilter = null
    ) {
        $this->nonCustomAttributesFilters = $nonCustomAttributesFilters;
        $this->customAttributesFilters = $customAttributesFilters;
        $this->visibilityFilter = $visibilityFilter;
        $this->isFullTextSearchRequired = $isFullTextSearchRequired;
        $this->isShowOutOfStockEnabled = $isShowOutOfStockEnabled;
        $this->select = $select;
        $this->usedIndex = $usedIndex;
        $this->dimensions = $dimensions;
    }

    /**
     * @return array
     */
    public function getNonCustomAttributesFilters()
    {
        return $this->nonCustomAttributesFilters;
    }

    /**
     * @return array
     */
    public function getCustomAttributesFilters()
    {
        return $this->customAttributesFilters;
    }

    /**
     * @return bool
     */
    public function hasCustomAttributesFilters()
    {
        return count($this->customAttributesFilters) > 0;
    }

    /**
     * @return bool
     */
    public function hasVisibilityFilter()
    {
        return $this->visibilityFilter !== null;
    }

    /**
     * Returns a null or copy of FilterInterface
     * This is done to ensure that SelectContainer is immutable
     *
     * @return FilterInterface
     */
    public function getVisibilityFilter()
    {
        return $this->visibilityFilter === null ? null : clone $this->visibilityFilter;
    }

    /**
     * @return bool
     */
    public function isFullTextSearchRequired()
    {
        return $this->isFullTextSearchRequired;
    }

    /**
     * @return bool
     */
    public function isShowOutOfStockEnabled()
    {
        return $this->isShowOutOfStockEnabled;
    }

    /**
     * @return string
     */
    public function getUsedIndex()
    {
        return $this->usedIndex;
    }

    /**
     * @return array
     */
    public function getDimensions()
    {
        return $this->dimensions;
    }

    /**
     * Returns a copy of Select
     * This is done to ensure that SelectContainer is immutable
     *
     * @return Select
     */
    public function getSelect()
    {
        return clone $this->select;
    }

    /**
     * Returns new instance of SelectContainer on update
     * This is done to ensure that SelectContainer is immutable
     *
     * @param Select $select
     * @return SelectContainer
     */
    public function updateSelect(Select $select)
    {
        $data = [
            clone $select,
            $this->nonCustomAttributesFilters,
            $this->customAttributesFilters,
            $this->dimensions,
            $this->isFullTextSearchRequired,
            $this->isShowOutOfStockEnabled,
            $this->usedIndex

        ];

        if ($this->visibilityFilter !== null) {
            $data[] = clone $this->visibilityFilter;
        }

        return new self(...$data);
    }
}
