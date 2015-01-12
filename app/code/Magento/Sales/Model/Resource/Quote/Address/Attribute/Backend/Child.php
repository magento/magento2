<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Resource\Quote\Address\Attribute\Backend;

/**
 * Quote address attribute backend child resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Child extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * Set store id to the attribute
     *
     * @param \Magento\Framework\Object $object
     * @return $this
     */
    public function beforeSave($object)
    {
        if ($object->getAddress()) {
            $object->setParentId($object->getAddress()->getId())->setStoreId($object->getAddress()->getStoreId());
        }
        parent::beforeSave($object);
        return $this;
    }
}
