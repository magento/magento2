<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
