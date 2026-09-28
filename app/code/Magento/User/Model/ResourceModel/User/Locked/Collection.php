<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\User\Model\ResourceModel\User\Locked;

/**
 * Admin user collection
 */
class Collection extends \Magento\User\Model\ResourceModel\User\Collection
{
    /**
     * Collection Init Select
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addFieldToFilter('lock_expires', ['notnull' => true]);

        return $this;
    }
}
