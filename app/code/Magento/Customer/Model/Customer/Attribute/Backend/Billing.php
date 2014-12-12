<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
     * @param \Magento\Framework\Object $object
     * @return void
     */
    public function beforeSave($object)
    {
        $defaultBilling = $object->getDefaultBilling();
        if (is_null($defaultBilling)) {
            $object->unsetDefaultBilling();
        }
    }

    /**
     * @param \Magento\Framework\Object $object
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
