<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Backend for serialized array data
 *
 */
namespace Magento\Backend\Model\Config\Backend\Serialized;

class ArraySerialized extends \Magento\Backend\Model\Config\Backend\Serialized
{
    /**
     * Unset array element with '__empty' key
     *
     * @return void
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        if (is_array($value)) {
            unset($value['__empty']);
        }
        $this->setValue($value);
        parent::beforeSave();
    }
}
