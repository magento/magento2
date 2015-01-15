<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity\Attribute\Backend;

/**
 * Entity/Attribute/Model - attribute backend default
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Increment extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * Set new increment id
     *
     * @param \Magento\Framework\Object $object
     * @return $this
     */
    public function beforeSave($object)
    {
        if (!$object->getId()) {
            $this->getAttribute()->getEntity()->setNewIncrementId($object);
        }

        return $this;
    }
}
