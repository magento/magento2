<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Model\ResourceModel\Order\Invoice\Attribute\Backend;

/**
 * Invoice backend model for child attribute
 */
class Child extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * Method is invoked before save
     *
     * @param \Magento\Framework\DataObject $object
     * @return \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
     */
    public function beforeSave($object)
    {
        if ($object->getInvoice()) {
            $object->setParentId($object->getInvoice()->getId());
        }
        return parent::beforeSave($object);
    }
}
