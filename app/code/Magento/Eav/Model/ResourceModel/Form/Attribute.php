<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * EAV Form Attribute Resource Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Eav\Model\ResourceModel\Form;

/**
 * Class \Magento\Eav\Model\ResourceModel\Form\Attribute
 *
 * @since 2.0.0
 */
abstract class Attribute extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Return form attribute IDs by form code
     *
     * @param string $formCode
     * @return array
     * @since 2.0.0
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
