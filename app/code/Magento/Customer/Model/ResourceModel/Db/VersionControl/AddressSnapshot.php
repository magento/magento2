<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\ResourceModel\Db\VersionControl;

use Magento\Framework\DataObject;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;

/**
 * Class \Magento\Customer\Model\ResourceModel\Db\VersionControl\AddressSnapshot
 *
 * @since 2.1.0
 */
class AddressSnapshot extends Snapshot
{
    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function isModified(DataObject $entity)
    {
        $result = parent::isModified($entity);

        if (!$result
            && !$entity->getIsCustomerSaveTransaction()
            && $this->isAddressDefault($entity)
        ) {
            return true;
        }

        return $result;
    }

    /**
     * Checks if address has chosen as default and has had an id
     *
     * @param DataObject $entity
     * @return bool
     * @since 2.1.0
     */
    private function isAddressDefault(DataObject $entity)
    {
        return $entity->getId() && ($entity->getIsDefaultBilling() || $entity->getIsDefaultShipping());
    }
}
