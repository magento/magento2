<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Review statuses collection
 */
namespace Magento\Review\Model\ResourceModel\Review\Status;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_reviewStatusTable;

    /**
     * Collection model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\Review\Model\Review\Status::class,
            \Magento\Review\Model\ResourceModel\Review\Status::class
        );
    }

    /**
     * Convert items array to array for select options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return parent::_toOptionArray('status_id', 'status_code');
    }
}
