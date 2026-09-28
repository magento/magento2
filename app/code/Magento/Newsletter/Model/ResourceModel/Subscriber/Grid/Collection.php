<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Newsletter\Model\ResourceModel\Subscriber\Grid;

class Collection extends \Magento\Newsletter\Model\ResourceModel\Subscriber\Collection
{
    /**
     * Sets flag for customer info loading on load
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->showCustomerInfo()->addSubscriberTypeField()->showStoreInfo();

        return $this;
    }
}
