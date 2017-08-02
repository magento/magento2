<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\ResourceModel\Theme\Grid;

/**
 * Theme grid collection
 * @since 2.0.0
 */
class Collection extends \Magento\Theme\Model\ResourceModel\Theme\Collection
{
    /**
     * Add area filter
     *
     * @return \Magento\Theme\Model\ResourceModel\Theme\Collection
     * @since 2.0.0
     */
    protected function _initSelect()
    {
        \Magento\Theme\Model\ResourceModel\Theme\Collection::_initSelect();
        $this->filterVisibleThemes()->addAreaFilter(\Magento\Framework\App\Area::AREA_FRONTEND)->addParentTitle();
        return $this;
    }
}
