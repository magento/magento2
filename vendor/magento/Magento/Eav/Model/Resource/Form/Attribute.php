<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * EAV Form Attribute Resource Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Eav\Model\Resource\Form;

abstract class Attribute extends \Magento\Framework\Model\Resource\Db\AbstractDb
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
        $select = $this->_getReadAdapter()->select()->from(
            $this->getMainTable(),
            'attribute_id'
        )->where(
            'form_code = :form_code'
        );

        return $this->_getReadAdapter()->fetchCol($select, $bind);
    }
}
