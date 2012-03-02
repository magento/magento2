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
 * @package     Mage_Dataflow
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * DataFlow Import resource model
 *
 * @category    Mage
 * @package     Mage_Dataflow
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Dataflow_Model_Resource_Import extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Define main table
     *
     */
    protected function _construct()
    {
        $this->_init('dataflow_import_data', 'import_id');
    }

    /**
     * Returns all import data select by session id
     *
     * @param int $sessionId
     * @return Varien_Db_Select
     */
    public function select($sessionId)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable())
            ->where('session_id=?', $sessionId)
            ->where('status=?', 0);
        return $select;
    }

    /**
     * Load all import data by session id
     *
     * @param int $sessionId
     * @param int $min
     * @param int $max
     * @return array
     */
    public function loadBySessionId($sessionId, $min = 0, $max = 100)
    {
        if (!is_numeric($min) || !is_numeric($max)) {
            return array();
        }
        $bind = array(
            'status'     => 0,
            'session_id' => $sessionId,
            'min_id'     => (int)$min,
            'max_id'     => (int)$max,
        );
        $read = $this->_getReadAdapter();
        $select = $read->select()
            ->from($this->getTable('dataflow_import_data'))
            ->where('import_id >= :min_id')
            ->where('import_id >= :max_id')
            ->where('status= :status')
            ->where('session_id = :session_id');
        return $read->fetchAll($select, $bind);
    }

    /**
     * Load total import data by session id
     *
     * @param int $sessionId
     * @return array
     */
    public function loadTotalBySessionId($sessionId)
    {
        $bind = array(
            'status'    => 0,
            'session_id' => $sessionId
        );
        $read = $this->_getReadAdapter();
        $select = $read->select()
            ->from($this->getTable('dataflow_import_data'),
                array('max'=>'max(import_id)', 'min'=>'min(import_id)', 'cnt'=>'count(*)'))
            ->where('status = :status')
            ->where('session_id = :$session_id');
        return $read->fetchRow($select, $bind);
    }

    /**
     * Load import data by id
     *
     * @param int $importId
     * @return array
     */
    public function loadById($importId)
    {
        $bind = array(
            'status'    => 0,
            'import_id' => $importId
        );
        $read = $this->_getReadAdapter();
        $select = $read->select()
            ->from($this->getTable('dataflow_import_data'))
            ->where('status = :status')
            ->where('import_id = :import_id');
        return $read->fetchRow($select, $bind);
    }
}
