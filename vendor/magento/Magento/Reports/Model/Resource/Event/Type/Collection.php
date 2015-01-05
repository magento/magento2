<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Report event types collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Reports\Model\Resource\Event\Type;

class Collection extends \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Reports\Model\Event\Type', 'Magento\Reports\Model\Resource\Event\Type');
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
