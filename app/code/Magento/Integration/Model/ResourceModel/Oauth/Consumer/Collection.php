<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Model\ResourceModel\Oauth\Consumer;

/**
 * OAuth Application resource collection model
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialize collection model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Magento\Integration\Model\Oauth\Consumer',
            'Magento\Integration\Model\ResourceModel\Oauth\Consumer'
        );
    }
}
