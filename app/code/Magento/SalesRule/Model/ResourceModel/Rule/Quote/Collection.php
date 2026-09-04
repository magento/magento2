<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\SalesRule\Model\ResourceModel\Rule\Quote;

class Collection extends \Magento\SalesRule\Model\ResourceModel\Rule\Collection
{
    /**
     * Add websites for load
     *
     * @return $this
     */
    public function _initSelect()
    {
        parent::_initSelect();
        $this->addWebsitesToResult();
        return $this;
    }
}
