<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Eav\Model\Resource\Form;

use Magento\Framework\Model\AbstractModel;
use Magento\Eav\Model\Form\Fieldset as FormFieldset;
use Magento\Framework\DB\Select;

/**
 * Eav Form Fieldset Resource Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Fieldset extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Initialize connection and define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('eav_form_fieldset', 'fieldset_id');
        $this->addUniqueField(
            array('field' => array('type_id', 'code'), 'title' => __('Form Fieldset with the same code'))
        );
    }

    /**
     * After save (save labels)
     *
     * @param FormFieldset|AbstractModel $object
     * @return $this
     */
    protected function _afterSave(AbstractModel $object)
    {
        if ($object->hasLabels()) {
            $new = $object->getLabels();
            $old = $this->getLabels($object);

            $adapter = $this->_getWriteAdapter();

            $insert = array_diff(array_keys($new), array_keys($old));
            $delete = array_diff(array_keys($old), array_keys($new));
            $update = array();

            foreach ($new as $storeId => $label) {
                if (isset($old[$storeId]) && $old[$storeId] != $label) {
                    $update[$storeId] = $label;
                } elseif (isset($old[$storeId]) && empty($label)) {
                    $delete[] = $storeId;
                }
            }

            if (!empty($insert)) {
                $data = array();
                foreach ($insert as $storeId) {
                    $label = $new[$storeId];
                    if (empty($label)) {
                        continue;
                    }
                    $data[] = array(
                        'fieldset_id' => (int)$object->getId(),
                        'store_id' => (int)$storeId,
                        'label' => $label
                    );
                }
                if ($data) {
                    $adapter->insertMultiple($this->getTable('eav_form_fieldset_label'), $data);
                }
            }

            if (!empty($delete)) {
                $where = array('fieldset_id = ?' => $object->getId(), 'store_id IN(?)' => $delete);
                $adapter->delete($this->getTable('eav_form_fieldset_label'), $where);
            }

            if (!empty($update)) {
                foreach ($update as $storeId => $label) {
                    $bind = array('label' => $label);
                    $where = array('fieldset_id =?' => $object->getId(), 'store_id =?' => $storeId);
                    $adapter->update($this->getTable('eav_form_fieldset_label'), $bind, $where);
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
     */
    public function getLabels($object)
    {
        $objectId = $object->getId();
        if (!$objectId) {
            return array();
        }
        $adapter = $this->_getReadAdapter();
        $bind = array(':fieldset_id' => $objectId);
        $select = $adapter->select()->from(
            $this->getTable('eav_form_fieldset_label'),
            array('store_id', 'label')
        )->where(
            'fieldset_id = :fieldset_id'
        );

        return $adapter->fetchPairs($select, $bind);
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param FormFieldset $object
     * @return Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);

        $labelExpr = $select->getAdapter()->getIfNullSql('store_label.label', 'default_label.label');

        $select->joinLeft(
            array('default_label' => $this->getTable('eav_form_fieldset_label')),
            $this->getMainTable() . '.fieldset_id = default_label.fieldset_id AND default_label.store_id=0',
            array()
        )->joinLeft(
            array('store_label' => $this->getTable('eav_form_fieldset_label')),
            $this->getMainTable() .
            '.fieldset_id = store_label.fieldset_id AND default_label.store_id=' .
            (int)$object->getStoreId(),
            array('label' => $labelExpr)
        );

        return $select;
    }
}
