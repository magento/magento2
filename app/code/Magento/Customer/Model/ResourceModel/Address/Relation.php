<?php
/**
 * Customer address entity resource model
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\ResourceModel\Address;

use Magento\Customer\Model\Address;
use Magento\Customer\Model\Customer;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationInterface;

/**
 * Class represents save operations for customer address relations
 */
class Relation implements RelationInterface
{
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     */
    public function __construct(\Magento\Customer\Model\CustomerFactory $customerFactory)
    {
        $this->customerFactory = $customerFactory;
    }

    /**
     * Process object relations
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return void
     */
    public function processRelation(\Magento\Framework\Model\AbstractModel $object)
    {
        /**
         * @var $object Address
         */
        if (!$object->getIsCustomerSaveTransaction() && $object->getId()) {
            $customer = $this->customerFactory->create()->load($object->getCustomerId());

            $changedAddresses = [];
            $changedAddresses = $this->getDefaultBillingChangedAddress($object, $customer, $changedAddresses);
            $changedAddresses = $this->getDefaultShippingChangedAddress($object, $customer, $changedAddresses);

            if ($changedAddresses) {
                $customer->getResource()->getConnection()->update(
                    $customer->getResource()->getTable('customer_entity'),
                    $changedAddresses,
                    $customer->getResource()->getConnection()->quoteInto('entity_id = ?', $customer->getId())
                );
            }
        }
    }

    /**
     * Get default billing changed address
     *
     * @param Address $object
     * @param Customer $customer
     * @param array $changedAddresses
     * @return array
     */
    private function getDefaultBillingChangedAddress(
        Address $object,
        Customer $customer,
        array $changedAddresses
    ): array {
        if ($object->getIsDefaultBilling()) {
            $changedAddresses['default_billing'] = $object->getId();
        } elseif ($customer->getDefaultBillingAddress()
            && $object->getIsDefaultBilling() === false
            && (int)$customer->getDefaultBillingAddress()->getId() === (int)$object->getId()
        ) {
            $changedAddresses['default_billing'] = null;
        }

        return $changedAddresses;
    }

    /**
     * Get default shipping changed address
     *
     * @param Address $object
     * @param Customer $customer
     * @param array $changedAddresses
     * @return array
     */
    private function getDefaultShippingChangedAddress(
        Address $object,
        Customer $customer,
        array $changedAddresses
    ): array {
        if ($object->getIsDefaultShipping()) {
            $changedAddresses['default_shipping'] = $object->getId();
        } elseif ($customer->getDefaultShippingAddress()
            && $object->getIsDefaultShipping() === false
            && (int)$customer->getDefaultShippingAddress()->getId() === (int)$object->getId()
        ) {
            $changedAddresses['default_shipping'] = null;
        }

        return $changedAddresses;
    }

    /**
     * Checks if address has chosen as default and has had an id
     *
     * @deprecated 102.0.1 Is not used anymore due to changes in logic of save of address.
     *             If address was default and becomes not default than default address id for customer must be
     *             set to null
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return bool
     */
    protected function isAddressDefault(\Magento\Framework\Model\AbstractModel $object)
    {
        return $object->getId() && ($object->getIsDefaultBilling() || $object->getIsDefaultShipping());
    }
}
