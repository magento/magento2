<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Store\Model\Resource\Website\Grid;

/**
 * Grid collection
 */
class Collection extends \Magento\Store\Model\Resource\Website\Collection
{
    /**
     * Join website and store names
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->joinGroupAndStore();
        return $this;
    }
}
