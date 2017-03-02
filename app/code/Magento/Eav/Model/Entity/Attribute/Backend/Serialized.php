<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity\Attribute\Backend;

/**
 * "Serialized" attribute backend
 */
class Serialized extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * Serialize before saving
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    public function beforeSave($object)
    {
        // parent::beforeSave() is not called intentionally
        $attrCode = $this->getAttribute()->getAttributeCode();
        if ($object->hasData($attrCode)) {
            $object->setData($attrCode, serialize($object->getData($attrCode)));
        }

        return $this;
    }

    /**
     * Unserialize after saving
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    public function afterSave($object)
    {
        parent::afterSave($object);
        $this->_unserialize($object);
        return $this;
    }

    /**
     * Unserialize after loading
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    public function afterLoad($object)
    {
        parent::afterLoad($object);
        $this->_unserialize($object);
        return $this;
    }

    /**
     * Try to unserialize the attribute value
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    protected function _unserialize(\Magento\Framework\DataObject $object)
    {
        $attrCode = $this->getAttribute()->getAttributeCode();
        if ($object->getData($attrCode)) {
            try {
                $unserialized = unserialize($object->getData($attrCode));
                $object->setData($attrCode, $unserialized);
            } catch (\Exception $e) {
                $object->unsetData($attrCode);
            }
        }

        return $this;
    }
}
