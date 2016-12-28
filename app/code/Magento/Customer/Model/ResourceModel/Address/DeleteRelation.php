<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\ResourceModel\Address;

use Magento\Customer\Api\Data\CustomerInterface;

/**
 * Class DeleteRelation
 * @package Magento\Customer\Model\ResourceModel\Address
 */
class DeleteRelation
{
    /**
     * Delete relation (billing and shipping) between customer and address
     *
     * @param \Magento\Framework\Model\AbstractModel $address
     * @param \Magento\Customer\Model\Customer $customer
     * @return void
     */
    public function deleteRelation(
        \Magento\Framework\Model\AbstractModel $address,
        \Magento\Customer\Model\Customer $customer
    ) {
        $toUpdate = $this->getDataToUpdate($address, $customer);

        if (!$address->getIsCustomerSaveTransaction() && !empty($toUpdate)) {
            $address->getResource()->getConnection()->update(
                $address->getResource()->getTable('customer_entity'),
                $toUpdate,
                $address->getResource()->getConnection()->quoteInto('entity_id = ?', $customer->getId())
            );
        }
    }

    /**
     * Return address type (billing or shipping), or null if address is not default
     *
     * @param \Magento\Customer\Api\Data\AddressInterface $address
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return array
     */
    private function getDataToUpdate(
        \Magento\Framework\Model\AbstractModel $address,
        \Magento\Customer\Model\Customer $customer
    ) {
        $toUpdate = [];
        if ($address->getId()) {
            if ($customer->getDefaultBilling() == $address->getId()) {
                $toUpdate[CustomerInterface::DEFAULT_BILLING] = null;
            }

            if ($customer->getDefaultShipping() == $address->getId()) {
                $toUpdate[CustomerInterface::DEFAULT_SHIPPING] = null;
            }
        }

        return $toUpdate;
    }
}
