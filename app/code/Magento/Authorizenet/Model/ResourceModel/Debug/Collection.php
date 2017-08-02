<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Model\ResourceModel\Debug;

/**
 * Resource Authorize.net debug collection model
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
        $this->_init(
            \Magento\Authorizenet\Model\Debug::class,
            \Magento\Authorizenet\Model\ResourceModel\Debug::class
        );
    }
}
