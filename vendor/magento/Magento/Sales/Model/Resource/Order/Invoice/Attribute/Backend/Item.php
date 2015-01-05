<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Model\Resource\Order\Invoice\Attribute\Backend;

/**
 * Invoice backend model for item attribute
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Item extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * Method is invoked after save
     *
     * @param \Magento\Framework\Object $object
     * @return \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
     */
    public function afterSave($object)
    {
        if ($object->getOrderItem()) {
            $object->getOrderItem()->save();
        }
        return parent::beforeSave($object);
    }
}
