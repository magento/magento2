<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

/**
 * Backend for serialized array data
 *
 */
namespace Magento\Config\Model\Config\Backend\Serialized;

/**
 * @api
 * @since 100.0.2
 */
class ArraySerialized extends \Magento\Config\Model\Config\Backend\Serialized
{
    /**
     * Unset array element with '__empty' key
     *
     * @return $this
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        if (is_array($value)) {
            unset($value['__empty']);
        }
        $this->setValue($value);
        return parent::beforeSave();
    }
}
