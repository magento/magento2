<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\ResourceModel\Customer\Indexer;

/**
 * Customers collection for customer_grid indexer
 */
class Collection extends \Magento\Customer\Model\ResourceModel\Customer\Collection
{
    /**
     * @inheritdoc
     */
    protected function beforeAddLoadedItem(\Magento\Framework\DataObject $item)
    {
        return $item;
    }
}
