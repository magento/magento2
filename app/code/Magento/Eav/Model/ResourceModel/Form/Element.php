<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\ResourceModel\Form;

/**
 * Eav Form Element Resource Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Element extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize connection and define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('eav_form_element', 'element_id');
        $this->addUniqueField(
            ['field' => ['type_id', 'attribute_id'], 'title' => __('Form Element with the same attribute')]
        );
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param \Magento\Eav\Model\Form\Element $object
     * @return \Magento\Framework\DB\Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        $select->join(
            $this->getTable('eav_attribute'),
            $this->getTable('eav_attribute') . '.attribute_id = ' . $this->getMainTable() . '.attribute_id',
            ['attribute_code', 'entity_type_id']
        );

        return $select;
    }
}
