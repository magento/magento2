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
 * @package     Mage_Poll
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Pool resource collection model
 *
 * @category    Mage
 * @package     Mage_Poll
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Poll_Model_Resource_Poll_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Initialize collection
     *
     */
    public function _construct()
    {
        $this->_init('Mage_Poll_Model_Poll', 'Mage_Poll_Model_Resource_Poll');
    }

    /**
     * Redefine default filters
     *
     * @param string $field
     * @param mixed $condition
     * @return Varien_Data_Collection_Db
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field == 'stores') {
            return $this->addStoreFilter($condition);
        } else {
            return parent::addFieldToFilter($field, $condition);
        }
    }

    /**
     * Add Stores Filter
     *
     * @param mixed $storeId
     * @param bool  $withAdmin
     * @return Mage_Poll_Model_Resource_Poll_Collection
     */
    public function addStoreFilter($storeId, $withAdmin = true)
    {
        $this->getSelect()->join(
            array('store_table' => $this->getTable('poll_store')),
            'main_table.poll_id = store_table.poll_id',
            array()
        )
        ->where('store_table.store_id in (?)', ($withAdmin ? array(0, $storeId) : $storeId))
        ->group('main_table.poll_id');

        /*
         * Allow analytic functions usage
         */
        $this->_useAnalyticFunction = true;

        return $this;
    }

    /**
     * Add stores data
     *
     * @return Mage_Poll_Model_Resource_Poll_Collection
     */
    public function addStoreData()
    {
        $pollIds = $this->getColumnValues('poll_id');
        $storesToPoll = array();

        if (count($pollIds) > 0) {
            $select = $this->getConnection()->select()
                ->from($this->getTable('poll_store'))
                ->where('poll_id IN(?)', $pollIds);
            $result = $this->getConnection()->fetchAll($select);

            foreach ($result as $row) {
                if (!isset($storesToPoll[$row['poll_id']])) {
                    $storesToPoll[$row['poll_id']] = array();
                }
                $storesToPoll[$row['poll_id']][] = $row['store_id'];
            }
        }

        foreach ($this as $item) {
            if (isset($storesToPoll[$item->getId()])) {
                $item->setStores($storesToPoll[$item->getId()]);
            } else {
                $item->setStores(array());
            }
        }

        return $this;
    }

    /**
     * Set stores of the current poll
     *
     * @return Mage_Poll_Model_Resource_Poll_Collection
     */
    public function addSelectStores()
    {
        $pollId = $this->getId();
        $select = $this->getConnection()->select()
            ->from($this->getTable('poll_store'), array('stor_id'))
            ->where('poll_id = :poll_id');
        $stores = $this->getConnection()->fetchCol($select, array(':poll_id' => $pollId));
        $this->setSelectStores($stores);

        return $this;
    }
}
