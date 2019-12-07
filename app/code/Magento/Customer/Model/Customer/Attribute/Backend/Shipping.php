<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Customer\Attribute\Backend;

/**
 * Customer default shipping address backend
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Shipping extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * @param \Magento\Framework\DataObject $object
     * @return void
     */
    public function beforeSave($object)
    {
        $defaultShipping = $object->getDefaultShipping();
        if ($defaultShipping === null) {
            $object->unsetDefaultShipping();
        }
    }

    /**
     * @param \Magento\Framework\DataObject $object
     * @return void
     */
    public function afterSave($object)
    {
        if ($defaultShipping = $object->getDefaultShipping()) {
            $addressId = false;
            /**
             * post_index set in customer save action for address
             * this is $_POST array index for address
             */
            foreach ($object->getAddresses() as $address) {
                if ($address->getPostIndex() == $defaultShipping) {
                    $addressId = $address->getId();
                }
            }

            if ($addressId) {
                $object->setDefaultShipping($addressId);
                $this->getAttribute()->getEntity()->saveAttribute($object, $this->getAttribute()->getAttributeCode());
            }
        }
    }
}
