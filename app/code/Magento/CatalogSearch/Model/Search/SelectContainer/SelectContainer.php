<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Search\SelectContainer;

use Magento\Framework\DB\Select;
use Magento\Framework\Search\Request\FilterInterface;

/**
 * Class SelectContainer
 * This class is a container for all data that is required for creating select query by search request
 * @since 2.2.0
 */
class SelectContainer
{
    /**
     * @var array FilterInterface[]
     * @since 2.2.0
     */
    private $nonCustomAttributesFilters;

    /**
     * @var array FilterInterface[]
     * @since 2.2.0
     */
    private $customAttributesFilters;

    /**
     * @var FilterInterface
     * @since 2.2.0
     */
    private $visibilityFilter;

    /**
     * @var bool
     * @since 2.2.0
     */
    private $isFullTextSearchRequired;

    /**
     * @var bool
     * @since 2.2.0
     */
    private $isShowOutOfStockEnabled;

    /**
     * @var Select
     * @since 2.2.0
     */
    private $select;

    /**
     * @var string
     * @since 2.2.0
     */
    private $usedIndex;

    /**
     * @var array
     * @since 2.2.0
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
     * @since 2.2.0
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
     * @since 2.2.0
     */
    public function getNonCustomAttributesFilters()
    {
        return $this->nonCustomAttributesFilters;
    }

    /**
     * @return array
     * @since 2.2.0
     */
    public function getCustomAttributesFilters()
    {
        return $this->customAttributesFilters;
    }

    /**
     * @return bool
     * @since 2.2.0
     */
    public function hasCustomAttributesFilters()
    {
        return count($this->customAttributesFilters) > 0;
    }

    /**
     * @return bool
     * @since 2.2.0
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
     * @since 2.2.0
     */
    public function getVisibilityFilter()
    {
        return $this->visibilityFilter === null ? null : clone $this->visibilityFilter;
    }

    /**
     * @return bool
     * @since 2.2.0
     */
    public function isFullTextSearchRequired()
    {
        return $this->isFullTextSearchRequired;
    }

    /**
     * @return bool
     * @since 2.2.0
     */
    public function isShowOutOfStockEnabled()
    {
        return $this->isShowOutOfStockEnabled;
    }

    /**
     * @return string
     * @since 2.2.0
     */
    public function getUsedIndex()
    {
        return $this->usedIndex;
    }

    /**
     * @return array
     * @since 2.2.0
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
     * @since 2.2.0
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
     * @since 2.2.0
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
