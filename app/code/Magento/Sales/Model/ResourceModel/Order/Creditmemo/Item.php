<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Model\ResourceModel\Order\Creditmemo;

use Magento\Sales\Model\ResourceModel\EntityAbstract as SalesResource;
use Magento\Sales\Model\Spi\CreditmemoItemResourceInterface;

/**
 * Flat sales order creditmemo item resource
 */
class Item extends SalesResource implements CreditmemoItemResourceInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'sales_order_creditmemo_item_resource';

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sales_creditmemo_item', 'entity_id');
    }

    /**
     * Before save
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        /**@var $object \Magento\Sales\Model\Order\Creditmemo\Item*/
        if (!$object->getParentId() && $object->getCreditmemo()) {
            $object->setParentId($object->getCreditmemo()->getId());
        }
        return parent::_beforeSave($object);
    }
}
