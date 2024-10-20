<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Report event types collection
 */
namespace Magento\Reports\Model\ResourceModel\Event\Type;

/**
 * @api
 * @since 100.0.2
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Reports\Model\Event\Type::class, \Magento\Reports\Model\ResourceModel\Event\Type::class);
    }

    /**
     * Return option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return parent::_toOptionArray('event_type_id', 'event_name');
    }
}
