<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Customer\Attribute\Backend;

/**
 * Customer default billing address backend
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Billing extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * @param \Magento\Framework\DataObject $object
     * @return void
     */
    public function beforeSave($object)
    {
        $defaultBilling = $object->getDefaultBilling();
        if ($defaultBilling === null) {
            $object->unsetDefaultBilling();
        }
    }

    /**
     * @param \Magento\Framework\DataObject $object
     * @return void
     */
    public function afterSave($object)
    {
        if ($defaultBilling = $object->getDefaultBilling()) {
            $addressId = false;
            /**
             * post_index set in customer save action for address
             * this is $_POST array index for address
             */
            foreach ($object->getAddresses() as $address) {
                if ($address->getPostIndex() == $defaultBilling) {
                    $addressId = $address->getId();
                }
            }
            if ($addressId) {
                $object->setDefaultBilling($addressId);
                $this->getAttribute()->getEntity()->saveAttribute($object, $this->getAttribute()->getAttributeCode());
            }
        }
    }
}
