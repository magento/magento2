<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Model\ResourceModel\Oauth\Consumer;

/**
 * OAuth Application resource collection model
 * @since 2.0.0
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialize collection model
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\Integration\Model\Oauth\Consumer::class,
            \Magento\Integration\Model\ResourceModel\Oauth\Consumer::class
        );
    }
}
