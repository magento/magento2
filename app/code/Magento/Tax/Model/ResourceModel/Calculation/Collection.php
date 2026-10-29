<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Tax\Model\ResourceModel\Calculation;

/**
 * Tax Calculation Collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Tax\Model\Calculation::class, \Magento\Tax\Model\ResourceModel\Calculation::class);
    }
}
