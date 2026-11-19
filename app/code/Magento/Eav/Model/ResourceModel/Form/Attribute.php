<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

/**
 * EAV Form Attribute Resource Model
 */
namespace Magento\Eav\Model\ResourceModel\Form;

abstract class Attribute extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Return form attribute IDs by form code
     *
     * @param string $formCode
     * @return array
     */
    public function getFormAttributeIds($formCode)
    {
        $bind = ['form_code' => $formCode];
        $select = $this->getConnection()->select()->from(
            $this->getMainTable(),
            'attribute_id'
        )->where(
            'form_code = :form_code'
        );

        return $this->getConnection()->fetchCol($select, $bind);
    }
}
