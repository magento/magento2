<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Resource\Theme\Grid;

/**
 * Theme grid collection
 */
class Collection extends \Magento\Theme\Model\Resource\Theme\Collection
{
    /**
     * Add area filter
     *
     * @return \Magento\Theme\Model\Resource\Theme\Collection
     */
    protected function _initSelect()
    {
        \Magento\Theme\Model\Resource\Theme\Collection::_initSelect();
        $this->filterVisibleThemes()->addAreaFilter(\Magento\Framework\App\Area::AREA_FRONTEND)->addParentTitle();
        return $this;
    }
}
