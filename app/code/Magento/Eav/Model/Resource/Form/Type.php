<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Resource\Form;

use Magento\Eav\Model\Form\Type as FormType;
use Magento\Framework\Model\AbstractModel;

/**
 * Eav Form Type Resource Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Type extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Initialize connection and define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('eav_form_type', 'type_id');
        $this->addUniqueField(
            ['field' => ['code', 'theme', 'store_id'], 'title' => __('Form Type with the same code')]
        );
    }

    /**
     * Load an object
     *
     * @param FormType|AbstractModel $object
     * @param mixed $value
     * @param string $field field to load by (defaults to model id)
     * @return $this
     */
    public function load(AbstractModel $object, $value, $field = null)
    {
        if (is_null($field) && !is_numeric($value)) {
            $field = 'code';
        }
        return parent::load($object, $value, $field);
    }

    /**
     * Retrieve form type entity types
     *
     * @param FormType $object
     * @return array
     */
    public function getEntityTypes($object)
    {
        $objectId = $object->getId();
        if (!$objectId) {
            return [];
        }
        $adapter = $this->_getReadAdapter();
        $bind = [':type_id' => $objectId];
        $select = $adapter->select()->from(
            $this->getTable('eav_form_type_entity'),
            'entity_type_id'
        )->where(
            'type_id = :type_id'
        );

        return $adapter->fetchCol($select, $bind);
    }

    /**
     * Save entity types after save form type
     *
     * @see \Magento\Framework\Model\Resource\Db\AbstractDb#_afterSave($object)
     *
     * @param FormType|AbstractModel $object
     * @return $this
     */
    protected function _afterSave(AbstractModel $object)
    {
        if ($object->hasEntityTypes()) {
            $new = $object->getEntityTypes();
            $old = $this->getEntityTypes($object);

            $insert = array_diff($new, $old);
            $delete = array_diff($old, $new);

            $adapter = $this->_getWriteAdapter();

            if (!empty($insert)) {
                $data = [];
                foreach ($insert as $entityId) {
                    if (empty($entityId)) {
                        continue;
                    }
                    $data[] = ['entity_type_id' => (int)$entityId, 'type_id' => $object->getId()];
                }
                if ($data) {
                    $adapter->insertMultiple($this->getTable('eav_form_type_entity'), $data);
                }
            }

            if (!empty($delete)) {
                $where = ['entity_type_id IN (?)' => $delete, 'type_id = ?' => $object->getId()];
                $adapter->delete($this->getTable('eav_form_type_entity'), $where);
            }
        }

        return parent::_afterSave($object);
    }

    /**
     * Retrieve form type filtered by given attribute
     *
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute|int $attribute
     * @return array
     */
    public function getFormTypesByAttribute($attribute)
    {
        if ($attribute instanceof \Magento\Eav\Model\Entity\Attribute\AbstractAttribute) {
            $attribute = $attribute->getId();
        }
        if (!$attribute) {
            return [];
        }
        $bind = [':attribute_id' => $attribute];
        $select = $this->_getReadAdapter()->select()->from(
            $this->getTable('eav_form_element')
        )->where(
            'attribute_id = :attribute_id'
        );

        return $this->_getReadAdapter()->fetchAll($select, $bind);
    }
}
