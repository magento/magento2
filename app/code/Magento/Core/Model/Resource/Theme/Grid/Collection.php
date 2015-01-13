<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Core\Model\Resource\Theme\Grid;

/**
 * Theme grid collection
 */
class Collection extends \Magento\Core\Model\Resource\Theme\Collection
{
    /**
     * Add area filter
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->filterVisibleThemes()->addAreaFilter(\Magento\Framework\App\Area::AREA_FRONTEND)->addParentTitle();
        return $this;
    }
}
