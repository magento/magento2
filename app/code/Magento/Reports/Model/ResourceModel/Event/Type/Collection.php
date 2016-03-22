<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Report event types collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Reports\Model\ResourceModel\Event\Type;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Reports\Model\Event\Type', 'Magento\Reports\Model\ResourceModel\Event\Type');
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
