<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
