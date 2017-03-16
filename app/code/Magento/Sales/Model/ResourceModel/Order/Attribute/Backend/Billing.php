<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Order\Attribute\Backend;

/**
 * Order billing address backend
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Billing extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * Perform operation before save
     *
     * @param \Magento\Framework\DataObject $object
     * @return void
     */
    public function beforeSave($object)
    {
        $billingAddressId = $object->getBillingAddressId();
        if ($billingAddressId === null) {
            $object->unsetBillingAddressId();
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
        $billingAddressId = false;
        foreach ($object->getAddressesCollection() as $address) {
            if ('billing' == $address->getAddressType()) {
                $billingAddressId = $address->getId();
            }
        }
        if ($billingAddressId) {
            $object->setBillingAddressId($billingAddressId);
            $this->getAttribute()->getEntity()->saveAttribute($object, $this->getAttribute()->getAttributeCode());
        }
    }
}
