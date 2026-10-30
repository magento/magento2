<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Model\ResourceModel\Order\Shipment\Attribute\Backend;

/**
 * Invoice backend model for child attribute
 */
class Child extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * Performed before data is saved
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    public function beforeSave($object)
    {
        if ($object->getShipment()) {
            $object->setParentId($object->getShipment()->getId());
        }
        return parent::beforeSave($object);
    }
}
