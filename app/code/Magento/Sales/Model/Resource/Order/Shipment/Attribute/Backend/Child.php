<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Model\Resource\Order\Shipment\Attribute\Backend;

/**
 * Invoice backend model for child attribute
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Child extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * Performed before data is saved
     *
     * @param \Magento\Framework\Object $object
     * @return $this
     */
    public function beforeSave($object)
    {
        if ($object->getShipment()) {
            $object->setParentId($object->getShipment()->getId());
        }
        return parent::beforeSave($object);
    }
}
