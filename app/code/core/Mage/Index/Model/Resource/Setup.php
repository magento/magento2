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
 * @package     Mage_Index
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Index Setup Model
 *
 * @category    Mage
 * @package     Mage_Index
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Index_Model_Resource_Setup extends Mage_Core_Model_Resource_Setup
{
    /**
     * Apply Index module DB updates and sync indexes declaration
     *
     * @return void
     */
    public function applyUpdates()
    {
        parent::applyUpdates();
        $this->_syncIndexes();
    }

    /**
     * Sync indexes declarations in config and in DB
     *
     * @return Mage_Index_Model_Resource_Setup
     */
    protected function _syncIndexes()
    {
        $connection = $this->getConnection();
        if (!$connection) {
            return $this;
        }
        $indexes = Mage::getConfig()->getNode(Mage_Index_Model_Process::XML_PATH_INDEXER_DATA);
        $indexCodes = array();
        foreach ($indexes->children() as $code => $index) {
            $indexCodes[] = $code;
        }
        $table = $this->getTable('index_process');
        $select = $connection->select()->from($table, 'indexer_code');
        $existingIndexes = $connection->fetchCol($select);
        $delete = array_diff($existingIndexes, $indexCodes);
        $insert = array_diff($indexCodes, $existingIndexes);

        if (!empty($delete)) {
            $connection->delete($table, $connection->quoteInto('indexer_code IN (?)', $delete));
        }
        if (!empty($insert)) {
            $insertData = array();
            foreach ($insert as $code) {
                $insertData[] = array(
                    'indexer_code' => $code,
                    'status' => Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX
                );
            }
            if (method_exists($connection, 'insertArray')) {
                $connection->insertArray($table, array('indexer_code', 'status'), $insertData);
            }
        }
    }
}
