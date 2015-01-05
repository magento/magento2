<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Model\Resource\Order\Invoice\Attribute\Backend;

/**
 * Invoice backend model for order attribute
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Order extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * Method is invoked before save
     *
     * @param \Magento\Framework\Object $object
     * @return $this
     */
    public function beforeSave($object)
    {
        if ($object->getOrder()) {
            $object->setOrderId($object->getOrder()->getId());
            $object->setBillingAddressId($object->getOrder()->getBillingAddress()->getId());
        }
        return parent::beforeSave($object);
    }
}
