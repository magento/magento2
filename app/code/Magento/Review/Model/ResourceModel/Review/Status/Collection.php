<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Review statuses collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Review\Model\ResourceModel\Review\Status;

/**
 * Class \Magento\Review\Model\ResourceModel\Review\Status\Collection
 *
 * @since 2.0.0
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Review status table
     *
     * @var string
     * @since 2.0.0
     */
    protected $_reviewStatusTable;

    /**
     * Collection model initialization
     *
     * @return void
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        return parent::_toOptionArray('status_id', 'status_code');
    }
}
