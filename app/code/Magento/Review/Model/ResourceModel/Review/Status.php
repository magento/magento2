<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Review\Model\ResourceModel\Review;

/**
 * Review status resource model
 */
class Status extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Resource status model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('review_status', 'status_id');
    }
}
