<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\ResourceModel\Visitor;

/**
 * Class \Magento\Customer\Model\ResourceModel\Visitor\Collection
 *
 * @since 2.0.0
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource initialization
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(\Magento\Customer\Model\Visitor::class, \Magento\Customer\Model\ResourceModel\Visitor::class);
    }
}
