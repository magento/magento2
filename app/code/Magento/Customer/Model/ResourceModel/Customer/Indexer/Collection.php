<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\ResourceModel\Customer\Indexer;

/**
 * Customers collection for customer_grid indexer
 * @since 2.2.0
 */
class Collection extends \Magento\Customer\Model\ResourceModel\Customer\Collection
{
    /**
     * @inheritdoc
     * @since 2.2.0
     */
    protected function beforeAddLoadedItem(\Magento\Framework\DataObject $item)
    {
        return $item;
    }
}
