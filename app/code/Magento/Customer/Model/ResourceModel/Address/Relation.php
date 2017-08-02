<?php
/**
 * Customer address entity resource model
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\ResourceModel\Address;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationInterface;

/**
 * Class represents save operations for customer address relations
 * @since 2.0.0
 */
class Relation implements RelationInterface
{
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     * @since 2.0.0
     */
    protected $customerFactory;

    /**
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function processRelation(\Magento\Framework\Model\AbstractModel $object)
    {
        /**
         * @var $object \Magento\Customer\Model\Address
         */
        if (!$object->getIsCustomerSaveTransaction() && $this->isAddressDefault($object)) {
            $customer = $this->customerFactory->create()->load($object->getCustomerId());
            $changedAddresses = [];

            if ($object->getIsDefaultBilling()) {
                $changedAddresses['default_billing'] = $object->getId();
            }

            if ($object->getIsDefaultShipping()) {
                $changedAddresses['default_shipping'] = $object->getId();
            }

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
     * Checks if address has chosen as default and has had an id
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return bool
     * @since 2.0.0
     */
    protected function isAddressDefault(\Magento\Framework\Model\AbstractModel $object)
    {
        return $object->getId() && ($object->getIsDefaultBilling() || $object->getIsDefaultShipping());
    }
}
