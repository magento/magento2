<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
