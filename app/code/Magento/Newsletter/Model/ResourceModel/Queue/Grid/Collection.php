<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */

/**
 * Newsletter queue data grid collection
 */
namespace Magento\Newsletter\Model\ResourceModel\Queue\Grid;

class Collection extends \Magento\Newsletter\Model\ResourceModel\Queue\Collection
{
    /**
     * Init select method
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addSubscribersInfo();
        return $this;
    }
}
