<?php
/**
 * Helper for EAV functionality in integration tests.
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Helper;

class Eav
{
    /**
     * Set increment id prefix in entity model.
     *
     * @param string $entityType
     * @param string $prefix
     */
    public static function setIncrementIdPrefix($entityType, $prefix)
    {
        $website = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Store\Model\StoreManagerInterface'
        )->getWebsite();
        $storeId = $website->getDefaultStore()->getId();
        $entityTypeModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Eav\Model\Entity\Type'
        )->loadByCode(
            $entityType
        );
        /** @var \Magento\Eav\Model\Entity\Store $entityStore */
        $entityStore = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Eav\Model\Entity\Store'
        )->loadByEntityStore(
            $entityTypeModel->getId(),
            $storeId
        );
        $entityStore->setEntityTypeId($entityTypeModel->getId());
        $entityStore->setStoreId($storeId);
        $entityStore->setIncrementPrefix($prefix);
        $entityStore->save();
    }
}
