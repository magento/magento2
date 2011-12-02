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
 * @package     Mage_GoogleOptimizer
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Google Optimizer resource model
 *
 * @category    Mage
 * @package     Mage_GoogleOptimizer
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_GoogleOptimizer_Model_Resource_Code extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Resource initialization
     *
     */
    protected function _construct()
    {
        $this->_init('googleoptimizer_code', 'code_id');
    }

    /**
     * Load scripts by entity and store
     *
     * @param Mage_GoogleOptimizer_Model_Code $object
     * @param integer $storeId
     * @return Mage_GoogleOptimizer_Model_Resource_Code
     */
    public function loadbyEntityType($object, $storeId)
    {
        $adapter = $this->_getReadAdapter();

        $codeIdExpr             = $adapter->getCheckSql(
            't_store.code_id IS NOT NULL',
            't_store.code_id',
            't_def.code_id');
        $storeIdExpr            = $adapter->getCheckSql(
            't_store.store_id IS NOT NULL',
            't_store.store_id',
            't_def.store_id');
        $controlScriptExpr      = $adapter->getCheckSql(
            't_store.control_script IS NOT NULL',
            't_store.control_script',
            't_def.control_script');
        $trackingScriptExpr     = $adapter->getCheckSql(
            't_store.tracking_script IS NOT NULL',
            't_store.tracking_script',
            't_def.tracking_script');
        $conversionScriptExpr   = $adapter->getCheckSql(
            't_store.conversion_script IS NOT NULL',
            't_store.conversion_script',
            't_def.conversion_script');
        $conversionPageExpr     = $adapter->getCheckSql(
            't_store.conversion_page IS NOT NULL',
            't_store.conversion_page',
            't_def.conversion_page');
        $additionalDataExpr     = $adapter->getCheckSql(
            't_store.additional_data IS NOT NULL',
            't_store.additional_data',
            't_def.additional_data');

        $select = $adapter->select()
            ->from(
                array('t_def' => $this->getMainTable()),
                array('entity_id', 'entity_type'))
            ->joinLeft(
                array('t_store' => $this->getMainTable()),
                't_store.entity_id = t_def.entity_id AND t_store.entity_type = t_def.entity_type AND '
                    . $adapter->quoteInto('t_store.store_id = ?', $storeId),
                array(
                    'code_id'           => $codeIdExpr,
                    'store_id'          => $storeIdExpr,
                    'control_script'    => $controlScriptExpr,
                    'tracking_script'   => $trackingScriptExpr,
                    'conversion_script' => $conversionScriptExpr,
                    'conversion_page'   => $conversionPageExpr,
                    'additional_data'   => $additionalDataExpr))
            ->where('t_def.entity_id=?', $object->getEntity()->getId())
            ->where('t_def.entity_type=?', $object->getEntityType())
            ->where('t_def.store_id IN (0, ?)', $storeId)
            ->order('t_def.store_id DESC')
            ->limit(1);
        $data = $adapter->fetchRow($select);
        if ($data) {
            $object->setData($data);
        }
        $this->_afterLoad($object);
        return $this;
    }

    /**
     * Delete scripts by entity and store
     *
     * @param Mage_GoogleOptimizer_Model_Code $object
     * @param integer $store_id
     * @return Mage_GoogleOptimizer_Model_Resource_Code
     */
    public function deleteByEntityType($object, $store_id)
    {
        $adapter = $this->_getWriteAdapter();

        $entityIds = $object->getEntityIds();
        if (!empty($entityIds)) {
            $where[$this->getMainTable() . '.entity_id IN (?)'] = $entityIds;
        } else {
            $where[$this->getMainTable() . '.entity_id=?'] = $object->getEntity()->getId();
        }
        $where[$this->getMainTable() . '.entity_type=?'] = $object->getEntityType();
        $where[$this->getMainTable() . '.store_id=?'] = $store_id;
        $adapter->delete($this->getMainTable(), $where);

        $this->_afterDelete($object);
        return $this;
    }
}
