<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
