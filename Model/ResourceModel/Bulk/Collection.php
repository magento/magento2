<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Model\ResourceModel\Bulk;

/**
 * Class Collection
 * @codeCoverageIgnore
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define collection item type and corresponding table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\AsynchronousOperations\Model\BulkSummary::class,
            \Magento\AsynchronousOperations\Model\ResourceModel\Bulk::class
        );
        $this->setMainTable('magento_bulk');
    }
}
