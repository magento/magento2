<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Theme\Model\ResourceModel\Theme\Grid;

/**
 * Theme grid collection
 * @deprecated 101.0.0
 * @see \Magento\Theme\Ui\Component\Theme\DataProvider\SearchResult
 */
class Collection extends \Magento\Theme\Model\ResourceModel\Theme\Collection
{
    /**
     * Add area filter
     *
     * @return $this
     */
    protected function _initSelect()
    {
        \Magento\Theme\Model\ResourceModel\Theme\Collection::_initSelect();
        $this->filterVisibleThemes()->addAreaFilter(\Magento\Framework\App\Area::AREA_FRONTEND)->addParentTitle();
        return $this;
    }
}
