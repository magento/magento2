<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Model\ResourceModel\Debug;

/**
 * Resource Authorize.net debug collection model
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
        $this->_init(
            \Magento\Authorizenet\Model\Debug::class,
            \Magento\Authorizenet\Model\ResourceModel\Debug::class
        );
    }
}
