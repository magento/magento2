<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorization\Model\ResourceModel\Permissions;

/**
 * Admin permissions collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialize resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\Authorization\Model\Rules::class,
            \Magento\Authorization\Model\ResourceModel\Rules::class
        );
    }
}
