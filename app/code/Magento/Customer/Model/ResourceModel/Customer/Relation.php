<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\ResourceModel\Customer;

/**
 * Class Relation
 * @since 2.0.0
 */
class Relation implements \Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationInterface
{
    /**
     * Save relations for Customer
     *
     * @param \Magento\Framework\Model\AbstractModel $customer
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @since 2.0.0
     */
    public function processRelation(\Magento\Framework\Model\AbstractModel $customer)
    {
        $defaultBillingId = $customer->getData('default_billing');
        $defaultShippingId = $customer->getData('default_shipping');

        /** @var \Magento\Customer\Model\Address $address */
        foreach ($customer->getAddresses() as $address) {
            if ($address->getData('_deleted')) {
                if ($address->getId() == $defaultBillingId) {
                    $customer->setData('default_billing', null);
                }

                if ($address->getId() == $defaultShippingId) {
                    $customer->setData('default_shipping', null);
                }

                $removedAddressId = $address->getId();
                $address->delete();

                // Remove deleted address from customer address collection
                $customer->getAddressesCollection()->removeItemByKey($removedAddressId);
            } else {
                $address->setParentId(
                    $customer->getId()
                )->setStoreId(
                    $customer->getStoreId()
                )->setIsCustomerSaveTransaction(
                    true
                )->save();

                if (($address->getIsPrimaryBilling() ||
                        $address->getIsDefaultBilling()) && $address->getId() != $defaultBillingId
                ) {
                    $customer->setData('default_billing', $address->getId());
                }

                if (($address->getIsPrimaryShipping() ||
                        $address->getIsDefaultShipping()) && $address->getId() != $defaultShippingId
                ) {
                    $customer->setData('default_shipping', $address->getId());
                }
            }
        }

        $changedAddresses = [];

        $changedAddresses['default_billing'] = $customer->getData('default_billing');
        $changedAddresses['default_shipping'] = $customer->getData('default_shipping');

        $customer->getResource()->getConnection()->update(
            $customer->getResource()->getTable('customer_entity'),
            $changedAddresses,
            $customer->getResource()->getConnection()->quoteInto('entity_id = ?', $customer->getId())
        );
    }
}
