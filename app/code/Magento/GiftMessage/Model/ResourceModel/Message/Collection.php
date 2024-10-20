<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Model\ResourceModel\Message;

/**
 * Gift Message collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\GiftMessage\Model\Message::class,
            \Magento\GiftMessage\Model\ResourceModel\Message::class
        );
    }
}
