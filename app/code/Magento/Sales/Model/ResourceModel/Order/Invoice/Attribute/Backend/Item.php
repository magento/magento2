<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Order\Invoice\Attribute\Backend;

/**
 * Invoice backend model for item attribute
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Item extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * Method is invoked after save
     *
     * @param \Magento\Framework\DataObject $object
     * @return \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
     * @since 2.0.0
     */
    public function afterSave($object)
    {
        if ($object->getOrderItem()) {
            $object->getOrderItem()->save();
        }
        return parent::beforeSave($object);
    }
}
