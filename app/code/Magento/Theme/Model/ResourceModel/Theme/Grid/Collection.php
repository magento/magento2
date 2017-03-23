<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\ResourceModel\Theme\Grid;

/**
 * Theme grid collection
 */
class Collection extends \Magento\Theme\Model\ResourceModel\Theme\Collection
{
    /**
     * Add area filter
     *
     * @return \Magento\Theme\Model\ResourceModel\Theme\Collection
     */
    protected function _initSelect()
    {
        \Magento\Theme\Model\ResourceModel\Theme\Collection::_initSelect();
        $this->filterVisibleThemes()->addAreaFilter(\Magento\Framework\App\Area::AREA_FRONTEND)->addParentTitle();
        return $this;
    }
}
