<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Order\Attribute\Backend;

/**
 * Order shipping address backend
 */
class Shipping extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * Perform operation before save
     *
     * @param \Magento\Framework\DataObject $object
     * @return void
     */
    public function beforeSave($object)
    {
        $shippingAddressId = $object->getShippingAddressId();
        if ($shippingAddressId === null) {
            $object->unsetShippingAddressId();
        }
    }

    /**
     * Perform operation after save
     *
     * @param \Magento\Framework\DataObject $object
     * @return void
     */
    public function afterSave($object)
    {
        $shippingAddressId = false;
        foreach ($object->getAddressesCollection() as $address) {
            if ('shipping' == $address->getAddressType()) {
                $shippingAddressId = $address->getId();
            }
        }
        if ($shippingAddressId) {
            $object->setShippingAddressId($shippingAddressId);
            $this->getAttribute()->getEntity()
                ->saveAttribute($object, $this->getAttribute()->getAttributeCode());
        }
    }
}
