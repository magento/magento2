<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
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
