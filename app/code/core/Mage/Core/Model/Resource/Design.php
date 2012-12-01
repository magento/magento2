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
 * @category    Mage
 * @package     Mage_Core
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Core Design Resource Model
 *
 * @category    Mage
 * @package     Mage_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Core_Model_Resource_Design extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Define main table and primary key
     *
     */
    protected function _construct()
    {
        $this->_init('design_change', 'design_change_id');
    }

    /**
     * Perform actions before object save
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Core_Model_Resource_Db_Abstract
     * @throws Mage_Core_Exception
     */
    public function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if ($date = $object->getDateFrom()) {
            $object->setDateFrom($this->formatDate($date));
        } else {
            $object->setDateFrom(null);
        }

        if ($date = $object->getDateTo()) {
            $object->setDateTo($this->formatDate($date));
        } else {
            $object->setDateTo(null);
        }

        if (!is_null($object->getDateFrom()) && !is_null($object->getDateTo())
                && Varien_Date::toTimestamp($object->getDateFrom()) > Varien_Date::toTimestamp($object->getDateTo())) {
            Mage::throwException(Mage::helper('Mage_Core_Helper_Data')->__('Start date cannot be greater than end date.'));
        }

        $check = $this->_checkIntersection(
            $object->getStoreId(),
            $object->getDateFrom(),
            $object->getDateTo(),
            $object->getId()
        );

        if ($check) {
            Mage::throwException(
                Mage::helper('Mage_Core_Helper_Data')->__('Your design change for the specified store intersects with another one, please specify another date range.')
            );
        }

        if ($object->getDateFrom() === null) {
            $object->setDateFrom(new Zend_Db_Expr('null'));
        }
        if ($object->getDateTo() === null) {
            $object->setDateTo(new Zend_Db_Expr('null'));
        }

        parent::_beforeSave($object);
    }


    /**
     * Check intersections
     *
     * @param int $storeId
     * @param date $dateFrom
     * @param date $dateTo
     * @param int $currentId
     * @return Array
     */
    protected function _checkIntersection($storeId, $dateFrom, $dateTo, $currentId)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from(array('main_table'=>$this->getTable('design_change')))
            ->where('main_table.store_id = :store_id')
            ->where('main_table.design_change_id <> :current_id');

        $dateConditions = array('date_to IS NULL AND date_from IS NULL');

        if (!is_null($dateFrom)) {
            $dateConditions[] = ':date_from BETWEEN date_from AND date_to';
            $dateConditions[] = ':date_from >= date_from and date_to IS NULL';
            $dateConditions[] = ':date_from <= date_to and date_from IS NULL';
        } else {
            $dateConditions[] = 'date_from IS NULL';
        }

        if (!is_null($dateTo)) {
            $dateConditions[] = ':date_to BETWEEN date_from AND date_to';
            $dateConditions[] = ':date_to >= date_from AND date_to IS NULL';
            $dateConditions[] = ':date_to <= date_to AND date_from IS NULL';
        } else {
            $dateConditions[] = 'date_to IS NULL';
        }

        if (is_null($dateFrom) && !is_null($dateTo)) {
            $dateConditions[] = 'date_to <= :date_to OR date_from <= :date_to';
        }

        if (!is_null($dateFrom) && is_null($dateTo)) {
            $dateConditions[] = 'date_to >= :date_from OR date_from >= :date_from';
        }

        if (!is_null($dateFrom) && !is_null($dateTo)) {
            $dateConditions[] = 'date_from BETWEEN :date_from AND :date_to';
            $dateConditions[] = 'date_to BETWEEN :date_from AND :date_to';
        } elseif (is_null($dateFrom) && is_null($dateTo)) {
            $dateConditions = array();
        }

        $condition = '';
        if (!empty($dateConditions)) {
            $condition = '(' . implode(') OR (', $dateConditions) . ')';
            $select->where($condition);
        }

        $bind = array(
            'store_id'   => (int)$storeId,
            'current_id' => (int)$currentId,
        );

        if (!is_null($dateTo)) {
            $bind['date_to'] = $dateTo;
        }
        if (!is_null($dateFrom)) {
            $bind['date_from'] = $dateFrom;
        }

        $result = $adapter->fetchOne($select, $bind);
        return $result;
    }

    /**
     * Load changes for specific store and date
     *
     * @param int $storeId
     * @param string $date
     * @return array
     */
    public function loadChange($storeId, $date)
    {
        $select = $this->_getReadAdapter()->select()
            ->from(array('main_table' => $this->getTable('design_change')))
            ->where('store_id = :store_id')
            ->where('date_from <= :required_date or date_from IS NULL')
            ->where('date_to >= :required_date or date_to IS NULL');

        $bind = array(
            'store_id'      => (int)$storeId,
            'required_date' => $date
        );

        return $this->_getReadAdapter()->fetchRow($select, $bind);
    }
}
