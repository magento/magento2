<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Model\ResourceModel\User\Locked;

/**
 * Admin user collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
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
    }
}
