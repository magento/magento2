<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Customer\Model\ResourceModel\Visitor;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Customer\Model\Visitor::class, \Magento\Customer\Model\ResourceModel\Visitor::class);
    }
}
