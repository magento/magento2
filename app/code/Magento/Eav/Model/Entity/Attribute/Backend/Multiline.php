<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity\Attribute\Backend;

use Magento\Framework\Object;

/**
 * Class Multiline
 */
class Multiline extends AbstractBackend
{
    const DELIMITER = "\n";

    /**
     * Convert data before saving
     *
     * @param Object $object
     * @return $this
     */
    public function beforeSave($object)
    {
        $attrCode = $this->getAttribute()->getAttributeCode();
        $value = $object->getData($attrCode);
        if (is_array($value)) {
            $object->setData($attrCode, implode(static::DELIMITER, $value));
        }

        return $this;
    }

    /**
     * Convert data after saving
     *
     * @param Object $object
     * @return $this
     */
    public function afterSave($object)
    {
        parent::afterSave($object);
        $this->convert($object);
        return $this;
    }

    /**
     * Convert data after loading
     *
     * @param \Magento\Framework\Object $object
     * @return $this
     */
    public function afterLoad($object)
    {
        parent::afterLoad($object);
        $this->convert($object);
        return $this;
    }

    /**
     * Convert string to array
     *
     * @param Object $object
     * @return void
     */
    protected function convert($object)
    {
        $attrCode = $this->getAttribute()->getAttributeCode();
        $value = $object->getData($attrCode);
        if (is_string($value)) {
            $object->setData($attrCode, explode(static::DELIMITER, $value));
        }
    }
}
