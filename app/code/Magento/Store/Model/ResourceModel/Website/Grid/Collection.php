<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\ResourceModel\Website\Grid;

/**
 * Grid collection
 */
class Collection extends \Magento\Store\Model\ResourceModel\Website\Collection
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
