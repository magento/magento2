<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\ResourceModel\Theme\Grid;

use Magento\Framework\App\Area;
use Magento\Theme\Model\ResourceModel\Theme\Collection as ThemeCollection;

/**
 * Theme grid collection
 * @deprecated 101.0.0
 * @see \Magento\Theme\Ui\Component\Theme\DataProvider\SearchResult
 */
class Collection extends ThemeCollection
{
    /**
     * Add area filter
     *
     * @return $this
     */
    protected function _initSelect()
    {
        ThemeCollection::_initSelect();
        $this->filterVisibleThemes()->addAreaFilter(Area::AREA_FRONTEND)->addParentTitle();
        return $this;
    }
}
