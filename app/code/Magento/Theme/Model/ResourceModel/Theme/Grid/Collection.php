<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\ResourceModel\Theme\Grid;

/**
 * Theme grid collection
 * @deprecated
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
