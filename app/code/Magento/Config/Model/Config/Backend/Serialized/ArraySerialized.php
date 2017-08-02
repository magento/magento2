<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Backend for serialized array data
 *
 */
namespace Magento\Config\Model\Config\Backend\Serialized;

/**
 * @api
 * @since 2.0.0
 */
class ArraySerialized extends \Magento\Config\Model\Config\Backend\Serialized
{
    /**
     * Unset array element with '__empty' key
     *
     * @return $this
     * @since 2.0.0
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
