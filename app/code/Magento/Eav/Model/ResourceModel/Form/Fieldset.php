<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\ResourceModel\Form;

use Magento\Eav\Model\Form\Fieldset as FormFieldset;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\AbstractModel;

/**
 * Eav Form Fieldset Resource Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Fieldset extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize connection and define main table
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init('eav_form_fieldset', 'fieldset_id');
        $this->addUniqueField(
            ['field' => ['type_id', 'code'], 'title' => __('Form Fieldset with the same code')]
        );
    }

    /**
     * After save (save labels)
     *
     * @param FormFieldset|AbstractModel $object
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @since 2.0.0
     */
    protected function _afterSave(AbstractModel $object)
    {
        if ($object->hasLabels()) {
            $new = $object->getLabels();
            $old = $this->getLabels($object);

            $connection = $this->getConnection();

            $insert = array_diff(array_keys($new), array_keys($old));
            $delete = array_diff(array_keys($old), array_keys($new));
            $update = [];

            foreach ($new as $storeId => $label) {
                if (isset($old[$storeId]) && $old[$storeId] != $label) {
                    $update[$storeId] = $label;
                } elseif (isset($old[$storeId]) && empty($label)) {
                    $delete[] = $storeId;
                }
            }

            if (!empty($insert)) {
                $data = [];
                foreach ($insert as $storeId) {
                    $label = $new[$storeId];
                    if (empty($label)) {
                        continue;
                    }
                    $data[] = [
                        'fieldset_id' => (int)$object->getId(),
                        'store_id' => (int)$storeId,
                        'label' => $label,
                    ];
                }
                if ($data) {
                    $connection->insertMultiple($this->getTable('eav_form_fieldset_label'), $data);
                }
            }

            if (!empty($delete)) {
                $where = ['fieldset_id = ?' => $object->getId(), 'store_id IN(?)' => $delete];
                $connection->delete($this->getTable('eav_form_fieldset_label'), $where);
            }

            if (!empty($update)) {
                foreach ($update as $storeId => $label) {
                    $bind = ['label' => $label];
                    $where = ['fieldset_id =?' => $object->getId(), 'store_id =?' => $storeId];
                    $connection->update($this->getTable('eav_form_fieldset_label'), $bind, $where);
                }
            }
        }

        return parent::_afterSave($object);
    }

    /**
     * Retrieve fieldset labels for stores
     *
     * @param FormFieldset $object
     * @return array
     * @since 2.0.0
     */
    public function getLabels($object)
    {
        $objectId = $object->getId();
        if (!$objectId) {
            return [];
        }
        $connection = $this->getConnection();
        $bind = [':fieldset_id' => $objectId];
        $select = $connection->select()->from(
            $this->getTable('eav_form_fieldset_label'),
            ['store_id', 'label']
        )->where(
            'fieldset_id = :fieldset_id'
        );

        return $connection->fetchPairs($select, $bind);
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param FormFieldset $object
     * @return Select
     * @since 2.0.0
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);

        $labelExpr = $select->getConnection()->getIfNullSql('store_label.label', 'default_label.label');

        $select->joinLeft(
            ['default_label' => $this->getTable('eav_form_fieldset_label')],
            $this->getMainTable() . '.fieldset_id = default_label.fieldset_id AND default_label.store_id=0',
            []
        )->joinLeft(
            ['store_label' => $this->getTable('eav_form_fieldset_label')],
            $this->getMainTable() .
            '.fieldset_id = store_label.fieldset_id AND default_label.store_id=' .
            (int)$object->getStoreId(),
            ['label' => $labelExpr]
        );

        return $select;
    }
}
